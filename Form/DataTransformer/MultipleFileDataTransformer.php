<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;

class MultipleFileDataTransformer implements DataTransformerInterface
{
    /**
     * @var UploadableManager
     */
    private $uploadableManager;
    
    /**
     * @var string
     */
    private $fileClass;
    
    /**
     * @var string
     */
    private $driver;
    
    function __construct(UploadableManager $uploadableManager, $fileClass) {
        $this->uploadableManager = $uploadableManager;
        $this->fileClass = $fileClass;
    }

    public function transform($files)
    {
        if ($files instanceof Collection) {
            return array('_path' => $files);
        }
        return array('_path' => new ArrayCollection());
    }

    public function reverseTransform($data)
    {
        $collection = $data['_path'];

        if ($collection instanceof PersistentCollection ) {
            $deletedIds = array();
            if (($deletedIds=$data['_delete']) !== null && strlen($deletedIds) > 0) {
                $deletedIds = array_filter(array_map('intval', explode(',', $deletedIds)));

                /* @var $file \EMC\FileinputBundle\Entity\FileInterface */
                foreach($collection as $file) {
                    if (in_array($file->getId(), $deletedIds, true)) {
                        $collection->removeElement($file);
                    }
                }
            }
        }

        if (count($data['path']) > 0) {
            foreach ($data['path'] as $uploadedFile) {
                if ($uploadedFile === null) {
                    continue;
                }
                $file = new $this->fileClass;
                $file->setPath($uploadedFile);
                $this->uploadableManager->markEntityToUpload($file, $uploadedFile);
                $collection->add($file);
            }
        }

        return $collection;
    }

    public function setDriver($driver) {
        $this->driver = $driver;
    }

}
