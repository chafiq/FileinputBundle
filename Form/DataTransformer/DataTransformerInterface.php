<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace EMC\FileinputBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface as BaseDataTransformerInterface;

interface DataTransformerInterface extends BaseDataTransformerInterface {
    public function setDriver($driver);
}
