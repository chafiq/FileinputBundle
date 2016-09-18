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
    private $title;
    
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
        $this->title = isset($data['title']) ? $data['title'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->settings = isset($data['settings']) ? $data['settings'] : null;
    }

    function getDriver() {
        return $this->driver;
    }
    
    function getAccept() {
        return $this->accept;
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->description;
    }
    
    function getSettings() {
        return $this->settings;
    }
}
