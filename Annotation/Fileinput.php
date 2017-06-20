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
    
    /**
     * @var array
     */
    private $accept;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var string
     */
    private $description;
    
    /**
     * @var array
     */
    private $settings;
    
    function __construct($data) {
        $this->driver = isset($data['driver']) ? $data['driver'] : null;
        $this->accept = isset($data['accept']) ? $data['accept'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->settings = isset($data['settings']) ? $data['settings'] : null;
    }

    function getDriver() {
        return $this->driver;
    }
    
    function getAccept() {
        return $this->accept;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }
    
    function getSettings() {
        return $this->settings;
    }
}
