<?php

namespace WebSpecies\Bundle\CloudBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AppType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name');
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
