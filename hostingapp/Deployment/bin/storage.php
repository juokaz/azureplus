<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once '../Assets/PHPAzure/Microsoft/Console/Command.php';
require_once '../Assets/PHPAzure/Microsoft/WindowsAzure/Storage/Blob.php';

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
	 * @command-name store
	 * @command-description Store data in Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $from Microsoft_Console_Command_ParameterSource_Argv --from|-f Required. From filename.
	 */
	public function storeCommand($container, $name, $from)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob('blob.core.windows.net', self::ACCOUNT, self::KEY);

		$result = $storageClient->createContainerIfNotExists($container);
		
		$result = $storageClient->putBlob($container, $name, $from);

		echo 'Blob name is: ' . $result->Name;
	}
	
	/**
	 * @command-name store-archive
	 * @command-description Store archived folder
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $folder Microsoft_Console_Command_ParameterSource_Argv --folder|-f Required. From folder.
	 */
	public function storeArchiveCommand($container, $name, $folder)
	{
		$folder = rtrim($folder, '\\') . '\\';
		
		$temp_file = tempnam(sys_get_temp_dir(), 'Azure');
		
		$zip = new ZipArchive();

		// open archive 
		if ($zip->open($temp_file, ZIPARCHIVE::CREATE) !== TRUE) {
			die ("Could not open archive");
		}

		// initialize an iterator
		// pass it the directory to be processed
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));

		// iterate over the directory
		// add each file found to the archive
		foreach ($iterator as $key=>$value) {
			// fix file name
			$name_ = str_replace($folder, '', $key);
			$zip->addFile(realpath($key), $name_) or die ("ERROR: Could not add file: $key");
		}

		// close and save archive
		$zip->close();
		
		// Store file in the storage acccount
		$this->storeCommand($container, $name, $temp_file);
		
		// No need for this anymore
		unlink($temp_file);
	}

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
	}
}

Microsoft_Console_Command::bootstrap($_SERVER['argv']);