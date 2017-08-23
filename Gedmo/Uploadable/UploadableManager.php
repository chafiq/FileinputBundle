<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Doctrine\ORM\EntityManager;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableRegistry as GedmoUploadableRegistry;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Annotation\Fileinput;

class UploadableManager
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var GedmoUploadableRegistry
     */
    private $registry;

    /**
     * UploadableManager constructor.
     *
     * @param EntityManager           $entityManager
     * @param GedmoUploadableRegistry $registry
     */
    public function __construct(EntityManager $entityManager, UploadableRegistry $registry)
    {
        $this->entityManager = $entityManager;
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
        if ($annotation && $annotation->getDriver()) {
            $driver = $annotation->getDriver();
        } else {
            $driver = 'default';
        }

        /* @var $uploadableManager \Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager */
        $uploadableManager = $this->registry->get($driver);
        $uploadableManager->markEntityToUpload($file, $fileInfo);
        $uploadableManager->getUploadableListener()
            ->addExtraFileInfoObjects($file, $owner, $annotation);

        if ($driver !== 'default') {
            $file->setDriver($driver);
        }
    }
}
