<?php

require __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

// Disable this setting in production
$app['debug'] = true; 
$app['upload_folder'] = __DIR__ . '/uploads';

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

$app->register(new Neutron\Silex\Provider\ImagineServiceProvider());
