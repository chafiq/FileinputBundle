<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Exception\TransformationFailedException;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;

class FileDataTransformer implements DataTransformerInterface
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

    public function transform($file)
    {
        if ($file instanceof FileInterface) {
            return array('_path' => $file);
        }
        return array('_path' => null);
    }

    public function reverseTransform($data)
    {
        $file = $data['_path'];
        if ($data['path'] === null) {
            if ($file instanceof FileInterface && $file->getId() === (int) $data['_delete']) {
                return null;
            }
            return $file;
        }
        
        if ($data['path'] instanceof UploadedFile) {
            $file = new $this->fileClass;
            $file->setPath($data['path']);
            $this->uploadableManager->markEntityToUpload($file, $data['path'], $this->driver);

            return $file;
        }
        
        throw new TransformationFailedException('Fileinput data transform error!');
    }

    public function setDriver($driver) {
        $this->driver = $driver;
    }

}

