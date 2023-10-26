<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Method;

/**
 * Enumeration of Sale required fields
 */
enum Sale
{
    case action;
    case client_key;
    case order_id;
    case order_amount;
    case order_currency;
    case order_description;
    case card_number;
    case card_exp_month;
    case card_exp_year;
    case card_cvv2;
    case payer_first_name;
    case payer_last_name;
    case payer_address;
    case payer_country;
    case payer_city;
    case payer_zip;
    case payer_email;
    case payer_phone;
    case payer_ip;
    case term_url_3ds;
    case hash;
}
