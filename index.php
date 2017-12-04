<?php

require (dirname(__FILE__)) . '/autoload.php';

$config = require (dirname(__FILE__)) . '/config.php';

\amocrm\Credentials::setCredentials($config);

echo \amocrm\AmoCrm::createDeal();