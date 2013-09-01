<?php

require __DIR__ . '/vendor/autoload.php';

use Gaufrette\Filesystem;
use Gaufrette\StreamWrapper;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Adapter\InMemory as InMemoryAdapter;

$app = new Silex\Application();

// Detect environment (default: prod) by checking for the existence of $app_env
// (If you know of a safer or smarter way to do this that works with both HTTP and CLI, let me know)
if (isset($app_env) && in_array($app_env, array('prod','dev','test')))
    $app['env'] = $app_env;
else
    $app['env'] = 'prod';

// WARNING: Disable this setting in production. Set it to false.
$app['debug'] = true; 

// Configuring the filesystem based on environment
if ('test' == $app['env'])
{
    $app['upthing.adapter'] = new InMemoryAdapter();
}
else
{
    $app['upthing.adapter'] = new LocalAdapter(__DIR__ . '/uploads');
}

$app['upthing.filesystem'] = new Filesystem($app['upthing.adapter']);

$map = StreamWrapper::getFilesystemMap();
$map->set('upthing', $app['upthing.filesystem']);

StreamWrapper::register();

$app['upthing.storage'] = 'gaufrette://upthing/';

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

$app->register(new Neutron\Silex\Provider\ImagineServiceProvider());
