<?php

namespace EMC\FileinputBundle\Driver;

use EMC\FileinputBundle\Entity\FileInterface;
use Vimeo\Vimeo;

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
	 * @var string
	 */
	private $kernelRootDir;

	/**
	 * @var string
	 */
	private $imageCacheDir;

	/**
	 * @var string
	 */
	private $configCacheDir;

	function __construct($clientId, $clientSecret, $accessToken, array $settings, array $whitelist, $kernelRootDir, $imageCacheDir, $configCacheDir) {
		$this->vimeo = new Vimeo($clientId, $clientSecret);
		$this->vimeo->setToken($accessToken);
		$this->settings = $settings;
		$this->whitelist = $whitelist;
		$this->kernelRootDir = $kernelRootDir;
		$this->imageCacheDir = $imageCacheDir;
		$this->configCacheDir = $configCacheDir;
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
		$path = sprintf('%s%s.json', $this->configCacheDir, $video);

		if (file_exists($path)){
			return json_decode(file_get_contents($path), true);
		}

		$data = $this->vimeo->request($video);

		if ($data['status'] !== 200) {
			return null;
		}

		$body = $data['body'];

		if (!isset($body['pictures']['sizes'][3]['link'])) {
			return $body;
		}

        if (!is_dir($_path=dirname($path))) {
            mkdir($_path, 0755, true);
        }

        file_put_contents($path, json_encode($body));

		return $body;
	}

	public function delete($video) {
		return $this->vimeo->request($video, array(), 'DELETE');
	}

	public function getUrl($pathname) {
		$data = $this->get($pathname);
		return $data ? $data['link'] : null;
	}

	public function getThumbnail($pathname) {
		$path = sprintf('%s/../web%s%s', $this->kernelRootDir, $this->imageCacheDir, $pathname);
		$url = sprintf('%s%s', $this->imageCacheDir, $pathname);

		if (file_exists($path)){
			return $url;
		}

		$data = $this->get($pathname);

		if ($data){
			$link = $data['pictures']['sizes'][3]['link'];

            if (!is_dir($_path=dirname($path))) {
                mkdir($_path, 0755, true);
            }

            copy($link, $path);
			return $url;
		}

		return null;
	}

	public function render($pathname) {
		$data = $this->get($pathname);
		return $data ? $data['embed']['html'] : null;
	}

    public function getName()
    {
        return 'vimeo';
    }
}
