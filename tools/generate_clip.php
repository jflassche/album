<?php

(PHP_SAPI !== "cli" || isset($_SERVER["HTTP_USER_AGENT"])) && die("CLI only");

// Help.
$help = "php generate_clip.php [-f frames] [-h help] -i input_file [-n fragments] -o output_preview_file [-t temp folder]
    
    -f : frames per fragment.
    -h : help (this text).
    -i : input movie file.
    -n : number of fragments in preview.
    -o : output movie preview file.
    -t : folder for temporary files.

";
$sceneStart = array();

// Verwerk argumenten.
$arguments  = getopt("fhi:no:t");
(isset($arguments["f"])) ? $frames = $arguments["f"] : $frames = 20;
(isset($arguments["h"])) ? die($help) : $true = true;
(isset($arguments["i"])) ? $input = $arguments["i"] : die("Require input file -i.\n");
(isset($arguments["n"])) ? $maxScenes = $arguments["n"] : $maxScenes = 4;
(isset($arguments["o"])) ? $output = $arguments["o"] : die("Require output preview file -o.\n");
(isset($arguments["t"])) ? $temp = $arguments["t"] : $temp = "/tmp/album_generate_preview";

// Toon instellingen
echo "Input:\t\t" . shell_exec("ls -l -h '{$input}'");
echo "Frames:\t\t{$frames}\n";
echo "maxScenes:\t{$maxScenes}\n";
echo "Temp:\t\t{$temp}\n";

// make temp
mkdir($temp);

// movie length
$cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '{$input}'";
$seconds = round(shell_exec($cmd), 1);
echo "Movie length:\t{$seconds}s\n";

// bepaal spreiding van fragmenten
$sceneInterval = $seconds / $maxScenes;
echo "Interval:\t{$sceneInterval}s\n";

for ($i = 0; $i < $maxScenes; $i++)
{
        $sceneStart[] = floor($i * $sceneInterval); 
}
$scenes = count($sceneStart);
echo "Scenes at:\t" . implode("s, ", $sceneStart) . "s\n\n";

// extract frames	
$frameCounter = 0;
echo "Extract frames from {$scenes} scenes: ";
for ($i = 0; $i < $scenes; $i++)
{
    echo ($i + 1) . " ";
    $start = $sceneStart[$i];
    $end = $start + 1;
    $command = "ffmpeg -hide_banner -loglevel error -y -threads 4 -ss {$sceneStart[$i]} -i '{$input}' -vf scale=-1:300 -vframes {$frames} -start_number {$frameCounter} -vsync vfr '{$temp}/%04d.bmp'";
    shell_exec($command);
    $frameCounter += $frames;
}
echo "\n";

// combine frames in mp4
echo "Combine frames to output ";
$command = "ffmpeg -hide_banner -loglevel error -y -threads 4 -i '{$temp}/%04d.bmp' -vf fps={$frames} -preset ultrafast -crf 28 '{$output}'";
shell_exec($command);
echo "\n";

// remove all frame
echo "Remove temp frames ";
array_map("unlink", glob($temp . "/*"));
rmdir($temp);
echo "\n\n";

// show result
echo "Output:\t\t" . shell_exec("ls -l -h '{$output}'");
echo "Time:\t\t" . round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]), 1) . "s\n";

?>
