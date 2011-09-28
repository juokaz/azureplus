<?php

namespace WebSpecies\Bundle\CloudBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use WebSpecies\Bundle\CloudBundle\Entity\Configuration;
use WebSpecies\Bundle\CloudBundle\Form\Model\App as AppModel;
use WebSpecies\Bundle\CloudBundle\Entity\App;

class AppType extends AbstractType
{
    private $edit = false;

    public function __construct($action = '')
    {
        $this->edit = $action == 'edit';    
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        // those fields cannot be changed after app creation
        if (!$this->edit) {
            $builder->add('name');

            $builder->add('location', 'choice', array(
                'choices' => array(
                    Configuration::LOCATION_NORTH_CENTRAL_US => 'North Central US - Chicago, Illinois',
                    Configuration::LOCATION_SOUTH_CENTRAL_US => 'South Central US - San Antonio, Texas',
                    Configuration::LOCATION_NORTH_EUROPE => 'North Europe - Amsterdam, Netherlands',
                    Configuration::LOCATION_WEST_EUROPE => 'West Europe - Dublin, Ireland',
                    Configuration::LOCATION_EAST_ASIA => 'East Asia - Honk Kong',
                    Configuration::LOCATION_SOUTHEAST_ASIA => 'Southeast Asia - Singapore',
                )
            ));
        } else {
            $builder->add('production', 'choice', array(
                'label' => 'Mode',
                'choices' => array(
                    true => 'Production',
                    false => 'Development',
                )
            ));
        }

        $builder->add('php_version', 'choice', array(
            'choices'   => array(
                Configuration::PHP_53 => 'PHP v5.3',
                Configuration::PHP_52 => 'PHP v5.2'
            )
        ));
        $builder->add('git_repository', null, array(
            'required' => false
        ));
        $builder->add('app_root', null, array(
            'required' => false
        ));
    }

    public function getApp(AppModel $model, App $app)
    {
        // those fields cannot be changed after app creation
        if (!$this->edit) {
            $app->setName($model->getName());
            $app->getConfiguration()->setLocation($model->getLocation());
        } else {
            $app->getConfiguration()->setProduction($model->getProduction());
        }

        $app->getConfiguration()->setAppRoot($model->getAppRoot());
        $app->getConfiguration()->setPhpVersion($model->getPhpVersion());

        $app->getSource()->setGitRepository($model->getGitRepository());

        return $app;
    }

    public function getModel(App $app, AppModel $model)
    {
        $model->setName($app->getName());
        
        $model->setAppRoot($app->getConfiguration()->getAppRoot());
        $model->setPhpVersion($app->getConfiguration()->getPhpVersion());
        $model->setLocation($app->getConfiguration()->getLocation());
        $model->setProduction($app->getConfiguration()->isProduction());
        
        $model->setGitRepository($app->getSource()->getGitRepository());

        return $model;
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'WebSpecies\Bundle\CloudBundle\Form\Model\App');
    }

    public function getName()
    {
        return 'app';
    }
}
