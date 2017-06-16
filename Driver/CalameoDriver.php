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
    protected $uploadUrl = 'http://upload.calameo.com/1.0';
    protected $defaultUrl = 'http://api.calameo.com/1.0';
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
        $this->client = new Client(['base_uri' => $this->defaultUrl]);
    }

    /**
     * @param $pathname
     * @param array $settings
     * @return mixed
     * @throws \Exception
     */
    public function upload($pathname, array $settings)
    {
        $params = array_replace_recursive($this->settings, $settings);

        $params['action'] = 'API.publish';
        $params['apikey'] = $this->apiKey;
        $params['subscription_id'] = $this->subscriptionId;
        // TODO : Définir la categorie le format et la langue...
        $params['category'] = 'MISC';
        $params['format'] = 'MISC';
        $params['dialect'] = 'en';
        $params['publishing_mode'] = 2;
        $params['is_published'] = 1;
        $params['subscribe'] = 1;
        $params['private_url'] = 1;
        $params['output'] = 'JSON';

        // Replacing default value by custom ones
        // Set signature
        $params = $this->setSignature($params);

        rename($pathname, $pathname . '.pdf');
        $pathname = $pathname . '.pdf';

        // Publish message
        $response = $this->client->request(
            'POST',
            'http://upload.calameo.com/1.0',
            [
                'query' => $params,
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($pathname,'r')
                    ],
                ]
            ]
        );

        $data = $this->getCalameoObject($response);

        return $data->content->ID;
    }

    public function get($filePath)
    {
        $path = sprintf('%s%s.json', $this->configCacheDir, $filePath);

        if (file_exists($path)){
            return json_decode(file_get_contents($path), true);
        }

        //Get file Info from Calameo
        $params['book_id'] = $filePath;
        $params['action'] = 'API.getBookInfos';
        $params = $this->setSignature(array_replace_recursive($this->settings, $params));

        $data = $this->getCalameoObject($this->client->get('', ['query' => $params]));

        return $data->content;
    }

    public function delete($pathname)
    {
        // TODO: Implement delete() method.
    }

    public function getUrl($pathname)
    {
        $data = $this->get($pathname);
        return $data ? $data->ViewUrl : null;
    }

    public function getThumbnail($pathname)
    {
        $data = $this->get($pathname);
        dump($data);
        return $data ? $data->ThumbUrl : null;
    }

    public function render($pathname)
    {
        // TODO: Implement render() method.
    }

    protected function getCalameoIdFromPath()
    {

    }

    /**
     * @param array $params
     * @return array
     */
    protected function setSignature(array $params)
    {
        $signature = $this->apiSecret;
        ksort($params);

        foreach ($params as $key => $value) {
            $signature.=$key.''.$value;
        }

        $params['signature'] = md5($signature);
        return $params;
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws \Exception
     */
    protected function getCalameoObject(ResponseInterface $response){
        $data = json_decode($response->getBody()->getContents())->response;
        if (property_exists($data, 'error')) {
            throw new \Exception($data->error->message, $data->error->code);
        }

        return $data;
    }

}