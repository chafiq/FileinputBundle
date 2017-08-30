<?php
/**
 * Created by PhpStorm.
 * User: nbourdiec
 * Date: 14/06/2017
 * Time: 16:43
 */

namespace EMC\FileinputBundle\Driver;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class CalameoDriver implements DriverInterface
{
    const UPLOAD_URL = 'http://upload.calameo.com/1.0';
    const DEFAULT_URL = 'http://api.calameo.com/1.0';
    const DOWNLOAD_URL = 'http://www.calameo.com/download/';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @var int
     */
    protected $subscriptionId;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $kernelRootDir;

    /**
     * @var string
     */
    protected $imageCacheDir;

    /**
     * @var string
     */
    protected $configCacheDir;

    /**
     * CalameoDriver constructor.
     *
     * @param $apiKey
     * @param $apiSecret
     * @param $settings
     */
    public function __construct($apiKey, $apiSecret, $susbscriptionId, $settings, $kernelRootDir, $imageCacheDir, $configCacheDir)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->subscriptionId = $susbscriptionId;
        $this->settings = $settings;
        $this->settings['apikey'] = $this->apiKey;

        // Directories // Cache Directories
        $this->kernelRootDir = $kernelRootDir;
        $this->imageCacheDir = $imageCacheDir;
        $this->configCacheDir = $configCacheDir;

        // API Client default URI -- Can be overriden if necessary !
        $this->client = new Client(['base_uri' => self::DEFAULT_URL]);
    }

    /**
     * Upload a file on Calameo Server.
     * See http://help.calameo.com/index.php?title=API:API.publish for more info
     *
     * @param $pathname
     * @param array $settings
     *
     * @return mixed
     * @throws \Exception
     */
    public function upload($pathname, array $settings)
    {
        if (isset($settings['book_id'])) {
            return $this->update($pathname, $settings);
        }

        $defaultParams = [
            'action' => 'API.publish',
            'subscription_id' => $this->subscriptionId,
            'category' => 'MISC',
            'format' => 'MISC',
            'dialect' => 'en',
            'publishing_mode' => 2,
            'private_url' => 1,
            'is_published' => 1,
            'subscribe' => 1,
            'download' => 2,
            'print' => 2,
        ];
        $params = array_replace_recursive($this->settings, $defaultParams, $settings);

        return $this->uploadFile($pathname, $params);
    }

    /**
     * Upload a file to Calameo servers.
     * @param $pathname
     * @param array $params
     * @return mixed
     */
    protected function uploadFile($pathname, array $params)
    {
        rename($pathname, $pathname.'.pdf');
        $pathname = $pathname.'.pdf';
        // Publish message
        $response = $this->client->request(
            'POST',
            self::UPLOAD_URL,
            [
                'query' => $this->configureQuerySettings($params),
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($pathname, 'r'),
                    ],
                ],
            ]
        );

        $data = $this->getData($response);

        return $data['content']['ID'];
    }

    /**
     * Updates the file on Calameo server
     * See http://help.calameo.com/index.php?title=API:API.revise for more info
     *
     *  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Does not update file properties.
     *  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     *
     * @param $pathname
     * @param array $settings
     *
     * @return bool
     */
    public function update($pathname, array $settings = [])
    {
        $params = [
            'action' => 'API.revise',
            'subscritpion_id' => $this->subscriptionId,
            'book_id' => $settings['book_id']
        ];

        $info = $this->uploadFile($pathname, array_replace_recursive($this->settings, $params));
        $this->updateFileProperties($settings);

        return $info;
    }

    /**
     * Updates file properties on Calameo server.
     *
     * See http://help.calameo.com/index.php?title=API:API.updateBook for more info
     * @param array $settings
     * @return array
     */
    public function updateFileProperties(array $settings)
    {
        $defaultParams = ['action' => 'API.updateBook'];

        return $data = $this->getData($this->client->get('', [
            'query' => $this->configureQuerySettings(
                array_replace_recursive($this->settings, $defaultParams, $settings)
            )
        ]));
    }

    public function get($filePath)
    {
        $path = sprintf('%s%s.json', $this->configCacheDir, $filePath);

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }

        //Get file Info from Calameo
        $params['book_id'] = $filePath;
        $params['action'] = 'API.getBookInfos';

        $data = $this->getData($this->client->get('', [
            'query' => $this->configureQuerySettings(array_replace_recursive($this->settings, $params))
        ]));

        return $data['content'];
    }

    public function delete($pathname)
    {
        $params = [
            'book_id' => $pathname,
            'action'  => 'API.deleteBook',
        ];
        $data = $this->getData($this->client->get('', [
            'query' => $this->configureQuerySettings(array_replace_recursive($this->settings, $params))
        ]));
      
        return ($data['status'] === 'ok') ;
    }

    public function getUrl($pathname)
    {
        $data = $this->get($pathname);

        return $data ? $data['ViewUrl'] : null;
    }

    public function getThumbnail($pathname)
    {
        $data = $this->get($pathname);

        return $data ? $data['ThumbUrl'] : null;
    }

    public function render($pathname)
    {
        // TODO: Implement render() method.
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function configureQuerySettings(array $params)
    {
        $signature = $this->apiSecret;
        ksort($params);

        foreach ($params as $key => $value) {
            $signature .= $key.''.$value;
        }

        $params['signature'] = md5($signature);
        return $params;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws \Exception
     */
    protected function getData(ResponseInterface $response){
        $data = json_decode($response->getBody()->getContents(), true)['response'];

        if (array_key_exists('error', $data) && isset($data['error'])) {
            throw new \Exception($data['error']['message'], $data['error']['code']);
        }

        return $data;
    }


    public function getName()
    {
        return 'calameo';
    }
}