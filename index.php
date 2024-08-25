<?php

require_once __DIR__ . '/model/config.php';
require_once __DIR__ . '/model/Router.php';

$router = new Router();
$router->run();