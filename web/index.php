<?php

use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;


// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

$config = \ZivSilex\AppFactory::getConfig(realpath(__DIR__ . '/..') . '/config.php');
$app = \ZivSilex\AppFactory::instantiateApp($config);
//$app = new Silex\Application();

$app->run();
