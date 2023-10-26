<?php

use NikitaRusakov\AkuratecoTest\Api\Client\Client;
use NikitaRusakov\AkuratecoTest\Api\Method\Sale;
use NikitaRusakov\AkuratecoTest\Framework\ObjectManager;

require_once __DIR__ . '/vendor/autoload.php';
$payload = [
    Sale::order_id->name => random_int(0, 10000),
    Sale::order_amount->name => 1.99,
    Sale::order_currency->name => 'USD',
    Sale::order_description->name => 'Product',
    Sale::card_number->name => '4111111111111111',
    Sale::card_exp_month->name => '01',
    Sale::card_exp_year->name => '2025',
    Sale::card_cvv2->name => '000',
    Sale::payer_first_name->name => 'John',
    Sale::payer_last_name->name => 'Doe',
    Sale::payer_address->name => 'Street',
    Sale::payer_country->name => 'US',
    Sale::payer_city->name => 'City',
    Sale::payer_zip->name => '1234',
    Sale::payer_email->name => 'test@mail.com',
    Sale::payer_phone->name => '199999999',
    Sale::payer_ip->name => '123.123.123.123',
    Sale::term_url_3ds->name => 'http://client.site.com/return.php',
];
$auth = [
    'url' => 'https://dev-api.rafinita.com/post',
    'client_pass' => 'd0ec0beca8a3c30652746925d5380cf3',
    Sale::client_key->name => '5b6492f0-f8f5-11ea-976a-0242c0a85007'
];

$objectManager = ObjectManager::getInstance();
/** @var Client $client */
$client = $objectManager->get(Client::class);
$client->setAuth($auth);
$response = $client->sale($payload);

var_dump($response->getData());
