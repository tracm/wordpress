<?php

/**
 * Tracm API - PHP:  Wrapper for v1.0
 */
class TracmAPIExchange
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecretKey;

    /**
     * @var array
     */
    private $postFields;

    /**
     * @var array
     */
    private $getFields;

    /**
     * @var string
     */
    private $url;

    /*
     * Create the object and pass in the api keys.
     * Requires: apiKey and apiSecret parameters.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new Exception('You will need to install cURL, see: http://curl.haxx.se/docs/install.html');
        }

        if (!isset($settings['apiKey']) && !isset($settings['apiSecretKey'])) {
            throw new InvalidArgumentException('Make sure you pass the API Key and API Secret Key');
        }

        $this->apiKey = $settings['apiKey'];
        $this->apiSecretKey = $settings['apiSecretKey'];
    }

    /**
     * @param array $postFields
     * @throws Exception
     */
    public function setPostFields(array $postFields)
    {
        if (!is_null($this->getGetFields())) {
            throw new Exception('You can only send POST or GET fields');
        }

        $this->postFields = $postFields;
    }

    /**
     * @param array $getFields
     * @throws LogicException
     */
    public function setGetFields(array $getFields)
    {
        if (!is_null($this->getGetFields())) {
            throw new LogicException('You can only send POST or GET fields');
        }

        $search = array('#', ',', '+', ':');
        $replace = array('%23', '%2C', '%2B', '%3A');

        foreach ($getFields as &$field) {
            $field = str_replace($search, $replace, $field);
        }

        $this->getFields = $getFields;
    }

    /**
     * @return array
     */
    public function getGetFields()
    {
        return $this->getFields;
    }

    /**
     * @return array
     */
    public function getPostFields()
    {
        return $this->postFields;
    }

    /**
     * @return string
     */
    protected function getAuthorizationString()
    {
        $string = sprintf("?apiKey=%s&apiSecretKey=%s", $this->apiKey, $this->apiSecretKey);

        return $string;
    }

    /**
     * @param $url
     * @return string
     */
    public function makeRequest($url)
    {
        $getFields = $this->getGetFields();
        $postFields = $this->getPostFields();

        $this->url = $url.$this->getAuthorizationString();

        $options = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
            CURLOPT_URL            => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10
        );

        if (!is_null($postFields)) {
            $postFields = http_build_query($postFields);
            $options[CURLOPT_POSTFIELDS] = $postFields;
        } elseif ($getFields !== '') {
            $options[CURLOPT_URL] = $this->url . '&' . http_build_query($getFields);
        }

        $feed = curl_init();
        curl_setopt_array($feed, $options);

        $json = curl_exec($feed);
        curl_close($feed);

        return $json;
    }
}
