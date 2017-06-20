<?php

namespace EMC\FileinputBundle\Driver;

interface DriverInterface {
    /**
     * @return bool
     */
    public function upload($pathname, array $settings);

    /**
     * @return array
     */
    public function get($pathname);

    /**
     * @return bool
     */
    public function delete($pathname);

    /**
     * @return string
     */
    public function getUrl($pathname);

    /**
     * @return string
     */
    public function getThumbnail($pathname);

    /**
     * @return string
     */
    public function render($pathname);

    /**
     * @return string
     */
    public function getName();
}
