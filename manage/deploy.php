<?php

require_once dirname(__FILE__) . '/lib/PHPAzure/Microsoft/AutoLoader.php';

/**
 * Deploy command
 * 
 * @command-handler deploy
 * @command-handler-description Deploy instances
 * @command-handler-header (C) Web Species Ltd
 */
class Deploy extends Microsoft_Console_Command
{
	const SUBSCRIPTION = '6e5926e8-54ff-4695-a357-4699583f2885';
	const CERTIFICATE = 'certs/mycert.pem';
	const PASSWORD = 'testing';
	const KEY = 'RfdXBwu35jj8oxNJJ1TI+J1RakF6jPr8na2WOMp0NCNJvgZPsx8iGNgUnfWW/1z1tY0EQZ/dfdInRyxwWh4htw==';
	
	const STORAGE = 'azurep';
	const COLLECTION = 'base';
	const LOCATION = 'base.cspkg';

	/**
	 * @command-name create-app
	 * @command-description Create app
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --app|-a Required. App name.
	 */
	public function createAppCommand($app)
	{
		$client = $this->getClient();
		 
		try {
			$client->createHostedService($app, $app, $app, 'West Europe');
		} catch (Exception $e) {
			print 'Error creating hosted service: ' . $e->getMessage() . PHP_EOL;
			return;
		}
		
		// Create app container
		$identifier = 'testings'; // @todo fix this
		if ('OK' !== ($putput = exec(sprintf('php storage.php create-app-container -a=%s -i=%s', $app, $identifier)))) {
			die ('Failed to create app container');
		}

		// Get app URL for future use
		$app_file = 'app.zip';
		$app_url = exec(sprintf('php storage.php get-signed-url -a=%s -n=%s -i=%s', $app, $app_file, $identifier));
		
		// App specific settings
		$configuration = 'data/ServiceConfiguration.cscfg';
		$conf = file_get_contents($configuration);
		$conf = str_replace('%APP_URL%', str_replace('&', '&amp;', $app_url), $conf);

		// Get base app location
		$blobClient = $client->createBlobClientForService(self::STORAGE);
		$package = $blobClient->getBlobInstance(self::COLLECTION, self::LOCATION);

		// Create deployment
		$client->createDeployment($app, 'production', 'deployment', 'deployment', $package->Url, $conf, true);

		// Wait for it to finish
		$client->waitForOperation();
	}
	
	/**
	 * @command-name delete-app
	 * @command-description Delete app
	 * @command-parameter-for $app Microsoft_Console_Command_ParameterSource_Argv --app|-a Required. App name.
	 */
	public function deleteAppCommand($app)
	{
		$client = $this->getClient();
		
		$client->deleteDeploymentBySlot($app, 'production');

		// Wait for it to finish
		$client->waitForOperation();

		$client->deleteHostedService($app);

		// Wait for it to finish
		$client->waitForOperation();
		
		if ('OK' !== ($putput = exec(sprintf('php storage.php delete-app-container -a=%s', $app)))) {
			die ('Failed to delete app container');
		}
	}
	
	/**
	 * @command-name store-base
	 * @command-description Store base app file
	 * @command-parameter-for $from Microsoft_Console_Command_ParameterSource_Argv --from|-f Required. From file.
	 */
	public function storeBaseCommand($from)
	{
		$collection = self::COLLECTION;
		$location = self::LOCATION;
		
		$client = $this->getClient();
		$blobClient = $client->createBlobClientForService(self::STORAGE);
		$blobClient->createContainerIfNotExists($collection);
		$blobClient->putBlob($collection, $location, $from);
		$package = $blobClient->getBlobInstance($collection, $location);
		
		print $package->Url;
	}
	
	private function getClient()
	{
		return new Microsoft_WindowsAzure_Management_Client(self::SUBSCRIPTION, self::CERTIFICATE, self::PASSWORD);
	}
}

Microsoft_Console_Command::bootstrap($_SERVER['argv']);