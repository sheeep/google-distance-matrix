<?php

namespace Valerian\GoogleDistanceMatrix;

use GuzzleHttp\Client;

class GoogleDistanceMatrix
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $origin;

    /**
     * @var array
     */
    private $destination;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $units;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $avoid;

    /**
     * URL for API
     */
    const URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    const MODE_DRIVING = 'driving';
    const MODE_WALKING = 'walking';
    const MODE_BICYCLING = 'bicycling';
    const MODE_TRANSIT = 'transit';

    const UNITS_METRIC = 'metric';
    const UNITS_IMPERIAL = 'imperial';

    const AVOID_TOOLS = 'tolls';
    const AVOID_HIGHWAYS = 'highways';
    const AVOID_FERRIES = 'ferries';
    const AVOID_INDOOR = 'indoor';

    /**
     * GoogleDistanceMatrix constructor.
     *
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language = 'en')
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $units
     * @return $this
     */
    public function setUnits($units = self::UNITS_METRIC)
    {
        $this->units = $units;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnits()
    {
        return $this->units;
    }

    /**
     * @param string $origin (for more values use addOrigin method instead)
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = array($origin);
        return $this;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function addOrigin($origin)
    {
        $this->origin[] = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $destination (for more values use addDestination method instead)
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = array($destination);
        return $this;
    }

    /**
     * @param string $destination
     * @return $this
     */
    public function addDestination($destination)
    {
        $this->destination[] = $destination;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode = 'driving')
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $avoid (for more values use | as separator)
     * @return $this
     */
    public function setAvoid($avoid)
    {
        $this->avoid = $avoid;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvoid()
    {
        return $this->avoid;
    }

    /**
     * @return \stdClass
     * @throws \Exception
     */
    public function sendRequest()
    {
        $this->validate();
        $data = [
            'key' => $this->apiKey,
            'language' => $this->language,
            'origins' => count($this->origin) > 1 ? implode('|', $this->origin) : $this->origin[0],
            'destinations' => count($this->destination) > 1 ? implode('|', $this->destination) : $this->destination[0],
            'mode' => $this->mode,
            'avoid' => $this->avoid,
            'units' => $this->units
        ];
        $parameters = http_build_query($data);
        $url = self::URL.'?'.$parameters;
        $response = $this->request('GET', $url);
        if ($response->getStatusCode() != 200) {
            throw new \Exception('Response with status code '.$response->getStatusCode());
        }
        
        return json_decode(($response->getBody()->getContents()));
    }
    
    /**
     * @param string $type
     * @param string $url
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function request($type = 'GET', $url)
    {
        $client = new Client();
        $response = $client->request($type, $url);
        return $response;
    }

    private function validate()
    {
        if (empty($this->getOrigin())) {
            throw new Exception('Origin must be set.');
        }
        if (empty($this->getDestination())) {
            throw new Exception('Destination must be set.');
        }
    }
}
