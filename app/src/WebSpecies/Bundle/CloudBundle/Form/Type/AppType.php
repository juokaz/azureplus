<?php

namespace WebSpecies\Bundle\CloudBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use WebSpecies\Bundle\CloudBundle\Entity\Configuration;

class AppType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
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
        $builder->add('php_version', 'choice', array(
            'choices'   => array(
                Configuration::PHP_53 => 'PHP v5.3',
                Configuration::PHP_52 => 'PHP v5.2'
            )
        ));
        $builder->add('git_repository');
        $builder->add('app_root');
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
