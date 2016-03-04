# PHP-REST-client
A PHP Class that will set send an HTTP DELETE, GET, POST, PUT to a URI. Headers can be set, and the data can be sent as either an HTTP query string or JSON

### Send an HTTP POST
    <?php
    use restbuilder\RestBuilder;

    include_once 'RestBuilder';

    /**
     * Assumes that the API URI returns JSON
     * Assumes that the API URI uses token authentication
     * @return array
     */
    function createNewUser($token)
    {
      $myCaller =  new RestBuilder;
      $data = [
        'username' => 'bob.jones',
        'password' => 'bobspassword',
        'name'     => 'Bob Jones',
      ];

      try {
        return json_decode($myCaller
          ->setUri('http://localhost/users/')
          ->setHttpVerb('post')
          ->addHeader('Content-type: application/x-www-form-urlencoded')
          ->addHeader('x-access-token:'.$token)
          ->setPostData($data)
          ->sendRequest(), true);
      } catch (Exception $e) {
        echo "Caught exception ".$e->getMessage();
      }

    }

    /**
     * Creates a user by sending the POST with JSON data
     * Assumes that the API returns JSON
     * Assumes that the API uses token authentication
     * @return array
     */
    public function createUserViaJson($token)
    {
      $myCaller =  new RestBuilder;
      $data = [
        'username' => 'bob.jones',
        'password' => 'bobspassword',
        'name'     => 'Bob Jones',
      ];

      $jsonPostData = json_encode($data);

      return json_decode($myCaller
        ->setUri('http://localhost/users/')
        ->setHttpVerb('post')
        ->addHeader('x-access-token:'.$token)
        ->setPostData($jsonPostData)
        ->sendAsJson()
        ->sendRequest(), true);   
    }
