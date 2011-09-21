<?php

namespace WebSpecies\Bundle\CloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WebSpecies\Bundle\CloudBundle\Entity\App;
use Symfony\Component\HttpFoundation\Response;

class DeployController extends Controller
{
    public function deployAction($name)
    {
        $app = $this->getAppsManager()->getApp($name);

        if (!$app) {
            throw $this->createNotFoundException('App not found');
        }

        $type = $this->getRequest()->server->get('CONTENT_TYPE');

        if (!$type) {
            return new Response('Missing Content-Type or content type is unsupported', 415);
        }

        $archive = $this->getRequest()->getContent();

        try {
            $this->getService()->deployArchive($app, $type, $archive);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }

        return new Response('Deployed');
    }

    /**
     * @return \WebSpecies\Bundle\CloudBundle\Entity\AppManager
     */
    private function getAppsManager()
    {
        return $this->get('cloud.manager.app');
    }

    /**
     * @return \WebSpecies\Bundle\CloudBundle\Service\Deploy
     */
    private function getService()
    {
        return $this->get('cloud.service.deploy');
    }
}