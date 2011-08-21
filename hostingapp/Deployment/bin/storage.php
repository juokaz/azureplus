<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once '../Assets/PHPAzure/Microsoft/Console/Command.php';
require_once '../Assets/PHPAzure/Microsoft/WindowsAzure/Storage/Blob.php';

/**
 * Stpre command
 * 
 * @command-handler store
 * @command-handler-description Store data in azure storage account
 * @command-handler-header (C) Web Species Ltd
 */
class Store
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
	 * @command-name retrieve
	 * @command-description Retreive data from Azure cloud
	 * @command-parameter-for $container Microsoft_Console_Command_ParameterSource_Argv --container|-c Required. Container name.
	 * @command-parameter-for $name Microsoft_Console_Command_ParameterSource_Argv --name|-n Required. Blob name.
	 * @command-parameter-for $to Microsoft_Console_Command_ParameterSource_Argv --to|-t Required. To filename.
	 */
	public function retrieveCommand($container, $name, $to)
	{
		$storageClient = new Microsoft_WindowsAzure_Storage_Blob('blob.core.windows.net', self::ACCOUNT, self::KEY);

		$storageClient->getBlob($container, $name, $to);
	}
}

Microsoft_Console_Command::bootstrap($_SERVER['argv']);