<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Internal;

use WebSpecies\Bundle\CloudBundle\Entity\App;
    
class Packager
{
    private $web_config;

    private $php_folder;
    
    public function __construct($web_config, $php_folder)
    {
        $this->web_config = $web_config;
        $this->php_folder = $php_folder;
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

        // Error mode, Detailed shows all errors of ISS while DetailedLocalOnly hides them 
        $template = str_replace('%ERROR_MODE%', $app->getConfiguration()->isProduction() ? 'DetailedLocalOnly' : 'Detailed', $template);

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
        $name = $this->php_folder . DIRECTORY_SEPARATOR . $app->getConfiguration()->getPhpVersion() . '.ini';

        if (!file_exists($name)) {
            throw new \InvalidArgumentException('Unrecognized PHP version. No php.ini template available');
        }

        $template = file_get_contents($name);

        // options for PHP
        $options = array('Azureplus' => array(
            'error_log' => 'D:\Windows\temp\php53_errors.log',
            'upload_tmp_dir' => 'D:\Windows\temp',
            'session.save_path' => 'D:\Windows\temp',
            'cgi.force_redirect' => '0',
            'cgi.fix_pathinfo' => '1',
            'fastcgi.impersonate' => '1',
            'fastcgi.logging' => '0',
            'max_execution_time' => '300',
            'date.timezone' => 'America/Los_Angeles',
            'extension_dir' => 'ext',
            'display_errors' => $app->getConfiguration()->isProduction() ? 'Off' : 'On'
        ));

        $template .= "\n" . $this->getIniFile($options, true);

        // Add some default extensions
        $template .= '
            [ExtensionList]
            extension=php_mysql.dll
            extension=php_mysqli.dll
            extension=php_mbstring.dll
            extension=php_gd2.dll
            extension=php_gettext.dll
            extension=php_curl.dll
            extension=php_exif.dll
            extension=php_xmlrpc.dll
            extension=php_openssl.dll
            extension=php_soap.dll
            extension=php_pdo_mysql.dll
            extension=php_pdo_sqlite.dll
            extension=php_imap.dll
            extension=php_tidy.dll
            extension=php_curl.dll';

        return $template;
    }

    private function getIniFile($assoc_arr, $has_sections = false)
    {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key=>$elem) {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2) {
                    if(is_array($elem2))
                    {
                        for($i=0;$i<count($elem2);$i++)
                        {
                            $content .= $key2."[] = \"".$elem2[$i]."\"\n";
                        }
                    }
                    else if($elem2=="") $content .= $key2." = \n";
                    else $content .= $key2." = \"".$elem2."\"\n";
                }
            }
        } else {
            foreach ($assoc_arr as $key=>$elem) {
                if(is_array($elem))
                {
                    for($i=0;$i<count($elem);$i++)
                    {
                        $content .= $key."[] = \"".$elem[$i]."\"\n";
                    }
                }
                else if($elem=="") $content .= $key." = \n";
                else $content .= $key." = \"".$elem."\"\n";
            }
        }
     
        return $content;
    }
}