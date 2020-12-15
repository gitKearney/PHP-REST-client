<?php
/**
 *     This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace RestClient;

use Exception;

/**
 * This class sends HTTP requests and follows HTTP forwarding requests (301 & 302)
 */
class RestClient {
    /** @var string|null  */
    protected ?string $uri;

    /** @var array */
    protected array $postData;

    /** @var string  */
    protected string $getData;

    /** @var mixed */
    protected $result;

    /** @var string */
    protected string $header;

    /** @var bool */
    protected bool $contentTypeSet;

    /** @var mixed */
    protected $method;

    const ALLOWED_VERBS = ['GET', 'DELETE', 'POST', 'PUT'];

    /**
     * @param string $uri the URI to send the request to
     * @param string $method the HTTP verb to send
     * @param array  $data the data to be URL encoded
     * @throws Exception
     */
    public function __construct($uri = null, $method = null, $data = [])
    {
        $this->method = $method;
        $this->header = '';
        $this->contentTypeSet = 'urlFormEncoded';

        # make the $method all capital letters
        if (!is_null($this->method)) {
            $this->method = strtoupper($this->method);
        }

        $this->method = 'GET';
        if (!is_null($this->method) && !in_array($this->method, self::ALLOWED_VERBS)) {
            $this->method = $method;
        }

        $this->postData = $data;
        $this->uri = $uri;
    }

    /**
     * @param string $header
     * @return RestClient
     */
    public function addHeader(string $header): RestClient
    {
        $this->header .= $header.PHP_EOL;
        return $this;
    }

    /**
     * @param array $data
     * @return RestClient
     */
    public function setBody(array $data): RestClient
    {
        $this->postData = $data;
        return $this;
    }

    /**
     * @param $data
     * @return RestClient
     */
    public function setGetQueryString($data): RestClient
    {
        $this->getData = http_build_query($data);
        return $this;
    }

    /**
     * @param string$method
     * @return RestClient $this
     */
    public function setMethod($method): RestClient
    {
        # make the $method all capital letters
        $this->method = strtoupper($method);
        if (! is_null($this->method) && ! in_array($this->method, self::ALLOWED_VERBS)) {
            // throw new Exception('invalid HTTP verb (method) passed in');
            $this->method = 'GET';
        }
        return $this;
    }

    /**
     * @param string $uri
     * @return RestClient
     */
    public function setUri($uri): RestClient
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Changes the data in the POST and PUT to JSON and sets the header to JSON
     * @return RestClient
     */
    public function sendAsJson(): RestClient
    {
        $this->contentTypeSet = 'json';
        return $this;
    }

    /**
     * Sends data as URL Form Encoded
     * @return RestClient
     */
    public function sendAsUrlFormEncoded(): RestClient
    {
        $this->contentTypeSet = 'urlFormEncoded';
        return $this;
    }

    /**
     * Returns the result from the last HTTP verb
     * @return mixed
     */
    public function getLastResult()
    {
        if (!$this->result) {
            return json_decode([]);
        }

        return json_decode($this->result, true);
    }

    /**
     * returns the POST data. Useful for after a request has been sent to 
     * see exactly what was sent to the URI
     * @return array
     */
    public function getPostData(): array
    {
        return $this->postData;
    }

    /**
     * Opens up a connection to the URI using the HTTP verb specified.
     * Communicates with the URI using HTTP 1.1.
     * Only redirects of type 301 or 302 are followed.
     * HTTP body defaults to type x-www-form-urlencoded
     * @return string
     */
    public function sendRequest(): string
    {
        $sendData = '';

        if ( strcmp($this->method, 'POST') == 0 || 
             strcmp($this->method, 'PUT') == 0) 
        {
            if ($this->contentTypeSet === 'urlFormEncoded')
            {
                # default to sending data as URL Form Encoded
                $this->header .= 'Content-Type: application/x-www-form-urlencoded'.PHP_EOL;
                $sendData = http_build_query($this->postData);
            } else {
                # send as JSON
                $this->header .= 'Content-Type: application/json; charset=utf-8'.PHP_EOL;
                $sendData = json_encode($this->postData);
                $this->header .= 'Content-Length: '.strlen($sendData).PHP_EOL;
            }

            echo $sendData, PHP_EOL;
        } elseif ($this->method === 'GET') {
            $this->header = 'Content-Type: text/html; charset=utf-8';
            $sendData = $this->getData;
        }

        # don't follow redirects, use HTTP protocol version 1.1
        $opts = [
            'http' => [
               'method'  => $this->method,
               'header'  => $this->header,
               'follow_location' => 0,
               'request_fulluri' => true,
               'protocol_version' => 1.1,
               'user-agent' => 'Linux/PHP',
               'content' => $sendData,
            ],
        ];

        $context = stream_context_create($opts);
        $this->result = file_get_contents($this->uri, false, $context);

        # search for a 301, 302 in the $http_response_header array
        if ( stristr($http_response_header[0], 'HTTP/1.1 301') || 
             stristr($http_response_header[0], 'HTTP/1.1 302')) 
        {
            # find the location to redirect to
            foreach ($http_response_header as $intIndex => $header) {
                if (strstr($header, 'Location: ')) {
                    # get the location to redirect to by splitting on the space
                    $locationArray = explode(' ', $header);
                    $this->uri = $locationArray[1];
                }
            }

            # attempt the request again
            $this->result = file_get_contents($this->uri, false, $context);
        }

        return $this->result;
    }
}
