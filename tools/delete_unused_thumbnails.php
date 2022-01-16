<?php

(PHP_SAPI !== "cli" || isset($_SERVER["HTTP_USER_AGENT"])) && die("CLI only");

require("../settings.inc.php");
require("../functions.inc.php");

// Help.
$help = "
    php delete_unused_thumbnails.php [-h] [-o] [-r]\n
    \n
    -a : delete all unused files in cache.\n
    -c : delete unused clips.\n
    -h : help (this text).\n
    -p : delete unused previews.\n
    -t : delete unused thumbnails.\n
    \n
";

$arguments  = getopt("ahpt");

// Verwerk argumenten.
if (isset($arguments["h"]))
{
    echo $help;
    die();
}

$deletedItems = array();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PATH));

// Verwerk mappen.
foreach ($rii as $item)
{
    if ($item->isFile() || $item->getFilename() == "..")
    {
        continue;
    }

    $itemInfo   = getItemInfo($item);
    $fullPath   = $itemInfo["fullPath"];
    $cachePath  = str_replace(PATH, CACHE, $fullPath);

    echo "----------------------------------------\n";
    echo "Original folder: " . realpath($fullPath) . "\n";
    echo "----------------------------------------\n";

    // Lees originelen
    try
    {
        $di = new DirectoryIterator($fullPath);
    }
    catch (Exception $e)
    {
    }
    
    // Verkrijg cache bestanden per origineel in een array
    $cacheItems = array();
    
    foreach ($di as $file)
    {
        if (! $file->isFile() || $file->getFilename() == "." || $file->getFilename() == "..")
        {
            continue;
        }
        
        $cacheFile      = realpath($cachePath) . "/" . pathinfo($file)["filename"];
        //echo "Original: {$file} added to cache array.\n";
        
        $cacheItems[]   = $cacheFile . "-clip.mp4";
        $cacheItems[]   = $cacheFile . "-preview.jpg";
        $cacheItems[]   = $cacheFile . "-thumbnail.jpg";
    }
    
    // Lees originelen
    try
    {
        $di = new DirectoryIterator($cachePath);
    }
    catch (Exception $e)
    {
    }

    echo "\nCache folder: " . realpath($cachePath) . "\n\n";
    
    if (realPath($cachePath) == "")
    {
        echo "Error: no cache matchine original folder\n";
        continue;
    }
    
    // Vergelijk of bestand voorkomt in array met cache bestanden
    foreach ($di as $file)
    {
        if (! $file->isFile() || $file->getFilename() == "." || $file->getFilename() == "..")
        {
            continue;
        }

        $cacheFile = realpath($cachePath) . "/" . $file;
        echo "Cache: {$file} ";
        
        if (! in_array($cacheFile, $cacheItems))
        {
            echo "not in cache array - it will be deleted.\n";
            $deletedItems[] = $cacheFile;
        }
        else
        {
            echo "in cache array.\n";
        }
    }    
}

echo "Cache files to be deleted:\n";
print_r($deletedItems);

?>