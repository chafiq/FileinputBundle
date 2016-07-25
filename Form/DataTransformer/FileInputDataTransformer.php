<?php

namespace EMC\FileInputBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use AppBundle\Entity\File;

class FileInputDataTransformer implements DataTransformerInterface
{
    /**
     *
     * @var UploadableManager
     */
    private $uploadableManager;
    
    /**
     * @var boolean
     */
    private $isMultiple;
    
    function __construct(UploadableManager $uploadableManager, $isMultiple) {
        $this->uploadableManager = $uploadableManager;
        $this->isMultiple = $isMultiple;
    }

    public function transform($files)
    {
        if ($files === null) {
            return array();
        }
        
        if ( $files instanceof File ) {
            return array($files);
        }
        
        return $files;
    }

    public function reverseTransform($files)
    {
        if ( $this->isMultiple ) {
            $entities = array();

            if (is_array($files)) {
                $files = new ArrayCollection($files);
            }
            
            /* @var $files \Doctrine\Common\Collections\ArrayCollection */
            $values = $files instanceof Collection ? array_combine($files->getKeys(), $files->getValues()) : $files;

            if ($files instanceof PersistentCollection ) {
                /* @var $files \Doctrine\ORM\PersistentCollection */
                $entities = $files->getSnapshot();

                $deletedIds = array();
                if (($deletedIds=$files->get('deletedIds')) !== null && strlen($deletedIds) > 0) {
                    $deletedIds = array_filter(array_map('intval', explode(',', $deletedIds)));

                    /* @var $file File */
                    foreach($entities as &$file) {
                        if (in_array($file->getId(), $deletedIds, true)) {
                            $file = null;
                        }
                    }
                    unset($file);

                    $entities = array_filter($entities);
                }
            }

            if (count($values['path']) > 0) {
                foreach ($values['path'] as $uploadedFile) {
                    if ($uploadedFile === null) {
                        continue;
                    }
                    $file = new File();
                    $file->setPath($uploadedFile);
                    $this->uploadableManager->markEntityToUpload($file, $file->getPath());
                    $entities[] = $file;
                }
            }
            
            $files->clear();
            foreach($entities as $entity) {
                $files->add($entity); 
            }
            
            return $files;
        } else {
            $file = isset($files[0]) && $files[0] instanceof File && $files[0]->getId() !== (int) $files['deletedIds'] ? $files[0] : null;

            if ($files['path'] === null) {
                return $file;
            }
            
            $file = new File();
            $file->setPath($files['path']);
            $this->uploadableManager->markEntityToUpload($file, $file->getPath());
            
            return $file;
        }
        
        throw new TransformationFailedException('Fileinput data transform error!');
    }
}

