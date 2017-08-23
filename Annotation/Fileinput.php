<?php

namespace EMC\FileinputBundle\Annotation;

/**
 * @Annotation
 */
class Fileinput
{
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

    /**
     * @var array
     */
    private $resample;

    /**
     * Fileinput constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->driver = isset($data['driver']) ? $data['driver'] : null;
        $this->accept = isset($data['accept']) ? $data['accept'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->settings = isset($data['settings']) ? $data['settings'] : null;
        $this->resample = isset($data['resample']) ? $data['resample'] : null;
    }

    /**
     * @return null|string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return array|null
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array|null
     */
    public function getResample()
    {
        return $this->resample;
    }

}
