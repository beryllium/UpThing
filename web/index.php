<?php

require __DIR__ . '/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

$app->get( '/', function() use ( $app ) {
    return $app['twig']->render('upload_form.html.twig');
})
->bind('upload_form');

$app->post('/', function( Request $request ) use ( $app ) {
    $file_bag = $request->files;
    $storage_path = $app['upthing.storage'];

    $new_name = 'img_' . microtime(true);

    if ( $file_bag->has('image') )
    {
        $image = $file_bag->get('image');
        if ( !empty($image) && $image->isValid() )
        {
            file_put_contents(
                $storage_path . $new_name, 
                file_get_contents($image->getPathname())
            );
            // TODO: Set a flash notice to let the user know the upload result
        } else {
            // We have an error!
            // TODO: Set a flash notice to let the user know the upload was NOT successful
        }
    }

    return new RedirectResponse($app['url_generator']->generate('gallery'));
})
->bind('upload_post');

$app->get('/img/{name}/{size}', function( $name, $size, Request $request ) use ( $app ) {
    $storage_path = $app['upthing.storage'];
    $storage_fs   = $app['upthing.filesystem'];
    $full_name = $storage_path . $name;

    $thumb_name = '';
    $thumb_width = 320;
    $thumb_height = 240;

    if ( !file_exists( $full_name ) )
    {
        throw new \Exception( 'File not found' );
    }

    switch ( $size )
    {
    default:
    case 'small':
        $thumb_name = 'small_' . $name . '.jpg';
        $thumb_width = 320;
        $thumb_height = 240;
        break;

    case 'medium':
        $thumb_name = 'medium_' . $name . '.jpg';
        $thumb_width = 1024;
        $thumb_height = 768;
        break;

    case 'original':
        // Do nothing, this is handled in a separate logic branch
        break;
    }

    $out = null;

    if ( 'original' == $size )
    {
        $out = new BinaryFileResponse($full_name);
    }
    else
    {
        if (file_exists($storage_path . $thumb_name))
        {
            $data = file_get_contents($storage_path . $thumb_name);
        }
        else
        {
            $data = $app['imagine']->open($full_name)
                ->thumbnail(
                    new Imagine\Image\Box($thumb_width,$thumb_height), 
                    Imagine\Image\ImageInterface::THUMBNAIL_INSET)
                ->get('jpg');

            file_put_contents($storage_path . $thumb_name, $data);
            //$storage_fs->write($thumb_name, $data);
        }

        $out = new Response($data, 200, array('Content-type' => 'image/jpeg'));
    }

    return $out;
})
->value('size', 'small')
->bind('img');

$app->get('/view', function() use ( $app ) {
    $image_glob = $app['upthing.filesystem']->keys();

    $images = array_filter(
        array_map( 
            function($val) { return basename( $val ); }, 
            $image_glob 
        ),
        function($val) { if ( 0 === strpos($val,'img_') ) return true; else return false; }
    );

    return $app['twig']->render('gallery.html.twig',array(
        'images' => $images,
    ));
})
->bind('gallery');

if ('test' == $app['env'])
    return $app;
else
    $app->run();
