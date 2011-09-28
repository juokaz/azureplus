<?php

// file to download
$file = $_SERVER['argv'][1];
// where to store
$target = $_SERVER['argv'][2];

// download php zip archive
$source = file_get_contents($file);

if (strpos($file, '.zip') !== false) {
    $zip = new ZipArchive();

    $temp_file = tempnam(sys_get_temp_dir(), 'PHP');
    file_put_contents($temp_file, $source);

    // open archive 
    if ($zip->open($temp_file) !== TRUE) {
	    die ("Could not open archive");
    }

    $zip->extractTo($target);
    $zip->close();

    // No need for this anymore
    unlink($temp_file);
} else {
    file_put_contents($target, $source);
}
