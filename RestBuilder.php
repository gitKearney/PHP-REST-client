<?php

namespace restbuilder;

/**
 * Sends HTTP DELETE, GET, POST, PUT to a UIR
 * TODO: add PATCH
 */

class RestBuilder {
    protected $uri;
    protected $postData;
    protected $getData;
    protected $httpVerb;
    protected $allowedHttpVerbs;
    protected $result;
    protected $header;
    protected $contentTypeSet;

    /**
     * @param string $uri the URI to send the request to
     * @param string $method the HTTP verb to send
     * @param array  $data the data to be URL encoded
     *
     * @throws \Exception
     */
    public function __construct($uri = null, $method = null, $data = [])
    {
        $this->allowedHttpVerbs = ['GET', 'DELETE', 'POST', 'PUT'];
        $this->method = $method;
        $this->header = '';
        $this->contentTypeSet = false;

        # make the $method all capital letters
        if (! is_null($this->method)) {
            $this->method = strtoupper($this->method);
        }

        if (! is_null($this->method) && ! in_array($this->method, $this->allowedHttpVerbs)) {
            throw new \Exception('invalid HTTP verb (method) passed in');
        }

        # URL encode the data
        if (strcmp($this->method, 'GET') == 0) {
            $this->getData = http_build_query($data);
        } else {
            $this->postData = http_build_query($data);
        }

        $this->uri = $uri;
    }

    /**
     * @param string $header
     * @return RestBuilder
     */
    public function addHeader($header)
    {
        $this->header .= $header.PHP_EOL;
        return $this;
    }

    /**
     * @param array $data
     * @return RestBuilder
     */
    public function setPostData($data)
    {
        $this->postData = $data;
        return $this;
    }

    /**
     * @param $data
     * @return RestBuilder
     */
    public function setGetQueryString($data)
    {
        $this->getData = http_build_query($data);
        return $this;
    }

    /**
     * @param string$method
     * @return RestBuilder $this
     * @throws \Exception
     */
    public function setHttpVerb($method)
    {
        # make the $method all capital letters
        $this->method = strtoupper($method);

        if (! is_null($this->method) && ! in_array($this->method, $this->allowedHttpVerbs)) {
            throw new \Exception('invalid HTTP verb (method) passed in');
        }

        return $this;
    }

    /**
     * @param string $uri
     * @return RestBuilder
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Changes the data in the POST and PUT to JSON and sets the header to JSON
     * @return RestBuilder
     */
    public function sendAsJson()
    {
        if ($this->contentTypeSet) {
            return $this;    
        }

        $this->header .= 'content-type: application/json'.PHP_EOL;
        $this->postData = json_encode($this->postData);
        $this->contentTypeSet = true;

        return $this;
    }

    /**
     * Sends data as URL Form Encoded
     * @return RestBuilder
     */
    public function sendAsUrlFormEncoded()
    {
        if ($this->contentTypeSet) {
            return $this;
        }

        $this->header .= 'Content-type: application/x-www-form-urlencoded'.PHP_EOL;
        $this->contentTypeSet = true;
        $this->postData = http_build_query($this->postData);

        return $this;
    }

    /**
     * Returns the result from the last HTTP verb
     * @return array
     */
    public function getLastResult()
    {
      return $this->result;
    }

    /**
     * Opens up a connection to the URI using the HTTP verb specified
     * @return array
     */
    public function sendRequest()
    {
        $sendData = [];

        if (strcmp($this->method, 'POST') == 0 || strcmp($this->method, 'PUT') == 0) {
            if (! $this->contentTypeSet) {
                # default to sending data as URL Form Encoded
                $this->sendAsUrlFormEncoded();
            }

            $sendData = $this->postData;
        } elseif ($this->method == 'GET') {
            $header = 'Content-Type: text/html; charset=utf-8';
            $sendData = $this->getData;
        }

        $opts = [
            'http' => [
               'method'  => $this->method,
               'header'  => $this->header,
               'content' => $sendData,
            ],
        ];

        $context = stream_context_create($opts);

        $this->result = file_get_contents($this->uri, false, $context);
        return $this->result;
    }
}
