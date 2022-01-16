<?php

(PHP_SAPI !== "cli" || isset($_SERVER["HTTP_USER_AGENT"])) && die("CLI only");

require_once("../album.class.php");
require_once("../functions.inc.php");
require_once("../item.class.php");
require_once("../settings.inc.php");

// Help.
$help = "
    php generate_thumbnails.php [-h] [-o] [-r]

    -d : delete *all* other files.
    -h : help (this text).
    -o : overwrite existing files.
    -r : recursive process all subfolders.

";

echo $help;

$arguments  = getopt("dhor");
$counter    = 1;
$files      = array(); 
$path       = PATH;

// Verwerk argumenten.
if (isset($arguments["r"]))
{
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
}
else
{
    $rii = new DirectoryIterator($path);
}

// Verwerk bestanden.
foreach ($rii as $file)
{
    if ($file->isDir())
    {
        continue;
    }

    $item = new Item($file->getPathName());
   
    switch($item->type)
    {
        case "image":
            if (! file_exists($item->thumbnailPath) || isset($arguments["o"]))
            {
                $item->generateImage("thumbnail");
                echo "{$counter} - {$item->htmlPath} - thumbnail ". printFilesize(filesize($item->thumbnailPath)) . "\n";
                $counter++;
            }
   
            if (! file_exists($item->previewPath) || isset($arguments["o"]))
            {
                $item->generateImage("preview");
                echo "{$counter} - {$item->htmlPath} - preview ". printFilesize(filesize($item->previewPath)) . "\n";
                $counter++;
            }
             
            break;
        
        case "video":
            if (! file_exists($item->thumbnailPath) || isset($arguments["o"]))
            {
                $item->generateImage("thumbnail");
                echo "{$counter} - {$item->htmlPath} - video thumbnail ". printFilesize(filesize($item->thumbnailPath)) . "\n";
                $counter++;
            }

            if (! file_exists($item->clipPath) || isset($arguments["o"]))
            {
                exec("php generate_clip.php -i {$item->fullPath} -o {$item->clipPath}");
                echo "{$counter} - {$item->htmlPath} - video clip ". printFilesize(filesize($item->clipPath)) . "\n";
                $counter++;
            }
            
            break;
    }
}

?>
