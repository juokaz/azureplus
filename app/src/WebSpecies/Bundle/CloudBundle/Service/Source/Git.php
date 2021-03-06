<?php

namespace WebSpecies\Bundle\CloudBundle\Service\Source;

use WebSpecies\Bundle\CloudBundle\Entity\App;
use Symfony\Component\HttpKernel\Util\Filesystem;

class Git implements SourceInterface
{
    private $git_path;

    /**
     * @var \Symfony\Component\HttpKernel\Util\Filesystem
     */
    private $filesystem;

    public function __construct($git_path, $filesystem)
    {
        $this->git_path = $git_path;
        $this->filesystem = $filesystem;
    }

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
    public function checkout(App $app, $location)
    {
        if (file_exists($location)) {
            $old_version = $this->getCurrentVersion($location);
            $this->runCommand($location, 'pull');
            return $old_version !== $this->getCurrentVersion($location);
        } else {
            $this->filesystem->mkdir($location);
            $this->runCommand($location, 'clone ' . $app->getSource()->getGitRepository() . ' .');
            return true;
        }
    }

    /**
     * Get current commit name
     *
     * @param \WebSpecies\Bundle\CloudBundle\Entity\App $app
     * @param string $location
     * @return string
     */
    public function getCurrentCommitName(App $app, $location)
    {
        // -n1 is to get one last commit
        // %s format returns title of it
        $command = 'log -n1 --pretty="%s"';

        return trim($this->runCommand($location, $command));
    }

    /**
     * Get git current version
     *
     * @param string $location
     * @return string
     */
    private function getCurrentVersion($location)
    {
        // -n1 is to get one last commit
        // %H format returns hash of it
        $command = 'log -n1 --pretty="%H"';

        return trim($this->runCommand($location, $command));
    }

    /**
     * Idea from: http://github.com/ornicar/php-git-repo
     *
     * @throws \RuntimeException
     * @param string $location
     * @param string $command
     * @return string
     */
    private function runCommand($location, $command)
    {
        $command = preg_replace('/^git\s/', '', $command);

        $command = $this->git_path . ' ' . $command;

        $commandToRun = sprintf('cd %s && %s', escapeshellarg($location), $command);

        ob_start();
        passthru($commandToRun, $returnVar);
        $output = ob_get_clean();

        if(0 !== $returnVar) {
            // Git 1.5.x returns 1 when running "git status"
            if(1 === $returnVar && 0 === strncmp($command, 'git status', 10)) {
                // it's ok
            }
            else {
                throw new \RuntimeException(sprintf(
                    'Command %s failed with code %s: %s',
                    $commandToRun,
                    $returnVar,
                    $output
                ), $returnVar);
            }
        }

        return $output;
    }
}