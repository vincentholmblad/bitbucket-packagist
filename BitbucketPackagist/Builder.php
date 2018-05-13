<?php

namespace BitbucketPackagist;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Builder
{

    const SATIS_FILENAME = "satis.json";

    const SATIS_BASE_FILENAME = "satis_base.json";

    const PACKAGES_FILENAME = "packages.json";

    const PACKAGES_BASE_FILENAME = "packages_base.json";

    public static function update(Event $event)
    {
        $composer = $event->getComposer();

        $config = $composer->getConfig();

        print("Updating local private packagist...\n");

        $params = self::getParams($event);

        $files = scandir($params['output-path'] . "include/"); 

        $data;

        foreach($files as $file)
        {
            $filePath = $params['output-path'] . "include/" . $file;
            if(is_file($filePath)){
                $data = json_decode(file_get_contents($filePath), true);
            }
        }

        if(array_key_exists("packages", $data) && $packages = $data["packages"]) {
            $repos = array();
            foreach($packages as $key => $package) {
                $repos[] = array(
                    "type" => "package",
                    "package" => $package[array_keys($package)[0]]
                );
            }
            $base = json_decode(file_get_contents($params["localpath"] . self::PACKAGES_BASE_FILENAME), true);

            $base["repositories"] = $repos;
    
            $fp = fopen($params["localpath"] . self::PACKAGES_FILENAME, 'w');
            fwrite($fp, json_encode($base, JSON_PRETTY_PRINT));
            fclose($fp);
        }
    }

    public static function build(Event $event)
    {
        $composer = $event->getComposer();

        $config = $composer->getConfig();

        print("Creating local private packagist...\n");

        new FileBuilder(self::getParams($event), $composer);

    }

    public static function getParams(Event $event)
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
            "rootpath" => $composer->getConfig()->get('vendor-dir') . "/../",
            "localpath" => __DIR__ . "/../"
        );

        $params["output-dir"] = $config->get('bitbucket_output_dir') ? $config->get('bitbucket_output_dir') : $params["localpath"] . "dist/";
        $params["output-path"] = $params["output-dir"];

        return $params;
    }

}