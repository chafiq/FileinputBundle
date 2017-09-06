<?php

namespace EMC\FileinputBundle\Gedmo\Uploadable;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\UnitOfWork;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;

class CalameoUploadableListener extends UploadableListener implements UploadableListenerInterface {

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    protected function getSettings($object)
    {
        $settings = parent::getSettings($object);

        /* @var $uow UnitOfWork */
        $uow = $this->objectManager->getUnitOfWork();

        $changeSet = $uow->getEntityChangeSet($object);
        if ($this->objectManager->contains($object) && $object->getId() !== null && isset($changeSet['path'])) {
            $settings['book_id'] = $changeSet['path'][0];
        }

        return $settings;
    }
}
