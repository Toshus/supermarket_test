<?php

use Supermarket\Supermarket;

include_once 'vendor/autoload.php';

$supermarket = new Supermarket();

echo $supermarket->calculateModel();