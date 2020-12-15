<?php

use RestClient\RestClient;

set_include_path(get_include_path().PATH_SEPARATOR.'./');

spl_autoload_register(function ($class_name) {
    $baseDir = __DIR__;
    $file = $baseDir.'/' . str_replace('\\', '/', $class_name) . '.php';

    include_once $file;
});

function main()
{
    $sender = new RestClient;

    $body = [
        'first_name' => 'John',
        'last_name'  => 'Swift',
        'birthday'   => '2012-04-14',
        'email'      => 'swifty@example.com',
        'password'   => 'umbrella',
        'id'         => '4e8758d7-5271-4e13-3000-ac9bdff19b28',
    ];

    $sender->setUri('http://localhost:8003/users')
        ->setMethod('PUT')
        ->setBody($body)
        ->addHeader('Authorization: Bearer 123.123.123')
        ->sendAsJson()
        ->sendRequest();

    $result = $sender->getLastResult();

    echo 'RESULT', "\n", '-----', print_r($result, true), "\n";
}

main();