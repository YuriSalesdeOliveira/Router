<?php

require(dirname(__DIR__) . '/vendor/autoload.php');

use Source\Router\Router;

$router = new Router("https://www.youdomain.com");

/*
 *  Com o metodo namespace podemos definir o namespace onde
 *  o controller será procurado pela classe router. Definir um
 *  namespace incorreto resultará em erro na aplicação.
 * 
 *  $router->namespace('namespace');
 */

/*
 *  Abaixo temos o exemplo de como seria o uso da classe router
 *  utilizando controllers.
 */

$router->get("/home", "WebController:home", 'web.home');
$router->get("/post/{id}", "WebController:showPost", 'web.showPost');
$router->post("/route", "WebController:authentication", 'web.authentication');