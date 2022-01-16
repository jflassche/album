<?php

// Diavoorstelling instellingenscherm tonen
if (! $slideshowInit)
{    
    $output = "
        <form method='get' action='index.php'>
            <input type='hidden' name='action' value='slideshow'>
            <input type='hidden' name='path' value='{$ITEM->path}'>
            <input type='hidden' name='slideshow' value='true'>
            <input type='hidden' name='slideshowInit' value='true'>
            <table class='slideshow--init'>
                <tr><td><label for='slideshowRepeat'>Herhalen vanaf begin na laatste dia</label></td><td><input id='slideshowRepeat' type='checkbox' name='slideshowRepeat'></td></tr>
                <tr><td><label for='slideshowStartFirst'>Starten vanaf eerste afbeelding</label></td><td><input id='slideshowStartFirst' type='checkbox' name='slideshowStartFirst'></td></tr>
                <tr><td>Tijd per dia in seconden</td><td><input name='slideshowTimer' type='number' min='1' max='600' value='{$slideshowTimer}'> s</td></tr>
                <tr><td colspan='2'><input type='submit' value=' Diavoorstelling starten '></td></tr>
            </table>
        </form>
    ";
}
else
{
    // Start vanaf de eerste dia?
    if ($slideshowStartFirst)
    {
        $nextItemLink = "index.php?action=slideshow&slideshowInit=true&path={$ALBUM->items[0]->htmlPath}&slideshow=true&slideshowRepeat={$slideshowRepeat}&slideshowTimer={$slideshowTimer}";
        header("Location: {$nextItemLink}");
    }

    // Bepaal volgende dia.
    if ($ALBUM->getItemOffset($ITEM) < $ALBUM->totalNumberOfItems - 1)
    {
        $nextItemLink   = "index.php?action=slideshow&slideshowInit=true&path={$ALBUM->getNextItem($ITEM)->htmlPath}&slideshow=true&slideshowRepeat={$slideshowRepeat}&slideshowTimer={$slideshowTimer}";
    }
    else
    {
        // Hierna terug naar het album of opnieuw beginnen?
        $nextItemLink = "index.php?path={$ITEM->album}";
        if ($slideshowRepeat)
        {
            $nextItemLink = "index.php?action=slideshow&slideshowInit=true&path={$ALBUM->items[0]->htmlPath}&slideshow=true&slideshowRepeat={$slideshowRepeat}&slideshowTimer={$slideshowTimer}";
        }
    }
    
    // Verkrijg dia.
    if ($ITEM->type == "image")
    {
        $output = "<img src='show.php?path={$ITEM->htmlPath}&display=preview'>";
    }
    else
    {    
        // Gelijk naar de volgende dia.
        header("Location: {$nextItemLink}");
        die();
    }

    // Niet gebruikte paginaelementen leeg maken.
    $footer     = "";
    $menu       = false;
    $menuButton = "";
    $navigation = "";
    $search     = "";

    // Output opmaken.
    // <style> toevoegen via PHP omdat $slideshowTimer toegvoegd moet worden voor een soepele overgang.
    $head = "
        <meta http-equiv='refresh' content='{$slideshowTimer}; URL={$nextItemLink}'>
        <style>
            body {
                animation: slideshow--fade-in-out ease {$slideshowTimer}s;
                animation-fill-mode: forwards;
                animation-iteration-count: 1;
                background: #000;
            }
        </style>
     ";
     
     $output = "
        <div class='slideshow'>
            <div class='slideshow__item'>
                {$output}
            </div>
        </div>
    ";
}

?>