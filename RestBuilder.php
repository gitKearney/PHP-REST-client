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
        $this->postData = http_build_query($data);
        return $this;
    }

    /**
     * @param $data
     * @return $this
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

    public function getLastResult()
    {
        return $this->result;
    }

    public function sendRequest()
    {
      $sendData = [];
       
      if (strcmp($this->method, 'POST') == 0 || strcmp($this->method, 'PUT') == 0) {
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
