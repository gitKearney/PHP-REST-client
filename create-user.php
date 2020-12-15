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
        'first_name' => 'Mikey',
        'last_name'  => 'Jordan',
        'birthday'   => '2015-05-02',
        'email'      => 'mikey.g@example.com',
        'password'   => 'dribble',
    ];

    $sender->setUri('http://localhost:8003/users')
        ->setMethod('POST')
        ->setBody($body)
        ->addHeader('Authorization: Bearer 123.123.123')
        ->sendAsJson()
        ->sendRequest();

    $result = $sender->getLastResult();

    echo 'RESULT', "\n", '-----', print_r($result, true), "\n";
}

main();