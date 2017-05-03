<?php

namespace EMC\FileinputBundle\Entity;

interface FileInterface {
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();
    
    /**
     * Set name
     *
     * @param string $name
     *
     * @return File
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();
    
    /**
     * Set path
     *
     * @param string $path
     *
     * @return File
     */
    public function setPath($path);

    /**
     * Get path
     *
     * @return string
     */
    public function getPath();

    /**
     * Set mimeType
     *
     * @param string $mimeType
     *
     * @return File
     */
    public function setMimeType($mimeType);

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Set size
     *
     * @param string $size
     *
     * @return File
     */
    public function setSize($size);

    /**
     * Get size
     *
     * @return string
     */
    public function getSize();
    
    /**
     * @return string
     */
    public function getExtension();
    
    /**
     * 
     * @param int $dec
     * @return string
     */
    public function getHumanReadableSize($dec = 2);
    
    /**
     * @return boolean
     */
    public function isImage();
    
    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();
    
    /**
     * @return array
     */
    public function getMetadata();
}
