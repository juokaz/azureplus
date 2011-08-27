<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once dirname(__FILE__) . '/../Assets/PHPAzure/Microsoft/Console/Command.php';
require_once dirname(__FILE__) . '/../Assets/PHPAzure/Microsoft/WindowsAzure/Storage/Blob.php';

/**
 * Stpre command
 * 
 * @command-handler storage
 * @command-handler-description Store data in azure storage account
 * @command-handler-header (C) Web Species Ltd
 */
class Storage
    extends Microsoft_Console_Command
{
	const ACCOUNT = 'azurep';
	const KEY = 'RfdXBwu35jj8oxNJJ1TI+J1RakF6jPr8na2WOMp0NCNJvgZPsx8iGNgUnfWW/1z1tY0EQZ/dfdInRyxwWh4htw==';

	/**
	 * @command-name retrieve
	 * @command-description Retrieve data from Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $to Microsoft_Console_Command_ParameterSource_Argv --to|-t Required. To filename.
	 */
	public function retrieveCommand($container, $name, $to)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob('blob.core.windows.net', self::ACCOUNT, self::KEY);

		$storageClient->getBlob($container, $name, $to);
	}

	/**
	 * @command-name retrieve-archive
	 * @command-description Retrieve archived folder from Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $to Microsoft_Console_Command_ParameterSource_Argv --to|-t Required. To folder.
	 */
	public function retrieveArchiveCommand($container, $name, $to)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob('blob.core.windows.net', self::ACCOUNT, self::KEY);
		
		$temp_file = tempnam(sys_get_temp_dir(), 'Azure');

		$storageClient->getBlob($container, $name, $temp_file);
		
		$zip = new ZipArchive();
		
		// open archive 
		if ($zip->open($temp_file) !== TRUE) {
			die ("Could not open archive");
		}
		
		$zip->extractTo($to);
		$zip->close();

		// No need for this anymore
		unlink($temp_file);
		
		echo 'Stored archive contents in: ' . $to . PHP_EOL;
	}

	/**
	 * @command-name update
	 * @command-description Retrieve archived folder from Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $existing Microsoft_Console_Command_ParameterSource_Argv --existing|-e Required. Existing app.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $to Microsoft_Console_Command_ParameterSource_Argv --to|-t Required. To folder.
	 */
	public function updateCommand($container, $existing, $name, $to)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob('blob.core.windows.net', self::ACCOUNT, self::KEY);
		
		$temp_file = tempnam(sys_get_temp_dir(), 'Azure');

		$storageClient->getBlob($container, $name, $temp_file);
		
		// File might not be actualy there
		if (file_exists($existing)) {
			$current = file_get_contents($existing);
		} else {
			$current = null;
		}
		$new = file_get_contents($temp_file);
		
		// new file
		if (sha1($current) != sha1($new)) {
			echo 'Updating the APP' . PHP_EOL;
			$zip = new ZipArchive();
			
			// open archive 
			if ($zip->open($temp_file) !== TRUE) {
				die ("Could not open archive");
			}
			
			$zip->extractTo($to);
			$zip->close();
		
			echo 'Stored archive contents in: ' . $to . PHP_EOL;
		} else {
			echo 'Ignoring the APP' . PHP_EOL;
		}
		
		// update current file
		copy($temp_file, $existing);

		// No need for this anymore
		unlink($temp_file);
	}
}

Microsoft_Console_Command::bootstrap($_SERVER['argv']);