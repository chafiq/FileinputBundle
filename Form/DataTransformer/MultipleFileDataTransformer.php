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
        return [
            '_path' => $files instanceof Collection ? $files : new ArrayCollection(),
            'path' => [],
            'position' => '',
            'name' => ''
        ];
    }

    public function reverseTransform($data)
    {
        $collection = $data['_path'];

        if (isset($data['position']) && is_string($data['position'])) {
            $data['position'] = json_decode($data['position'], true);
        }
        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = json_decode($data['name'], true);
        }

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

            if (is_array($data['position']) && isset($data['position'][$idx])) {
                $file->setPosition($data['position'][$idx]);
            }

            if (is_array($data['name']) && isset($data['name'][$idx])) {
                $file->setName($data['name'][$idx]);
            }
        }

        foreach ($collection as $file) {
            $idx = $file->getPath() instanceof UploadedFile ? $file->getPath()->getClientOriginalName() : $file->getId();

            if (is_array($data['name']) && isset($data['name'][$idx])) {
                $file->setName($data['name'][$idx]);
            }
        }

        return $collection;
    }
}
