<?php

/** @var Router $router */
use Minute\Model\Permission;
use Minute\Routing\Router;

$router->post('/generic/sounder-recorder/{fn}', 'Audio/SoundRecorder', false);