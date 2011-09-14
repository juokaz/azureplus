<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Source;

use WebSpecies\Bundle\CloudBundle\Entity\App;

interface SourceInterface
{
    /**
     * Checkout app to a location
     *
     * Returns true if new files were placed
     * Returns false if it's at the latest version
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $location
     * @return bool
     */
    public function checkout(App $app, $location);
}