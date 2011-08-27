<?php

require_once dirname(__FILE__) . '/lib/PHPAzure/Microsoft/AutoLoader.php';

/**
 * Stpre command
 * 
 * @command-handler storage
 * @command-handler-description Store data in azure storage account
 * @command-handler-header (C) Web Species Ltd
 */
class Storage extends Microsoft_Console_Command
{
	const ACCOUNT = 'azurep';
	const KEY = 'RfdXBwu35jj8oxNJJ1TI+J1RakF6jPr8na2WOMp0NCNJvgZPsx8iGNgUnfWW/1z1tY0EQZ/dfdInRyxwWh4htw==';

	/**
	 * @command-name create-app-container
	 * @command-description Create container for an app
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --app|-a App name.
	 * @command-parameter-for $identifier Microsoft_Console_Command_ParameterSource_Argv --identifier|-i Acl identifier.
	 */
	public function createAppContainerCommand($app, $identifier)
	{
		$storageClient = $this->getClient();
		
		$container = $this->getContainerName($app);
		
		try {
			$storageClient->createContainerIfNotExists($container);
		} catch (Exception $e) {
			print 'Failed to create a container' . PHP_EOL;
			return;
		}
		
		$identifier = new Microsoft_WindowsAzure_Storage_SignedIdentifier($identifier, $this->isoDate(), $this->isoDate(strtotime('+10 years')), 'r');
		
		try {
			$storageClient->setContainerAcl($container, Microsoft_WindowsAzure_Storage_Blob::ACL_PRIVATE, array($identifier));
		} catch (Exception $e) {
			print 'Failed to set ACL' . PHP_EOL;
			return;
		}		
	}

	/**
	 * @command-name store
	 * @command-description Store data in Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $from Microsoft_Console_Command_ParameterSource_Argv --from|-f Required. From filename.
	 */
	public function storeCommand($container, $name, $from)
	{
		$storageClient = $this->getClient();

		if (!$storageClient->containerExists($container)) {
			print 'Container doesn\'t exist' . PHP_EOL;
			return;
		}
		
		$storageClient->putBlob($container, $name, $from);
	}
	
	/**
	 * @command-name store-archive
	 * @command-description Store archived folder
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --app|-a App name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $folder Microsoft_Console_Command_ParameterSource_Argv --folder|-f Required. From folder.
	 */
	public function storeArchiveCommand($app, $name, $folder)
	{
		$container = $this->getContainerName($app);
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
	 * @command-name get-signed-url
	 * @command-description Get signed URL for a specific blog
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --app|-a App name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $identifier Microsoft_Console_Command_ParameterSource_Argv --identifier|-i Acl identifier.
	 */
	public function getSignedUrl($app, $name, $identifier)
	{
		$container = $this->getContainerName($app);
	
		$storageClient = $this->getClient();
		
		$signed = $storageClient->generateSharedAccessUrl($container, $name, 'b', '', '', '', $identifier);
		
		// replace buggy query string params which are not needed
		$signed = str_replace(array('se=', 'sp='), '', $signed);
		$signed = str_replace('&&', '&', $signed);
		$signed = str_replace('?&', '?', $signed);
		
		print $signed . PHP_EOL;
	}
	
	private function getClient()
	{
		return new Microsoft_WindowsAzure_Storage_Blob(str_replace('http://', '', Microsoft_WindowsAzure_Storage::URL_CLOUD_BLOB), self::ACCOUNT, self::KEY);
	}
	
	private function getContainerName($app) {
		return $app;
	}
	
	private function isoDate($timestamp = null) 
	{        
	    $tz = @date_default_timezone_get();
	    @date_default_timezone_set('UTC');
	    
	    if (is_null($timestamp)) {
	        $timestamp = time();
	    }
	        
	    $returnValue = str_replace('+00:00', '.0000000Z', @date('c', $timestamp));
	    @date_default_timezone_set($tz);
	    return $returnValue;
	}
}

Microsoft_Console_Command::bootstrap($_SERVER['argv']);