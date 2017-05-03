<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Annotation\Fileinput;

abstract class AbstractDataTransformer implements DataTransformerInterface {
    
    /**
     * @var UploadableManager
     */
    protected $uploadableManager;
    
    /**
     * @var string
     */
    protected $fileClass;
    
    /**
     * @var Fileinput
     */
    protected $annotation;
    
    /**
     * @var object
     */
    protected $owner;
    
    function __construct(UploadableManager $uploadableManager, $fileClass) {
        $this->uploadableManager = $uploadableManager;
        $this->fileClass = $fileClass;
    }
    
    function setAnnotation(Fileinput $annotation=null) {
        $this->annotation = $annotation;
    }

    function setOwner($owner=null) {
        $this->owner = $owner;
    }
    
    protected function markEntityToUpload(FileInterface $file, UploadedFile $uploadedFile) {
        return $this->uploadableManager->markEntityToUpload($file, $uploadedFile, $this->owner, $this->annotation);
    }
}
