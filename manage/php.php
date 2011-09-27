<?php

// file to download
$file = $_SERVER['argv'][1];
// where to store
$target = $_SERVER['argv'][2];
// php.ini location
$ini_file = $target . DIRECTORY_SEPARATOR . 'php.ini';

// download php zip archive
$source = file_get_contents($file);

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

// Create actual php.ini
move($target . DIRECTORY_SEPARATOR . 'php.ini-production', $ini_file);
