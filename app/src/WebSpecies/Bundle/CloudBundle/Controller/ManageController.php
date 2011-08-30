<?php

namespace WebSpecies\Bundle\CloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ManageController extends Controller
{
    public function indexAction()
    {
        return $this->render('CloudBundle:Manage:index.html.twig');
    }

    public function appsAction()
    {
        return $this->render('CloudBundle:Manage:apps.html.twig');
    }

    public function newAction()
    {
        return $this->render('CloudBundle:Manage:new.html.twig');
    }

    public function deleteAction($name)
    {
        return $this->redirect($this->generateUrl('CloudBundle_apps'));
    }
}