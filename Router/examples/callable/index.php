<?php

require(dirname(__DIR__, 2) . '/vendor/autoload.php');

use Source\Router\Router;

$router = new Router("https://www.youdomain.com");

/*
 *  Podemos também usar o callable ao invés de usar controller.
 *  Aforma correta de usar a classe router nesse caso é como mostrado
 *  logo abaixo. 
 */

$router->get("/", function ($data) {}, 'name.route');

$router->post("/", function ($data) {}, 'name.route');
