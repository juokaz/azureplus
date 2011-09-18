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

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'WebSpecies\Bundle\CloudBundle\Form\Model\App');
    }

    public function getName()
    {
        return 'app';
    }
}
