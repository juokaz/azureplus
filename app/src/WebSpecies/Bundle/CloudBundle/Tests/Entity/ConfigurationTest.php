<?php

namespace WebSpecies\Bundle\CloudBundle\Tests\Entity;

use WebSpecies\Bundle\CloudBundle\Entity\Configuration;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function testAppRootLeftTrimTest()
    {
        $c = new Configuration();
        $c->setAppRoot('/root');

        $this->assertSame('root', $c->getAppRoot());
    }

    public function testAppRootRightTrimTest()
    {
        $c = new Configuration();
        $c->setAppRoot('root/');

        $this->assertSame('root', $c->getAppRoot());
    }

    /**
     * @dataProvider approots
     */
    public function testAppRoot($approot, $index, $public)
    {
        $c = new Configuration();
        $c->setAppRoot($approot);

        $this->assertSame($index, $c->getRouter());
        $this->assertSame($public, $c->getPublicFolder());
    }

    public function testPhp52Root()
    {
        $c = new Configuration();
        $c->setPhpVersion(Configuration::PHP_52);

        $this->assertSame('D:\Program Files (x86)\PHP\v5.2\php-cgi.exe', $c->getPhpRoot());
    }

    public function testPhp53Root()
    {
        $c = new Configuration();
        $c->setPhpVersion(Configuration::PHP_53);

        $this->assertSame('D:\Program Files (x86)\PHP\v5.3\php-cgi.exe', $c->getPhpRoot());
    }

    public static function approots()
    {
        return array(
          array('', '', ''),
          array('public', '', 'public'),
          array('public/', '', 'public'),
          array('index.php', 'index.php', ''),
          array('public/index.php', 'index.php', 'public'),
        );
    }
}
