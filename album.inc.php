<?php

// Te grote aantallen verdelen over pagina's?
if ($ALBUM->totalNumberOfItems > PAGESIZE)
{
    $currentItemOffset      = 0;
    $ALBUM->items           = array_slice($ALBUM->items, ($page * PAGESIZE), PAGESIZE);

    $first                  = "<span class='button--disabled header'> |< </span>";
    $previous               = "<span class='button--disabled header'> < </span>";
    $next                   = "<span class='button--disabled header'> > </span>";
    $last                   = "<span class='button--disabled header'> >| </span>";
    
    // Eerste/ vorige pagina links.
    if ($page > 0)
    {
        $first              = "<a class='button header' class='header' href='index.php?path={$ALBUM->htmlPath}&search={$search}&page=0' id='first' title='acceskey + w'> |< </a>";
        $previousItemOffset = $currentItemOffset - 1;
        $previous           = "<a class='button header' class='header' href='index.php?path={$ALBUM->htmlPath}&search={$search}&page=" . $page - 1 . "' id='previous' title='acceskey + a'> < </a>";
    }
    
    // Volgende/ laatste pagina links.
    if ($page < floor($ALBUM->totalNumberOfItems / PAGESIZE))
    {
        $next               = "<a class='button header' class='header' href='index.php?path={$ALBUM->htmlPath}&search={$search}&page=" . $page + 1 . "' id='next' title='acceskey + d'> > </a>";
        $last               = "<a class='button header' class='header' href='index.php?path={$ALBUM->htmlPath}&search={$search}&page=" . floor($ALBUM->totalNumberOfItems / PAGESIZE) . "' id='last' title='acceskey + s'> >| </a>";
    }

    $counter                = "<span class='button--disabled header'>(" . $page + 1 . " / " . ceil($ALBUM->totalNumberOfItems / PAGESIZE) . ")</span>";
    $footer                 .= "<div id='pagination' class='footer__pagination'>{$first} {$previous} {$counter} {$next} {$last}</div>";
}

// Albumitems doorlopen.
foreach($ALBUM->items as $item)
{
    if ($item->type == "album")
    {
        $subAlbum           = new Album($item->fullPath);
        $subAlbum->loadFiles();
            
        // Als album weergeven.
        if ($_SESSION["displayAlbum"] == "album")
        {
            $output         .= $subAlbum->printThumbnail();
        }
        // Als lijst weergeven.
        else
        {
            $output         .= "
                <tr>
                    <td><span class='album__list--subtitle'>{$item->number}</span></td>
                    <td>
                        <a class='album__list button header' href='index.php?path={$item->htmlPath}' title='{$item->htmlPath}'>
                            <span class='album__list--title'>{$item->title}</span>
                        </a>
                    </td> 
                    <td><span class='album__list--subtitle'>({$subAlbum->printInformation()})</span></td>
                </tr>
            ";
        }
    }
    else
    {
        // Als album weergeven.
        if ($_SESSION["displayAlbum"] == "album")
        {
            $output         .= $item->printThumbnail(isMobileDevice());
        }
        // Als lijst weergeven.
        else
        {
            $output         .= $item->printList();
        }
    }
}

if ($_SESSION["displayAlbum"] == "list")
{
    $output                 = "<table>{$output}</table>";
}

// Menu opmaken.
if ($_SESSION["displayAlbum"] == "album")
{
    $menu[22]               = "<a class='menu--item' href='index.php?action=switchDisplayAlbum&fromurl={$_SERVER["QUERY_STRING"]}' title='Weergave wijzigen'>Als lijst weergeven</a>";
}
else
{
    $menu[22]               = "<a class='menu--item' href='index.php?action=switchDisplayAlbum&fromurl={$_SERVER["QUERY_STRING"]}' title='Weegave wijzigen'>Als album weergeven</a>";
}
$menu[25]                   = "<a class='menu--item' href='{$_SERVER["REQUEST_URI"]}&regenerate=true' title='Miniaturen opnieuw genereren'>Miniaturen opnieuw genereren</a>";

// Output opmaken.
$navigation                 .= " <span class='button--disabled header'>({$ALBUM->totalNumberOfItems})</span>"; // Aantal items in album toevoegen aan navigatie.

$output                     = "
    <div class='album'>
        {$output}
    </div>
";

?>
