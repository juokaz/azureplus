<?php

namespace WebSpecies\Bundle\CloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CloudBundle:Default:index.html.twig');
    }
    
    public function todoAction()
    {
        return $this->render('CloudBundle:Default:todo.html.twig');
    }
}