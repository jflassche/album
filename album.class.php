<?php

/**
 * Album.
 */
class Album
{
    public $numberOfItems       = 0;
    public $numberOfAlbums      = 0;
    private $files              = array();
    public $items               = array();
    public $path                = "";
    public $totalNumberOfItems  = 0;
    
    /**
     * Constructor.
     */
    function __construct($path)
    {
        $this->path             = $path;
        
        $path                   = str_replace(PATH . "/", "", $path); // PATH als prefix verwijderen.
        
        $pathinfo               = pathinfo($path);
        $filename               = $pathinfo["filename"] ?? "";

        $this->htmlPath         = htmlspecialchars($path, ENT_QUOTES);
        $this->title            = str_replace("_", " ", $filename);

        return;
    }

    /**
     * Verkrijg positie van item in album.
     */
    function getItemOffset(Item $item)
    {
        for ($offset = 0; $offset < $this->numberOfItems; $offset++)
        {
            // Vergelijk op basis van path. Dit is namelijk uniek.
            if ($this->items[$offset]->path == $item->path)
            {
                return $offset;
            }
        }

        return false;
    }

    /**
     * Verkrijg volgend item op basis van huidig item.
     */
    function getNextItem(Item $item)
    {
        $offset = $this->getItemOffset($item);
        
        if ($offset < $this->totalNumberOfItems - 1)
        {
            $offset++;
        }
        
        return $this->items[$offset];
    }

    /**
     * Verkrijg vorig item op basis van huidig item.
     */
    function getPreviousItem(Item $item)
    {
        $offset = $this->getItemOffset($item);
        
        if ($offset > 0)
        {
            $offset--;
        }
        
        return $this->items[$offset];
    }

    /**
     * Geef een willekeurig thumbnail terug (als deze bestaat).
     */
    function getThumbnail()
    {
        $items = $this->items;
        shuffle($items);
        
        foreach ($items as $item)
        {
            if ($item->type != "album" && $item->type != "audio" && $item->type != "unknown") // Kijk of er een thumbnail bestaat.
            {
                return $item->getThumbnail();
            }
        }
        
        return "img/album.png"; // Geef het standaard albumplaatje terug.
    }
    
    /**
     * Laad bestanden uit het bestandssysteem.
     */
    function loadFiles()
    {
        if (is_dir($this->path))
        {
            $this->files = glob($this->path . "/*");
        }
        else
        {
            $this->files = glob(dirname($this->path) . "/*"); // Als path een los item betreft dan het album waar het onderdeel van is gebruiken.
        }
        
        $this->processItems();
        
        return;
    }
        
    /**
     * Laad bestanden via een zoekopdracht.
     */
    function loadSearch()
    {
        $pattern        = "/(?i).*{$this->path}.*$/";
        $dir            = new RecursiveDirectoryIterator(PATH);
        $ite            = new RecursiveIteratorIterator($dir);
        foreach (new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH) as $file)
        {
            $basename = pathinfo($file[0])["basename"];
            
            if ($basename == "..") // Dit is een album.
            {
                $this->files[] = dirname($file[0]);
            }
            else if ($basename != ".") // /. niet meenemen.
            {
                $this->files[] = $file[0];
            }
        }

        $this->processItems();
        
        return;
    }

    /**
     * Druk html af met albuminformatie.
     */
    function printInformation()
    {
        $contentText = array();
        ($this->numberOfAlbums != 0) ? $contentText[] = "{$this->numberOfAlbums} albums" : false;
        ($this->numberOfItems != 0) ? $contentText[] = "{$this->numberOfItems} items" : false;
        
        return implode(", ", $contentText);
    }
    
    /**
     * Druk html af van voor de thumbnail.
     */
    function printThumbnail()
    {
        return "
            <a href='index.php?path={$this->htmlPath}'>
                <div class='album__thumbnail'>
                    <div class='album__thumbnail__image'><img src='{$this->getThumbnail()}'></div>
                    <div class='album__thumbnail__information album__thumbnail__information--album'>
                        <table>
                            <tr>
                                <td><img src='img/album_small.png'></td>
                                <td>
                                    <span class='album__thumbnail__information--title'>{$this->title}</span><br/>
                                    <span class='album__thumbnail__information--subtitle'>{$this->printInformation()}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class='album__thumbnail__overlay'></div>
                 </div>
            </a>
        ";
    }

    /**
     * Verwerk bestanden tot items.
     */
    function processItems()
    {
        $items = array();
        
        $this->files = array_reverse($this->files); // Meest recente eerst.
        
        // Maak items.
        foreach ($this->files as $file)
        {
            $items[] = new Item($file);
        }

        // Sorteer items: subalbums eerst.
        usort($items,
            function($a, $b)
            {
                return strcmp($a->type, $b->type);
            }
        );

        // Tellingen.
        foreach ($items as $index => $item)
        {
            $item->number = $index + 1;
            $this->items[] = $item;
            
            ($item->type == "album") ? $this->numberOfAlbums++ : $this->numberOfItems++; // Hoeveelheid subalbums of items.
        }
        $this->totalNumberOfItems = $this->numberOfAlbums + $this->numberOfItems;

        unset($items); // Maak tijdelijke items leeg.
        unset($this->files); // Maak bestanden array leeg.
             
        return;
    }

// End of class Album.
}

?>