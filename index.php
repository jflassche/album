<?php

/**
TODO:
- MOBILE slideshow in mobileview toont afbeelding te groot.
- MOBILE top button centreren.
- PATH via encrypted waarde in URL instellen zodat er een gast-album gemaakt kan worden. Eventueel met expiry date erin.
*/

session_start();

require_once("album.class.php");
require_once("functions.inc.php");
require_once("item.class.php");
require_once("settings.inc.php");

// Debugging?
if (DEBUG)
{
    error_reporting(E_ALL);
    ini_set("display_errors", True);
}

// Initialisatie variabelen.
$body                   = "";
$cumulativePath         = "";
$currentItemOffset      = 0;
$footer                 = "<div class='button header footer__to-top' onclick='toTop()' id='toTop' title='Naar boven'>Boven</div>";
$head                   = "";
$menu                   = array();
$navigation             = array();
$output                 = "";

// Verkrijg parameters uit sessie.
(! isset($_SESSION["theme"])) ? $_SESSION["theme"] = "light" : false;
(! isset($_SESSION["displayAlbum"])) ? $_SESSION["displayAlbum"] = "album" : false;

// Verkrijg parameters uit url.
$action                 = $_GET["action"] ?? false;
$display                = $_GET["display"] ?? false;
$page                   = $_GET["page"] ?? 0;
$path                   = $_GET["path"] ?? "/";
$regenerate             = $_GET["regenerate"] ?? false;
$search                 = $_GET["search"] ?? false;
$slideshow              = $_GET["slideshow"] ?? false;
$slideshowInit          = $_GET["slideshowInit"] ?? false;
$slideshowRepeat        = $_GET["slideshowRepeat"] ?? false;
$slideshowStartFirst    = $_GET["slideshowStartFirst"] ?? false;
$slideshowTimer         = $_GET["slideshowTimer"] ?? 4;
$fromurl                = $_GET["fromurl"] ?? "";

// Schoon path op (geen ../ of ./).
$path                   = cleanPath($path);

// Verkrijg inhoud.
if ($search) // ... uitzoekopdracht.
{
    $contentPath        = $search;
    $ALBUM              = new Album($contentPath);
    $ALBUM->loadSearch();
}
else // ... uit bestandssysteem.
{
    $contentPath        = PATH . "/" . $path;
    $ALBUM              = new Album($contentPath);
    $ALBUM->loadFiles();

    if (is_file($contentPath) || $action == "slideshow") // Het betreft een item of een diavoorstelling.
    {
        $ITEM           = new Item($contentPath);
    }
}

// Menuitems.
$menuButton             = "<a class='button header' href='javascript:void(0);' id='menuToggle' onclick='menuToggle()'>&#9776;</a>";

$menu[10]               = "
    <form class='menu--item search' method='get' action='index.php'>
        <input name='search' placeholder='Zoeken...' type='text' value='{$search}'>
        <button type='submit' value='Zoek'>&#128269;</button>
    </form>
";

if (! empty($ALBUM->items))
{
    $menu[20]           = "<a class='menu--item' href='index.php?action=slideshow&path={$ALBUM->htmlPath}' title='Diavoorstelling'>Diavoorstelling</a>";
}
if ($_SESSION["theme"] == "light")
{
    $menu[30]           = "<a class='menu--item' href='index.php?action=switchTheme&fromurl={$_SERVER["QUERY_STRING"]}' title='Thema wijzigen'>Donker thema</a>";
}
else
{
    $menu[30]           = "<a class='menu--item' href='index.php?action=switchTheme&fromurl={$_SERVER["QUERY_STRING"]}' title='Thema wijzigen'>Licht thema</a>";
}
if (DEBUG)
{
    $menu[40]           = "<a class='menu--item' href='index.php?action=debug&path={$ALBUM->htmlPath}' title='Debug'>Debug</a>";
}

// Navigatiebalk.
$pathElements           = explode("/", $path);
$pathElements           = array_filter($pathElements);
$numberOfPathElements   = count($pathElements);

if ($numberOfPathElements == 1)
{
    $navigation[]       = "<a class='button header' href='?path=/' id='back'>Album</a>"; // Dit is de terugoptie.
}
else
{
    $navigation[]       = "<a class='button header' href='?path=/'>Album</a>";
}

if ($search)
{
    $navigation[]       = "<a class='button header'>{$search}</a>"; // Zoekterm toevoegen.
}

foreach ($pathElements as $number => $element)
{
    $cumulativePath     .= $element . "/";
    $cumulativePath     = htmlspecialchars($cumulativePath, ENT_QUOTES); // Om bestandsnamen met bijv. ' te accepteren.
    $element            = str_replace("_", " ", $element);
    $element            = htmlspecialchars_decode($element); // Om HTML % codes leesbaar te maken.
    if ($number == $numberOfPathElements - 2)
    {
        $navigation[]   = "<a class='button header' href='?path={$cumulativePath}' id='back'>{$element}</a>"; // Dit is de terugoptie.
    }
    else
    {
        $navigation[]   = "<a class='button header' href='?path={$cumulativePath}'>{$element}</a>";
    }
}

$navigation             = implode("<span class='button--disabled header'> / </span>", $navigation);

// Verwerk acties.
switch ($action)
{
    case "debug":
        if (! DEBUG)
        {
            header("Location: index.php?path=" . $path);
            die();
        }
        $output .= debug($GLOBALS);
        break;
 
    case "regenerate":
        // Todo.
        header("Location: index.php?" . $fromurl);
        die();
        break;
 
    case "slideshow":
        require_once("slideshow.inc.php");
        break;

    case "switchDisplayAlbum":
        ($_SESSION["displayAlbum"] == "album") ? $_SESSION["displayAlbum"] = "list" : $_SESSION["displayAlbum"] = "album";
        header("Location: index.php?" . $fromurl);
        die();
        break;

    case "switchTheme":
        ($_SESSION["theme"] == "light") ? $_SESSION["theme"] = "dark" : $_SESSION["theme"] = "light";
        header("Location: index.php?" . $fromurl);
        die();
        break;

    default:
        (isset($ITEM)) ? require_once("item.inc.php") : require_once("album.inc.php"); // Item of album tonen?
        break;
}

// Menuelementen opmaken.
if ($menu != false)
{
    ksort($menu);
    $menu = "<a class='button header menu--close-button' href='javascript:void(0);' onclick='menuToggle()'>X</a><br/>" . implode($menu);
}

// Pagina afdrukken.
echo "
    <!DOCTYPE html>
    <html lang='nl'>
        <head>
            <link rel='stylesheet' href='css/stylesheet.css'>
            <link rel='stylesheet' href='css/{$_SESSION["theme"]}.css'>
            <meta http-equiv='content-type' content='text/html; charset=utf-8'>
            <meta name='viewport' content='width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1'>
            <meta name='robots' content='noindex'>
            <meta name='robots' content='nofollow'>
            <script src='js/javascript.js'></script>
            <title>Album</title>
            {$head}
        </head>
        
        <body>
            <div id='menu' class='menu js-menu--hide'>{$menu}</div>
            <div id='page' class='page'>
                <div id='header'>
                    {$menuButton}
                    {$navigation}
                    <hr/>
                </div>
                {$output}
            </div>
            <div class='footer'>{$footer}</div>
        </body>
        
    </html>
";

?>