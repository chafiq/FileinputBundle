<?php

namespace EMC\FileinputBundle\Form\DataTransformer;

use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Resample\Resampler;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileDataTransformer extends AbstractDataTransformer
{
    public function transform($file)
    {
        if ($file instanceof FileInterface) {
            return ['_path' => $file];
        }

        return ['_path' => null];
    }

    public function reverseTransform($data)
    {
        $file = $data['_path'];

        // File from DB
        if ($data['path'] === null) {

            $deletedIds = json_decode($data['delete'], true) ?: [];
            $deletedIds = array_map('intval', $deletedIds);

            if ($file instanceof FileInterface && in_array($file->getId(), $deletedIds, true)) {
                return null;
            }

            if (isset($data['name'])) {
                $file->setName($data['name']);
            }

            return $file;
        }

        // File to Upload
        if ($data['path'] instanceof UploadedFile) {
            $file = $file ?: new $this->fileClass;
            $file->setPath($data['path']);

            if (isset($data['name'])) {
                $file->setName($data['name']);
            }
            $this->markEntityToUpload($file, $data['path'], $this->annotation);
            // Resampling image with IMAGICK methods
            if ($this->annotation && $resampling = $this->annotation->getResample() && $data['path']->getMimeType()) {
                $image = new \Imagick($file->getPath()->getPathname());
                $image->resampleImage(
                    isset($resampling['dpi']) ? $resampling['dpi'] : 72, // Default DPI X => 72
                    isset($resampling['dpi']) ? $resampling['dpi'] : 72, // Default DPI X => 72
                    isset($resampling['filter']) ? $resampling['filter'] : \Imagick::FILTER_UNDEFINED, // Default Filter => No filter
                    isset($resampling['blur']) ? $resampling['blur'] : 1 // default Blur = 1 => no changes
                );
                $image->writeImage();
            }

            return $file;
        }

        throw new TransformationFailedException('Fileinput data transform error!');
    }
}

