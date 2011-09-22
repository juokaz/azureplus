<?php

// this is not supposed to be called from anywhere else but CLI
if (PHP_SAPI !== 'cli') {
    exit();
}

$folder = '.' . DIRECTORY_SEPARATOR;
$temp_file = tempnam(sys_get_temp_dir(), 'Azurep');
$endpoint = '%ENDPOINT%';
		
$zip = new ZipArchive();

// open archive 
if ($zip->open($temp_file, ZIPARCHIVE::CREATE) !== TRUE) {
	die ("Could not open archive");
}

// initialize an iterator
// pass it the directory to be processed, which is current dir
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS));

// iterate over the directory
// add each file found to the archive
foreach ($iterator as $key=>$value) {
	// fix file name
	$name_ = str_replace($folder, '', $key);

    // check for ignored files and folders
    foreach (array('.git') as $pattern) {
        if (strpos($name_, $pattern) !== false) {
            continue 2;
        }
    }
    
	$zip->addFile(realpath($key), $name_) or die ("ERROR: Could not add file: $key");
}

// close and save archive
$zip->close();

$curl_handle = curl_init();
    
$headers = array(
    'Content-Type: application/zip',
    'Api-Key: %API_KEY%',
);

curl_setopt($curl_handle, CURLOPT_URL, $endpoint);
curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_POST, 1); 
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, file_get_contents($temp_file));

$buffer = curl_exec($curl_handle);
curl_close($curl_handle);

print $buffer . PHP_EOL;

// this file is not needed anymore
unlink($temp_file);
