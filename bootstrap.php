<?php

require __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

// Disable this setting in production
$app['debug'] = true; 
