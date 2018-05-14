<?php

namespace vincentholmblad\bitbucket_packagist;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PreFileDownloadEvent;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class BitbucketPackagistPlugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        file_put_contents('/tmp/composer.log', __METHOD__ . "\n",FILE_APPEND);
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => 'installOrUpdate',
            'post-update-cmd' => 'installOrUpdate',            
        );
    }    
    
    public function installOrUpdate($event)
    {
        file_put_contents('/tmp/composer.log', __METHOD__ . "\n",FILE_APPEND);
        file_put_contents('/tmp/composer.log', get_class($event) . "\n",FILE_APPEND);            
        file_put_contents('/tmp/composer.log', $event->getName() . "\n",FILE_APPEND);                    
    }
}