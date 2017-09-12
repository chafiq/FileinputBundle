<?php

namespace EMC\FileinputBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile as BaseUploadedFile;

class UploadedFile extends BaseUploadedFile
{
    public function isValid()
    {
        return true;
    }
}