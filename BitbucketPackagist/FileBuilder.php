<?php

namespace BitbucketPackagist;

define("FILE", "bitbucket_packagist.json");
define("BASEFILE", "bitbucket_packagist_base.json");

class FileBuilder
{

    private $params;

    public function __construct($params, $composer) {
        $this->params = $params;
        $this->loadGuzzleFunctions($composer);
        $this->updateIncludeFile();
    }

    private function updateIncludeFile() {
        $data = $this->getIncludeFile();

        $data = json_decode($data, true);
        $data["name"] = $this->params["name"];
        $data["homepage"] = $this->params["homepage"];
        $data['repositories'] = $this->getRepos();

        $fp = fopen($this->getIncludeFilePath(), 'w');
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fp);
    }


    private function getIncludeFilePath() {
        return $this->params["filepath"] . FILE;
    }

    private function getIncludeBaseFilePath() {
        return __DIR__ . "/../" . BASEFILE;
    }

    private function getIncludeFile() {
        try {
            $data = file_get_contents($this->getIncludeFilePath());
        } catch(\Exception $e) {
            $data = file_get_contents($this->getIncludeBaseFilePath());
        }

        return $data;
    }

    private function getRepos() {
        
        if( empty($this->params['oauth']['oauth_consumer_key']) ||
            empty($this->params['oauth']['oauth_consumer_secret']) ||
            empty($this->params['team'])
        ) {
            return;
        }

        $repo = new \Bitbucket\API\Repositories();
        $repo->getClient()->addListener(
            new \Bitbucket\API\Http\Listener\OAuthListener($this->params['oauth'])
        );
        
        $page = new \Bitbucket\API\Http\Response\Pager($repo->getClient(), $repo->all($this->params['team']));
        
        $response = $page->getCurrent();
        
        $rows = $this->getRows($response);
        
        $totalRows = array();
        
        while($rows && count($rows) > 0) {
            $totalRows = array_merge($totalRows, $rows);
            $rows = self::getRows($page->fetchNext());
        }
        
        $return = array();
        
        foreach($totalRows as $row) {
            $links = $row['links']['clone'];
            $ssh = $links[array_search('ssh', array_column($links, 'name'))]['href'];
            $return[] = array(
            "type" => "vcs",
            "url" => $ssh
            );
        }
        
        return $return;
    
    }
    
    private function getRows($response) {
        if($response === null) {
            return;
        }
        $content = $response->getContent();
        if(empty($content)) {
            return;
        }
        $content = json_decode($content, true);
        if($rows = $content['values']) {
            if(count($rows) > 0) {
            return $rows;
            }
        }
    }

    public function loadGuzzleFunctions($composer)
    {
        require_once $composer->getConfig()->get('vendor-dir') . "/guzzlehttp/psr7/src/functions_include.php";
    }

}