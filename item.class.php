<?php

/**
 * Item.
 */
class Item
{
    /**
     * Construeer item en verwerk alle variabelen.
     */
    function __construct($file)
    {
        $path                   = str_replace(PATH . "/", "", $file); // PATH als prefix verwijderen.
        
        $pathinfo               = pathinfo($path);
        $basename               = $pathinfo["basename"] ?? "";
        $dirname                = $pathinfo["dirname"] ?? "";
        $extension              = $pathinfo["extension"] ?? "/"; // Extensie '/' stelt een album voor.
        $filename               = $pathinfo["filename"] ?? "";

        $this->album            = $dirname;
        $this->clipPath         = CACHE . "/" . "{$dirname}/{$filename}.{$extension}-clip.mp4";
        $this->extension        = $extension;
        $this->filename         = $filename;
        $this->filesize         = printFileSize(@filesize($file));
        $this->fullPath         = $file;
        $this->htmlPath         = htmlspecialchars($path, ENT_QUOTES);
        $this->path             = $path;
        $this->previewPath      = CACHE . "/" . "{$dirname}/{$filename}.{$extension}-preview.jpg";
        $this->thumbnailPath    = CACHE . "/" . "{$dirname}/{$filename}.{$extension}-thumbnail.jpg";
        $this->title            = str_replace("_", " ", $filename);
        $this->type             = FILETYPES[strtolower($extension)] ?? "unknown";

        return;
    }

    /**
     * Verkrijg afbeelding van item.
     */
    function generateImage($type = "thumbnail")
    {
        // Verkrijg origineel.
        switch($this->type)
        {
            case "document":
                if ($image = @new imagick($this->fullPath . "[0]"))
                {
                    $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE ); // Voorkomt zwarte achtergrond bij .pdf bestanden.
                }
                break;
                
            case "image":
                if ($image  = @new Imagick($this->fullPath))
                {
                    $image  = rotateImageByExif($image, $image->getImageProperty("exif:Orientation")); // Draaien en spiegelen op basis van exif?
                }
                break;
    
            case "video":
                $tempFile   = tempnam(sys_get_temp_dir(), "album");
                try
                {
                    exec(FFMPEG . " -i '{$this->fullPath}' -y -deinterlace -an -ss 1 -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg '{$tempFile}' 2>&1");
                    $image  = @new Imagick($tempFile);
                }
                catch (Exception $e)
                {
                }
                
                if (file_exists($tempFile))
                {
                    unlink($tempFile);
                }
                break;
        }

        // Verwerk afbeelding.
        if ($image)
        {
            if ($type == "preview")
            {
                $height     = PREVIEWSIZE;
                $outputFile = $this->previewPath;
            }
            else
            {
                $height     = THUMBNAILSIZE;
                $outputFile = $this->thumbnailPath;
            }

            // Cache map aanmaken?
            if (! file_exists(dirname($outputFile)))
            {
                mkdir(dirname($outputFile), 0775, true); // Recursief!
            }

            // Schalen.
            $inputWidth     = $image->getImageWidth();
            $inputHeight    = $image->getImageHeight();
            $scaleFactor    = $inputHeight/ $height;
            $outputWidth    = round($inputWidth/ $scaleFactor);
            $outputHeight   = $height;
            $image->resizeImage($outputWidth, $outputHeight, Imagick::FILTER_BOX, 1, false);

            // Schrijf image.
            $image->setImageCompressionQuality(QUALITY);
            $image->stripImage();
            $image->writeImage($outputFile);
            $image->clear();    
        }
        
        return;
    }

    /**
     * Geef thumbnail bestand terug.
     */
    function getThumbnail()
    {
        switch($this->type)
        {
            case "audio":
                return "img/audio.png";
                break;

            case "album":
                return "img/album.png";
                break;
                
            case "document":
            case "image":
            case "video":
                return "show.php?path={$this->htmlPath}&display=thumbnail";
                break;

            default:
                return "img/unknown.png";
                break;
        }
    }

    /**
     * Druk html af voor het juiste type lijstitem.
     */
    function printList()
    {
        return "
            <tr>
                <td><span class='album__list--subtitle'>{$this->number}</span></td>
                <td>
                    <a class='album__list button header' href='index.php?path={$this->htmlPath}' title='{$this->htmlPath}'>
                        <span class='album__list--title'>{$this->filename}.{$this->extension}</span>
                    </a>
                </td>
                <td><span class='album__list--subtitle'>({$this->type}, {$this->filesize})</span></td>
            </tr>
        ";
    }
    
    /**
     * Druk html af voor het juiste type voorbeeld.
     */
    function printPreview()
    {
        switch ($this->type)
        {
            case "audio":
                return "<audio controls><source src='show.php?path={$this->htmlPath}&display=full' type='audio/mp3'></audio>";
                break;

            case "document":
                return "<iframe src='show.php?path={$this->htmlPath}&display=full'></iframe>";
                break;

            case "image":
                return "<a href='show.php?path={$this->htmlPath}&display=full'><img src='show.php?path={$this->htmlPath}&display=preview' alt='{$this->title}'></a>";
                break;

            case "video":
                return "<video controls preload='metadata'><source src='show.php?path={$this->htmlPath}&display=full' type='video/mp4' /></video>";
                break;

            default:
                return "<img src='img/unknown.png' alt='unknown'>";
                break;
        }
    }
    
    /**
     * Druk html af voor het juiste type miniatuur.
     */
    function printThumbnail($isMobile = false)
    {
        $image              = "<img src='{$this->getThumbnail()}' alt='{$this->title}'>";
        $information        = "{$this->title}";
        $link               = "href='index.php?path={$this->htmlPath}'";
        $overlay            = "";

        switch ($this->type)
        {
            case "unknown":
                $link       = "";
                break;
            
            case "video":
                $overlay    = "<img src='img/video.png' alt='video'>";
                
                if (! $isMobile && file_exists($this->clipPath))
                {
                    $image = "
                        <video class='album__thumbnail__image--clip' loop onmouseover='this.play();' onmouseout='this.pause(); this.currentTime=0;'>
                            <source src='show.php?path={$this->htmlPath}&display=clip' /> 
                        </video>
                    ";
                }
                break;
        }

        return "
            <a {$link}>
                <div class='album__thumbnail'>
                    <div class='album__thumbnail__image'>{$image}</div>
                    <div class='album__thumbnail__information album__thumbnail__information--item'>{$information}</div>
                    <div class='album__thumbnail__overlay'>{$overlay}</div>
                </div>
            </a>
        ";
    }
    

// End of class Item.
}

?>