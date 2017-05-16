<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Exception\TransformationFailedException;
use EMC\FileinputBundle\Entity\FileInterface;

class FileDataTransformer extends AbstractDataTransformer
{
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
            if (isset($data['name'])) {
                $file->setName($data['name']);
            }
            return $file;
        }
        
        if ($data['path'] instanceof UploadedFile) {
            $file = new $this->fileClass;
            $file->setPath($data['path']);
            if (isset($data['name'])) {
                $file->setName($data['name']);
            }
            $this->markEntityToUpload($file, $data['path']);

            return $file;
        }
        
        throw new TransformationFailedException('Fileinput data transform error!');
    }
}

