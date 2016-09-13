<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Gedmo\Uploadable\UploadableListener;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Vimeo\Vimeo;

class VimeoUploadableListener extends UploadableListener implements UploadableListenerInterface {

    /**
     * @var Vimeo
     */
    private $vimeo;
    
    public function init($config) {
        $this->vimeo = new Vimeo($config['client_id'], $config['client_secret']);
        $this->vimeo->setToken($config['access_token'] ?: $this->getAccessToken($config['scope']));
    }
    
    protected function getAccessToken($scope) {
        $token = $this->vimeo->clientCredentials($scope);
        return $token['body']['access_token'];
    }
    
    public function moveFile(FileInfoInterface $fileInfo, $path, $filenameGeneratorClass = false, $overwrite = false, $appendNumber = false, $object) {
        $info = parent::moveFile($fileInfo, $path, $filenameGeneratorClass, $overwrite, $appendNumber, $object);
        
        $response = $this->vimeo->upload($fileInfo->getTmpName(), false);

        if (!preg_match('`/videos/[0-9]+`', $response)) {
            throw new \Exception('Unable to upload file.');
        }
        
        $info['filePath'] = $response;
        
        return $info;
    }
    
    public function doMoveFile($source, $dest, $isUploadedFile = true) {
        return true;
    }

    public function removeFile($filePath) {
        return $this->vimeo->request($filePath, array(), 'DELETE');
    }
}
