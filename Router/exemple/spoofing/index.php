<?php

require dirname(__DIR__, 2) . "/vendor/autoload.php";

use Source\Router\Router;

define("BASE", "https://www.localhost/coffeecode/router/exemple/controller");
$router = new Router(BASE);

/**
 * GET httpMethod
 */
$router->get("/", function ($data) {}, 'name');

/**
 * POST httpMethod
 */
$router->post("/", function ($data) {}, 'name');

$router->dispatch();