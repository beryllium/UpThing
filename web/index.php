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

    if ( $file_bag->has('image') )
    {
        $image = $file_bag->get('image');
        $image->move(
            $app['upload_folder'], 
            tempnam($app['upload_folder'],'img_')
        );
    }

    // TODO: Set a flash notice to let the user know the upload was successful

    return new RedirectResponse($app['url_generator']->generate('gallery'));
})
->bind('upload_post');

$app->get('/img/{name}/{size}', function( $name, $size, Request $request ) use ( $app ) {
    $prefix = $app['upload_folder'].'/';
    $full_name = $prefix . $name;

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
        $thumb_name = $prefix . 'small_' . $name . '.jpg';
        $thumb_width = 320;
        $thumb_height = 240;
        break;

    case 'medium':
        $thumb_name = $prefix . 'medium_' . $name . '.jpg';
        $thumb_width = 1024;
        $thumb_height = 768;
        break;
    }

    $out = null;

    if ( 'original' == $size )
    {
        $out = new BinaryFileResponse($full_name);
    }
    else
    {
        if ( !file_exists( $thumb_name ) )
        {
            $app['imagine']->open($full_name)
                ->thumbnail(
                    new Imagine\Image\Box($thumb_width,$thumb_height), 
                    Imagine\Image\ImageInterface::THUMBNAIL_INSET)
                ->save($thumb_name);
        }

        $out = new BinaryFileResponse($thumb_name);
    }

    return $out;
})
->value('size', 'small')
->bind('img');

$app->get('/view', function() use ( $app ) {
    $image_glob = glob($app['upload_folder'] . '/img*');

    $images = array_map( 
        function($val) { return basename( $val ); }, 
        $image_glob 
    );

    return $app['twig']->render('gallery.html.twig',array(
        'images' => $images,
    ));
})
->bind('gallery');

$app->run();
