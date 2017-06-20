<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use EMC\FileinputBundle\Entity\FileInterface;

class UploadableListener extends DefaultUploadableListener implements UploadableListenerInterface {

    /**
     * @var DriverInterface
     */
    private $driver;
    
    public function setDriver($driver) {
        $this->driver = $driver;
    }
    
    public function getSubscribedEvents() {
        $subscribedEvents = parent::getSubscribedEvents();
        $subscribedEvents[] = 'postLoad';
        return $subscribedEvents;
    }

    public function postLoad(LifecycleEventArgs $args) {
        $object = $args->getObject();

        if ($object instanceof FileInterface && ($object->getDriver() !== 'default' || !empty($object->getDriver()))) {
            $object->setDriver($object->getDriver(), $this->driver);
        }
    }
    
    public function moveFile(FileInfoInterface $fileInfo, $path, $filenameGeneratorClass = false, $overwrite = false, $appendNumber = false, $object) {
        $settings = $this->getSettings($object);

        
        $info = parent::moveFile($fileInfo, $path, $filenameGeneratorClass, $overwrite, $appendNumber, $object);
        
        $info['filePath'] = $this->driver->upload($fileInfo->getTmpName(), $settings);

        return $info;
    }
    
    public function doMoveFile($source, $dest, $isUploadedFile = true) {
        return true;
    }

    public function removeFile($filePath) {
        return $this->driver->delete($filePath);
    }
    
    private function getSettings($object) {
        
        $oid = spl_object_hash($object);
        if (!isset($this->extraFileInfoObjects[$oid])) {
            throw new \RuntimeException;
        }
        
        $owner = $this->extraFileInfoObjects[$oid]['owner'];
        /* @var $annotation \EMC\FileinputBundle\Annotation\Fileinput */
        $annotation = $this->extraFileInfoObjects[$oid]['annotation'];
        
        $settings = $annotation->getSettings() ?: array();

        if ($annotation->getName() && method_exists($owner, $method = 'get' . ucfirst($annotation->getName()))) {
            $settings['name'] = call_user_func(array($owner, $method));
        }
        
        if ($annotation->getDescription() && method_exists($owner, $method = 'get' . ucfirst($annotation->getDescription()))) {
            $settings['description'] = call_user_func(array($owner, $method));
        }

        return $settings;
    }
}
