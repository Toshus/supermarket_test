<?php

use Supermarket\Supermarket;

include_once 'vendor/autoload.php';

$config = include 'config.php';

$supermarket = new Supermarket($config);

echo $supermarket->calculateModel();