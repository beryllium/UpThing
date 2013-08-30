<?php

require __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

// Detect environment (default: prod) by checking for the existence of $app_env
// (If you know of a safer or smarter way to do this that works with both HTTP and CLI, let me know)
if (isset($app_env) && in_array($app_env, array('prod','dev','test')))
    $app['env'] = $app_env;
else
    $app['env'] = 'prod';

// WARNING: Disable this setting in production. Set it to false.
$app['debug'] = true; 

$app['upload_folder'] = __DIR__ . '/uploads';

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

$app->register(new Neutron\Silex\Provider\ImagineServiceProvider());
