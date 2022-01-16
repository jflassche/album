<?php

/**
 * Schoon het gegeven path op.
 */
function cleanPath($path)
{
    // Verkrijg path uit url.
    $path       = array_filter(explode("/", $path),
        function($element)
        {
            // Onwenselijke onderdelen verwijderen.
            if ($element != "." && $element != "..")
            {
                return trim($element);
            }
            return false;
        }
    );
    $elements   = array_filter($path);
    $path       = implode("/", $elements);

    return $path;
}

/**
 * Debug
 */
function debug($variable)
{
    if (! DEBUG)
    {
        return;
    }
        
    $output = "<table>";
    
    ksort($variable);
    foreach($variable as $key => $value)
    {
        if ($key == "output") // Output variabele niet tonen, deze is immers nog niet compleet.
        {
            continue;
        }
        
        ($value === false) ? $value = " false" : $value = $value; // False waardes als 'false' weergeven.
        
        if (is_array($value)) // Array's dichtgeklapt tonen.
        {
            $value = "<details><summary></summary><pre>" . print_r($value, true) . "</pre></details>";
        }
        
        if ($value instanceof Album)
        {
            $value = "<details><summary></summary><pre>" . print_r($value, true) . "</pre></details>";
        }

        if ($value instanceof Item)
        {
            $value = "<details><summary></summary><pre>" . print_r($value, true) . "</pre></details>";
        }
        
                    
        $output .= "
            <tr>
                <td style='font-weight: bold; vertical-align: baseline;'>{$key}</td>
                <td>{$value}</td>
            </tr>
        ";
    }
    return "{$output}</table>";
}

/**
 * Wordt de pagina via een telefoon bekeken?
 * https://www.geeksforgeeks.org/how-to-detect-a-mobile-device-using-php/
 */
function isMobileDevice()
{
    $mobileDevices = array(
        "android",
        "avantgo",
        "blackberry",
        "bolt",
        "boost",
        "cricket",
        "docomo",
        "fone",
        "hiptop",
        "mini",
        "mobi",
        "palm",
        "phone",
        "pie",
        "privacybrowser",
        "tablet",
        "up\.browser",
        "up\.link",
        "webos",
        "wos"
    );
    
    return preg_match("/(" . implode("|", $mobileDevices) . ")/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Druk bestandsgrootte af in leesbaar formaat.
 * Jeffrey Sambells (https://stackoverflow.com/a/23888858)
 */
function printFilesize($bytes, $dec = 2) 
{
    $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

/**
 * Afbeelding draaien/ spiegelen op basis van exif oriëntatie.
 */
function rotateImageByExif($image, $rotation)
{
    switch ($rotation)
    {
        case 1:
            break;
            
        case 2:
            $image->flopImage();
            break;
                        
        case 3:
            $image->rotateimage("#FFF", 180);
            break;

        case 4:
            $image->rotateimage("#FFF", 180);
            $image->flopImage();
            break;

        case 5:
            $image->rotateimage("#FFF", 90);
            $image->flipImage();
            break;
    
        case 6:
            $image->rotateimage("#FFF", 90);
            break;

        case 7:
            $image->rotateimage("#FFF", -90);
            $image->flipImage();
            break;
    
        case 8:
            $image->rotateimage("#FFF", -90);
            break;
    }

    return $image;
}

?>