<?php

namespace WebSpecies\Bundle\CloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebSpecies\Bundle\CloudBundle\Form\Type\AppType;
use WebSpecies\Bundle\CloudBundle\Form\Model\App as AppModel;
use WebSpecies\Bundle\CloudBundle\Entity\App;
use Symfony\Component\Form\FormError;

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
        $app_model = new AppModel();
        $type = new AppType();

        $form = $this->createForm($type, $app_model);

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                try {
                    $app = $type->getApp($app_model, $this->getAppsManager()->createApp($this->getUser()));
                    $this->getManager()->createApp($app);

                    $this->container->get('session')->setFlash('success', sprintf('App "%s" created', $app->getName()));
                    return $this->redirect($this->generateUrl('CloudBundle_manage'));
                } catch (\Exception $e) {
                    $form->addError(new FormError($e->getMessage()));
                }
            }
        }
        
        return $this->render('CloudBundle:Manage:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function viewAction($name)
    {
        $app = $this->getAppsManager()->getApp($name);

        if (!$app || $app->getUser()->getId() != $this->getUser()->getId()) {
            throw $this->createNotFoundException('App not found');
        }

        $type = new AppType('edit');
        $app_model = $type->getModel($app, new AppModel());

        $form = $this->createForm($type, $app_model);

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $app = $type->getApp($app_model, $app);
                $this->getAppsManager()->saveApp($app);

                $this->container->get('session')->setFlash('success', sprintf('App "%s" updated', $app->getName()));
                return $this->redirect($this->generateUrl('CloudBundle_view_app', array('name' => $app->getName())));
            }
        }

        return $this->render('CloudBundle:Manage:view.html.twig', array(
            'entity' => $app,
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

        return $this->redirect($this->generateUrl('CloudBundle_manage'));
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