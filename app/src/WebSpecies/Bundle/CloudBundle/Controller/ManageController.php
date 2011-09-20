<?php

namespace WebSpecies\Bundle\CloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebSpecies\Bundle\CloudBundle\Form\Type\AppType;
use WebSpecies\Bundle\CloudBundle\Form\Model\App as AppModel;

class ManageController extends Controller
{
    public function indexAction()
    {
        $apps = $this->getAppsManager()->getUserApps($this->getUser());


        return $this->render('CloudBundle:Manage:index.html.twig', array(
            'apps' => $apps
        ));
    }

    public function newAction()
    {
        $app = new AppModel();

        $form = $this->createForm(new AppType(), $app);

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $this->getManager()->createApp($this->getUser(), $app);

                $this->container->get('session')->setFlash('success', sprintf('App "%s" created', $app->getName()));
                return $this->redirect($this->generateUrl('CloudBundle_apps'));
            }
        }
        
        return $this->render('CloudBundle:Manage:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function deleteAction($name)
    {
        $app = $this->getAppsManager()->getApp($name);
        
        if (!$app || $app->getUser()->getId() != $this->getUser()->getId()) {
            throw $this->createNotFoundException('App not found');
        }

        $this->getManager()->deleteApp($name);

        return $this->redirect($this->generateUrl('CloudBundle_apps'));
    }

    /**
     * @return \WebSpecies\Bundle\CloudBundle\Entity\AppManager
     */
    private function getAppsManager()
    {
        return $this->get('cloud.manager.app');
    }

    /**
     * @return \WebSpecies\Bundle\CloudBundle\Service\Manager
     */
    private function getManager()
    {
        return $this->get('cloud.service.manager');
    }

    /**
     * @return \WebSpecies\Bundle\CloudBundle\Entity\User
     */
    public function getUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();
    }
}