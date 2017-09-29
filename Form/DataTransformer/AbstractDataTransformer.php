<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use EMC\FileinputBundle\Annotation\Fileinput;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Gedmo\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class AbstractDataTransformer
 * @package EMC\FileinputBundle\Form\DataTransformer
 */
abstract class AbstractDataTransformer implements DataTransformerInterface
{

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

    /**
     * AbstractDataTransformer constructor.
     *
     * @param UploadableManager $uploadableManager
     * @param                   $fileClass
     */
    public function __construct(UploadableManager $uploadableManager, $fileClass)
    {
        $this->uploadableManager = $uploadableManager;
        $this->fileClass = $fileClass;
    }

    /**
     * @param Fileinput|null $annotation
     */
    public function setAnnotation(Fileinput $annotation = null)
    {
        $this->annotation = $annotation;
    }

    /**
     * @param null $owner
     */
    public function setOwner($owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * @param FileInterface $file
     * @param UploadedFile  $uploadedFile
     * @param null          $annotation
     */
    protected function markEntityToUpload(FileInterface $file, UploadedFile $uploadedFile, $annotation = null)
    {
        return $this->uploadableManager
            ->markEntityToUpload($file, $uploadedFile, $this->owner, $annotation ?: $this->annotation);
    }
}
