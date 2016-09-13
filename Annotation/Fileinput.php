<?php

namespace EMC\FileinputBundle\Annotation;

/**
 * @Annotation
 */
class Fileinput {
    /**
     * @var string
     */
    private $driver;
    
    function __construct($driver) {
        $this->driver = $driver['value'];
    }

    function getDriver() {
        return $this->driver;
    }
}
