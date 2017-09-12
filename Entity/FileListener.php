<?php

namespace EMC\FileinputBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListener
{
    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * FileListener constructor.
     *
     * @param UploadableManager $uploadableManager
     */
    public function __construct(UploadableManager $uploadableManager)
    {
        $this->uploadableManager = $uploadableManager;
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        if ($event->getObject() instanceof FileInterface) {
            $this->onPrePersistOrUpdate($event->getObject());
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        if ($event->getObject() instanceof FileInterface) {
            $this->onPrePersistOrUpdate($event->getObject());
        }
    }

    public function onPrePersistOrUpdate(FileInterface $file)
    {
        if ($file->getPath() instanceof UploadedFile) {
            $this->uploadableManager->markEntityToUpload($file, $file->getPath());
        }
    }
}