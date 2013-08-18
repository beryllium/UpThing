<?php

require __DIR__ . '/../bootstrap.php';

use Symfony\Component\HttpFoundation\Request;

// Declare our primary action
$app->get( '/', function() {
    return 'Mr Watson, come here, I want to see you.';
});

$app->run();
