<?php

(PHP_SAPI !== "cli" || isset($_SERVER["HTTP_USER_AGENT"])) && die("CLI only");

require("../settings.inc.php");
require("../functions.inc.php");

// Help.
$help = "
    php list_unknown_files.php [-d] [-h] [-r]

    -d : delete unknown files.
    -h : help (this text).
    -r : recursive process all subfolders.

";

echo $help;

$arguments  = getopt("dhr");
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

    $file = getItemInfo($file->getPathName());
   
    switch($file["type"])
    {
        case "unknown":
            echo " {$file["fullPath"]}";
            if (isset($arguments["d"]))
            {
                unlink($file["fullPath"]);
                echo " ... deleted!";
            }
            echo "\n";
            break;
        
        default:          
            break;
    }
}

?>