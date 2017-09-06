<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Annotation\Fileinput;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableManager
{

    /**
     * @var UploadableRegistry
     */
    private $registry;
    
    function __construct(UploadableRegistry $registry) {
        $this->registry = $registry;
    }

    /**
     * This method marks an entity to be uploaded as soon as the "flush" method of your object manager is called.
     * After calling this method, the file info you passed is set for this entity in the listener. This is all it takes
     * to upload a file for an entity in the Uploadable extension.
     *
     * @param FileInterface  $file - The entity you are marking to "Upload" as soon as you call "flush".
     * @param mixed          $fileInfo - The file info object or array. In Symfony 2, this will be typically an UploadedFile instance.
     * @param null           $owner
     * @param Fileinput|null $annotation
     */
    public function markEntityToUpload(FileInterface $file, $fileInfo, $owner = null, Fileinput $annotation = null)
    {
        if ($file->getDriver()) {
            $driver = $file->getDriver();
        } else if ($annotation && $annotation->getDriver()) {
            $driver = $annotation->getDriver();
        } else {
            $driver = 'default';
        }

        /* @var $uploadableManager \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager */
        $uploadableManager = $this->registry->get($driver);
        $uploadableManager->markEntityToUpload($file, $fileInfo);

        if ($driver !== 'default') {
            $uploadableManager
                ->getUploadableListener()
                    ->addExtraFileInfoObjects($file, $owner, $annotation);
            $file->setDriver($driver);
        }
    }

    public function prePersist(LifecycleEventArgs $event) {
        if (!($file = $event->getObject()) instanceof FileInterface) {
            return;
        }
        if ($file->getPath() instanceof UploadedFile) {
            $this->markEntityToUpload($file, $file->getPath());
        }
    }
}
