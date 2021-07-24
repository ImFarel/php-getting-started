<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__ . '/views',
));

// Our web handlers

$app->get('/', function () use ($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->post('/captcha', function (Request $request) {
  $message = $request->get('message');
  $postfields = array(
    'secret' => 'xxxxxxxxxxxxxxxxxxxxxxxxx-xxxx',
    'response' => $request->get('token')
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_POST, 1);

  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
  $result = curl_exec($ch);

  $result = json_decode($result, true);
  
  return new Response(json_encode($result), 200);
});

$app->run();
