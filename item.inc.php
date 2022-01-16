<?php

// Item links.
$currentItemOffset  = $ALBUM->getItemOffset($ITEM);

$first              = "<span class='button--disabled header'> |< </span>";
$previous           = "<span class='button--disabled header'> < </span>";
$next               = "<span class='button--disabled header'> > </span>";
$last               = "<span class='button--disabled header'> >| </span>";

// Eerste/ vorige item links.
if ($currentItemOffset > 0)
{
    $first          = "<a class='button header' href='index.php?path={$ALBUM->items[0]->htmlPath}' id='first' title='acceskey + w'> |< </a>";
    $previous       = "<a class='button header' href='index.php?path={$ALBUM->items[$currentItemOffset - 1]->htmlPath}' id='previous'title='acceskey + a'> < </a>";
}

// Volgende/ laatste item links.
if ($currentItemOffset < $ALBUM->totalNumberOfItems - 1)
{
    $next           = "<a class='button header' href='index.php?path={$ALBUM->items[$currentItemOffset + 1]->htmlPath}' id='next' title='acceskey + d'> > </a>";
    $last           = "<a class='button header' href='index.php?path={$ALBUM->items[$ALBUM->totalNumberOfItems - 1]->htmlPath}' id='last' title='acceskey + s'> >| </a>";
}

// Menu opmaken.
if ($ITEM->type == "image" || $ITEM->type == "video")
{
    $menu[25]       = "<a class='menu--item' href='{$_SERVER["REQUEST_URI"]}&regenerate=true' title='Miniatuur opnieuw genereren'>Miniatuur opnieuw genereren</a>";
}

// Output opmaken.
$footer             .= "
    <div class='footer__pagination'>
        {$first}
        {$previous}
        <span class='button--disabled header'>(" . $currentItemOffset + 1 . " / " . $ALBUM->totalNumberOfItems . ")</span>
        {$next}
        {$last}
    </div>
";

$output             = "
    <div class='viewer'>
        <div class='viewer__item'>{$ITEM->printPreview()}</div>
    </div>
";

?>