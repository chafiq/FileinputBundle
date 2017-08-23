<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use EMC\FileinputBundle\Driver\DriverInterface;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use EMC\FileinputBundle\Entity\FileInterface;

/**
 * Class UploadableListener
 * @package EMC\FileinputBundle\Gedmo\Uploadable
 */
class UploadableListener extends DefaultUploadableListener implements UploadableListenerInterface
{

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        $subscribedEvents = parent::getSubscribedEvents();
        $subscribedEvents[] = 'postLoad';

        return $subscribedEvents;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof FileInterface && $object->getDriver() === $this->driver->getName()) {
            /** @var $object FileInterface */
            $object->setDriver($object->getDriver(), $this->driver);
        }
    }

    /**
     * @param FileInfoInterface $fileInfo
     * @param string            $path
     * @param bool              $filenameGeneratorClass
     * @param bool              $overwrite
     * @param bool              $appendNumber
     * @param object            $object
     *
     * @return array
     */
    public function moveFile(
        FileInfoInterface $fileInfo,
        $path,
        $filenameGeneratorClass = false,
        $overwrite = false,
        $appendNumber = false,
        $object
    ) {
        $settings = $this->getSettings($object);


        $info = parent::moveFile($fileInfo, $path, $filenameGeneratorClass, $overwrite, $appendNumber, $object);

        $info['filePath'] = $this->driver->upload($fileInfo->getTmpName(), $settings);

        return $info;
    }

    /**
     * @param string $source
     * @param string $dest
     * @param bool   $isUploadedFile
     *
     * @return bool
     */
    public function doMoveFile($source, $dest, $isUploadedFile = true)
    {
        return true;
    }

    /**
     * @param string $filePath
     *
     * @return bool
     */
    public function removeFile($filePath)
    {
        return $this->driver->delete($filePath);
    }

    /**
     * @param $object
     *
     * @return array
     */
    private function getSettings($object)
    {
        $oid = spl_object_hash($object);
        if (!isset($this->extraFileInfoObjects[$oid])) {
            throw new \RuntimeException;
        }

        $owner = $this->extraFileInfoObjects[$oid]['owner'];
        /* @var $annotation \EMC\FileinputBundle\Annotation\Fileinput */
        $annotation = $this->extraFileInfoObjects[$oid]['annotation'];

        $settings = $annotation->getSettings() ?: [];

        if ($annotation->getName() && method_exists($owner, $method = 'get' . ucfirst($annotation->getName()))) {
            $settings['name'] = call_user_func([$owner, $method]);
        }

        if ($annotation->getDescription() &&
            method_exists($owner, $method = 'get' . ucfirst($annotation->getDescription()))
        ) {
            $settings['description'] = call_user_func([$owner, $method]);
        }

        return $settings;
    }
}
