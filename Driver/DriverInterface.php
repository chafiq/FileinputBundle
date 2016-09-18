<?php

namespace EMC\FileinputBundle\Driver;

interface DriverInterface {
    public function upload($pathname, array $settings);
    public function get($pathname);
    public function delete($pathname);
    public function getUrl($pathname);
    public function getThumbnail($pathname);
    public function render($pathname);
}
