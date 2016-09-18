<?php

namespace EMC\FileinputBundle\Driver;

use Vimeo\Vimeo;
use EMC\FileinputBundle\Entity\FileInterface;

class VimeoDriver implements DriverInterface {
    
    /**
     * @var Vimeo
     */
    private $vimeo;

    /**
     * @var array
     */
    private $settings;
    
    /**
     * @var array
     */
    private $whitelist;
    
    /**
     * @var array
     */
    static private $cache = array();
    
    function __construct($clientId, $clientSecret, $accessToken, array $settings, array $whitelist) {
        $this->vimeo = new Vimeo($clientId, $clientSecret);
        $this->vimeo->setToken($accessToken);
        $this->settings = $settings;
        $this->whitelist = $whitelist;
    }
    
    public function upload($pathname, array $settings) {
        $video = $this->vimeo->upload($pathname, false);
        
        if (!preg_match('`/videos/[0-9]+`', $video)) {
            throw new \Exception('Unable to upload file.');
        }

        $settings = array_merge_recursive($this->settings, $settings);
        $response = $this->vimeo->request($video, $settings ?: $this->settings, 'PATCH');
        foreach($this->whitelist as $domain) {
            $response = $this->vimeo->request(sprintf('%s/privacy/domains/%s', $video, $domain), array(), 'PUT');
        }
        
        return $video;
    }
    
    public function get($video) {
        if (!isset(self::$cache[$video])) {
            $data = $this->vimeo->request($video);
            if ($data['status'] !== 200) {
                return null;
            }
            self::$cache[$video] = $data['body'];
        }
        return self::$cache[$video];
    }
    
    public function delete($video) {
        return $this->vimeo->request($video, array(), 'DELETE');
    }

    public function getUrl($pathname) {
        $data = $this->get($pathname);
        return $data ? $data['link'] : null;
    }

    public function getThumbnail($pathname) {
        $data = $this->get($pathname);
        return $data ? $data['pictures']['sizes'][3]['link_with_play_button'] : null;
    }
    
    public function render($pathname) {
        $data = $this->get($pathname);
        return $data ? $data['embed']['html'] : null;
    }

}
