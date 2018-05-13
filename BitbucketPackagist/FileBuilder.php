<?php

namespace BitbucketPackagist;

class FileBuilder
{

    private $params;

    public function __construct($params, $composer) {
        $this->params = $params;
        $this->loadGuzzleFunctions($composer);
        $this->updateSatisFile();
    }

    private function updateSatisFile() {
        $data = $this->getSatisFile();

        $data = json_decode($data, true);
        $data['repositories'] = $this->getRepos();
        $data['output-dir'] = $this->params['output-dir'] ? $this->params['output-dir'] : $data['output-dir'];

        $fp = fopen($this->getSatisFilePath(), 'w');
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fp);
    }


    private function getSatisFilePath() {
        return $this->params["rootpath"] . Builder::SATIS_FILENAME;
    }

    private function getSatisBaseFilePath() {
        return __DIR__ . "/../" . Builder::SATIS_BASE_FILENAME;
    }

    private function getSatisFile() {
        return file_get_contents($this->getSatisBaseFilePath());
    }

    private function getRepos() {
        
        if( empty($this->params['oauth']['oauth_consumer_key']) ||
            empty($this->params['oauth']['oauth_consumer_secret']) ||
            empty($this->params['team'])
        ) {
            return [];
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

        print("Found " . count($totalRows) . " repositories!\n");
        
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

    public function loadGuzzleFunctions()
    {
        require_once $this->params['rootpath'] . "/vendor/guzzlehttp/psr7/src/functions_include.php";
    }

}