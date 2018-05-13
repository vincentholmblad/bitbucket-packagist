<?php

namespace BitbucketPackagist;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Builder
{

    public static function build(Event $event)
    {
        $composer = $event->getComposer();

        $config = $composer->getConfig();

        $params = array(
            "oauth" => array(
                "oauth_consumer_key" => $config->get('bitbucket_consumer_key'),
                "oauth_consumer_secret" => $config->get('bitbucket_consumer_secret')
            ),
            "team" => $config->get('bitbucket_team'),
            "homepage" => $config->get('bitbucket_homepage') ? $config->get('bitbucket_homepage') : "http://packages.example.org",
            "name" => $config->get('bitbucket_name') ? $config->get('bitbucket_name') : "bitbucket-packagist",
            "filepath" => $composer->getConfig()->get('vendor-dir') . "/../"
        );

        print("Updating local private packagist...\n");

        new FileBuilder($params, $composer);

    }

}