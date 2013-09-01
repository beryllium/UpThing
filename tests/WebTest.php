<?php

require __DIR__ . '/../vendor/autoload.php';

use Silex\WebTestCase;

class WebTest extends WebTestCase {
    public function createApplication() {
        $app_env = 'test';
        return require __DIR__ . '/../web/index.php';
    }

    public function testUploadForm() {
        // Set up
        $test_image = __DIR__ . '/Resources/test_img.jpg';
        $client = $this->createClient();

        // Test the rendering of the upload form
        $crawler = $client->request('GET','/');
        $this->assertEquals(
            200, 
            $client->getResponse()->getStatusCode(), 
            'Upload form failed to load'
        );
        $this->assertCount(
            1, 
            $crawler->filter('html:contains("Initiate Upload")'), 
            'Upload button not found'
        );

        // Isolate the form so we can test uploads
        $button = $crawler->selectButton('submit');
        $form = $button->form();

        // Upload a test image and test that the proper redirect was returned
        $form['image']->upload($test_image);
        $client->submit($form);
        $this->assertTrue(
            $client->getResponse()->isRedirect('/view'), 
            'Expected a redirect to the gallery, did not receive it'
        );

        // Test that image shows up in gallery
        $crawler = $client->followRedirect();
        $this->assertEquals(
            1, 
            $crawler->filter('div > img.img-thumbnail')->count(), 
            'Gallery page did not contain expected number of image elements'
        );

        // Extract image URL(s) for testing retrieval
        $img_url = $crawler->filter('div > img.img-thumbnail')->extract(array('src'));
        $this->assertCount(1, $img_url, 'Did not return expected number of images');

        // Test retrieval of original image
        $crawler = $client->request('GET', $img_url[0] . '/original');
        $md5_test = md5(file_get_contents($test_image));
        $md5_result = md5(file_get_contents($client->getResponse()->getFile()));
        $this->assertEquals(
            $md5_test, 
            $md5_result, 
            'MD5 mismatch - the uploaded image did not match the file that was uploaded'
        );

        // Test retrieval of thumbnail (to ensure that processing is working)
        $crawler = $client->request('GET', $img_url[0]);
        $this->assertEquals(
            200, 
            $client->getResponse()->getStatusCode(), 
            'Thumbnail retrieval did not return HTTP-200'
        );
        $thumb_data = $client->getResponse()->getContent();
        $this->assertGreaterThan(
            '0', 
            strlen($thumb_data), 
            'Thumbnail was not properly generated, should be greater than 0 bytes'
        );
    }
}
