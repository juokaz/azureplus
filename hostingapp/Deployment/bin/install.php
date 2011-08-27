<?php

$args = $_SERVER['argv'];

if (count($args) != 4) {
	die('Invalid params: php install.php URL FOLDER');
}

$url = $args[1];
$existing = $args[2];
$folder = $args[3];

// File might not be actualy there
if (file_exists($existing)) {
	$current = file_get_contents($existing);
} else {
	$current = null;
}

$new = file_get_contents($url);

if (!$new) {
	die('Cannot download a new file');
}

// new file
if (sha1($current) != sha1($new)) {
	echo 'Updating the APP' . PHP_EOL;
	$zip = new ZipArchive();
	
	$temp_file = tempnam(sys_get_temp_dir(), 'Azure');
	file_put_contents($temp_file, $new);

	// open archive 
	if ($zip->open($temp_file) !== TRUE) {
		die ("Could not open archive");
	}
	
	$zip->extractTo($folder);
	$zip->close();

	// update current file
	copy($temp_file, $existing);

	// No need for this anymore
	unlink($temp_file);

	echo 'Stored archive contents in: ' . $folder . PHP_EOL;
} else {
	echo 'Ignoring the APP' . PHP_EOL;
}