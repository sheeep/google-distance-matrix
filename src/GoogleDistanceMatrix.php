<?php

namespace CiroVargas\GoogleDistanceMatrix;

use GuzzleHttp\Client;
use CiroVargas\GoogleDistanceMatrix\Response\GoogleDistanceMatrixResponse;

class GoogleDistanceMatrix
{

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $origins;

    /**
     * @var array
     */
    private $destinations;

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
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey() : string
    {
        return $this->apiKey;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language = 'en-US') : GoogleDistanceMatrix
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage() : string
    {
        return $this->language;
    }

    /**
     * @param string $units
     * @return $this
     */
    public function setUnits($units = self::UNITS_METRIC) : GoogleDistanceMatrix
    {
        $this->units = $units;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnits() : string
    {
        return $this->units;
    }

    /**
     * @param string $origin (for more values use addOrigin method instead)
     * @return $this
     */
    public function setOrigin($origin) : GoogleDistanceMatrix
    {
        $this->origins = array($origin);
        return $this;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function addOrigin($origin) : GoogleDistanceMatrix
    {
        $this->origins[] = $origin;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrigins() : array
    {
        return $this->origins;
    }

    /**
     * @param string $destination (for more values use addDestination method instead)
     * @return $this
     */
    public function setDestination($destination) : GoogleDistanceMatrix
    {
        $this->destinations = array($destination);
        return $this;
    }

    /**
     * @param string $destination
     * @return $this
     */
    public function addDestination($destination) : GoogleDistanceMatrix
    {
        $this->destinations[] = $destination;
        return $this;
    }

    /**
     * @return array
     */
    public function getDestinations() : array
    {
        return $this->destinations;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode = 'driving') : GoogleDistanceMatrix
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode() : string
    {
        return $this->mode;
    }

    /**
     * @param string $avoid (for more values use | as separator)
     * @return $this
     */
    public function setAvoid($avoid) : GoogleDistanceMatrix
    {
        $this->avoid = $avoid;
        return $this;
    }

    /**
     * @return string
     */
    public function getAvoid() : string
    {
        return $this->avoid;
    }

    /**
     * @return GoogleDistanceMatrixResponse
     * @throws \Exception
     */
    public function sendRequest() : GoogleDistanceMatrixResponse
    {
        $this->validateRequest();
        $data = [
            'key' => $this->apiKey,
            'language' => $this->language,
            'origins' => count($this->origins) > 1 ? implode('|', $this->origins) : $this->origins[0],
            'destinations' => count($this->destinations) > 1 ? implode('|', $this->destinations) : $this->destinations[0],
            'mode' => $this->mode,
            'avoid' => $this->avoid,
            'units' => $this->units
        ];
        $parameters = http_build_query($data);
        $url = self::URL.'?'.$parameters;

        return $this->request('GET', $url);
    }

    /**
     * @param string $type
     * @param string $url
     * @return GoogleDistanceMatrixResponse
     */
    private function request($type, $url) : GoogleDistanceMatrixResponse
    {
        $client = new Client();
        $response = $client->request($type, $url);

        if ($response->getStatusCode() != 200) {
            throw new \Exception('Response with status code '.$response->getStatusCode());
        }

        $responseObject = new GoogleDistanceMatrixResponse(json_decode($response->getBody()->getContents()));

        $this->validateResponse($responseObject);

        return $responseObject;
    }

    private function validateResponse(GoogleDistanceMatrixResponse $response) : void
    {

        switch ($response->getStatus()) {
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_OK:
                break;
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_INVALID_REQUEST:
                throw new Exception\ResponseException("Invalid request.", 1);
                break;
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_MAX_ELEMENTS_EXCEEDED:
                throw new Exception\ResponseException("The product of the origin and destination exceeds the limit per request.", 2);
                break;
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_OVER_QUERY_LIMIT:
                throw new Exception\ResponseException("The service has received too many requests from your application in the allowed time range.", 3);
                break;
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_REQUEST_DENIED:
                throw new Exception\ResponseException("The service denied the use of the Distance Matrix API service by your application.", 4);
                break;
            case GoogleDistanceMatrixResponse::RESPONSE_STATUS_UNKNOWN_ERROR:
                throw new Exception\ResponseException("Unknown error.", 5);
                break;
            default:
                throw new Exception\ResponseException(sprintf("Unknown status code: %s",$response->getStatus()), 6);
                break;
        }
    }

    private function validateRequest() : void
    {
        if (empty($this->getOrigins())) {
            throw new Exception\OriginException('Origin must be set.');
        }
        if (empty($this->getDestinations())) {
            throw new Exception\DestinationException('Destination must be set.');
        }
    }
}
