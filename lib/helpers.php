<?php

/**
 * Print simple progress bar.
 * 
 * @access public
 * @param int $processed amount of items processed
 * @param int $max maximum amount of items to process
 * @param boolean $showPoints show progress points or not
 * @return void
 */
function progress($processed, $max, $showPoints)
{
    $progress = round($processed / ( $max / 100 ), 2);
    $progress_points = floor($progress/2);
    $points = "";

    if ($showPoints) {
        $points = str_pad(str_repeat("#", $progress_points), 52, " ", STR_PAD_RIGHT);
    }
    
    echo $points . sprintf("%.2f", $progress) . str_pad("% ", 27, " ", STR_PAD_RIGHT). "\r";
}
