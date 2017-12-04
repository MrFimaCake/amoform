<?php

phpinfo();
die;


require (dirname(__FILE__)) . '/autoload.php';

$config = require (dirname(__FILE__)) . '/config.php';

//include 'contact_form.php';
\amocrm\Credentials::setCredentials($config);

echo \amocrm\AmoCrm::createDeal();

//echo '<pre>';
//die(var_dump(\amocrm\ApiClient::callApi()));
