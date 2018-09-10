<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultipleFileDataTransformer extends AbstractDataTransformer
{
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

        if ($collection instanceof PersistentCollection) {
            $deletedIds = json_decode($data['delete'], true) ?: [];
            $deletedIds = array_map('intval', $deletedIds);

            /* @var $file \EMC\FileinputBundle\Entity\FileInterface */
            foreach ($collection as $file) {
                if (in_array($file->getId(), $deletedIds, true)) {
                    $collection->removeElement($file);
                }
            }
        }

        if (count($data['path']) > 0) {
            /* @var $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
            foreach ($data['path'] as $uploadedFile) {
                if ($uploadedFile === null) {
                    continue;
                }
                $file = new $this->fileClass;
                $file->setPath($uploadedFile);
                $this->markEntityToUpload($file, $uploadedFile);
                $collection->add($file);
            }
        }

        foreach ($collection as $file) {
            $idx = $file->getPath() instanceof UploadedFile ? $file->getPath()->getClientOriginalName() : $file->getId();

            if (isset($data['position'][$idx])) {
                $file->setPosition($data['position'][$idx]);
            }

            if (isset($data['name'][$idx])) {
                $file->setName($data['name'][$idx]);
            }
        }

        foreach ($collection as $file) {
            $idx = $file->getPath() instanceof UploadedFile ? $file->getPath()->getClientOriginalName() : $file->getId();

            if (isset($data['name'])) {
                if (isset($data['name'][$idx])) {
                    $file->setName($data['name'][$idx]);
                }
            }
        }

        return $collection;
    }
}
