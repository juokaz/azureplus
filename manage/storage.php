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
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --name|-n App name.
	 * @command-parameter-for $identifier Microsoft_Console_Command_ParameterSource_Argv --identifier|-i Acl identifier.
	 */
	public function createAppContainerCommand($app, $identifier)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob(str_replace('http://', '', Microsoft_WindowsAzure_Storage::URL_CLOUD_BLOB), self::ACCOUNT, self::KEY);
		
		$container = $app;
		
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