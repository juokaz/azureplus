<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Internal;

use WebSpecies\Bundle\CloudBundle\Entity\App;
    
class Packager
{
    private $web_config;

    private $php;
    
    public function __construct($web_config, $php)
    {
        $this->web_config = $web_config;
        $this->php = $php;
    }

    public function createPackage(App $app, $file, $folder)
    {
        $zip = new \ZipArchive();

		// open archive
		if ($zip->open($file, \ZIPARCHIVE::CREATE) !== TRUE) {
			throw new \RuntimeException ("Could not open archive");
		}

		// initialize an iterator
		// pass it the directory to be processed
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS));

		// iterate over the directory
		// add each file found to the archive
		foreach ($iterator as $key=>$value) {
			// fix file name
			$name_ = str_replace($folder, '', $key);

            // check for ignored files and folders
            foreach (array('.git', 'web.config', 'php.ini') as $pattern) {
                if (strpos($name_, $pattern) !== false) {
                    continue 2;
                }
            }

			if (!$zip->addFile(realpath($key), $name_)) {
			    throw new \RuntimeException ("ERROR: Could not add file: $key");
		    }
		}

        // Add web.config file
        $zip->addFromString('web.config', $this->getWebConfig($app));

        // Add web.config file
        $zip->addFromString('php.ini', $this->getPhpConfig($app));

		// close and save archive
		$zip->close();
    }

    /**
     * Get IIS web.config file to the package
     *
     * @param \ZipArchive $zip
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return void
     */
    private function getWebConfig(App $app)
    {
        $router = $app->getConfiguration()->getRouter();
        $public = $app->getConfiguration()->getPublicFolder();

        $template = file_get_contents($this->web_config);
        $template = str_replace('%PUBLIC_FOLDER%', $public, $template);

        if ($router) {
            $template = str_replace('%ROUTER_ENABLE%', 'true', $template);
        } else {
            $template = str_replace('%ROUTER_ENABLE%', 'false', $template);
        }

        // some apps might not have a router file, but should still have a default document
        $index = $router ?: 'index.php';

        $template = str_replace('%INDEX_FILE%', $index, $template);

        $template = str_replace('%PHP_PATH%', $app->getConfiguration()->getPhpRoot(), $template);

        return $template;
    }

    /**
     * Get PHP configuration
     *
     * @throws \InvalidArgumentException
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @return string
     */
    private function getPhpConfig(App $app)
    {
        if (!file_exists($this->php[$app->getConfiguration()->getPhpVersion()])) {
            throw new \InvalidArgumentException('Unrecognized PHP version. No php.ini template available');
        }

        $template = file_get_contents($this->php[$app->getConfiguration()->getPhpVersion()]);

        return $template;
    }
}