# Router

Esse projeto é um simples componente de rotas PHP. Fiz esse projeto para entender o funcionamento de um gerenciador de rotas.
Tive como base o coffeecode/router que é um projeto profissional que uso em outros projetos postados aqui mesmo
no github.
## Instalação

```shell
composer require yuri-oliveira/router
```

## Como usar

### Apache

```apache
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ ./index.php [QSA,NC]
</IfModule>
```
### Construtor

```php
<?php

use YuriOliveira\Router\Request;
use YuriOliveira\Router\Response;
use YuriOliveira\Router\Router;

require_once(__DIR__ . '/vendor/autoload.php');

define('SITE', [
    'root' => 'http://localhost/yuri-oliveira-router'
]);

$request = new Request(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_FILES,
    $_GET,
    $_POST,
    getallheaders()
);

$response = new Response();

$router = new Router($request, $response, SITE['root']);

```

### Rotas

```php

// Definindo o namespace dos Controllers
$router->namespace('YuriOliveira\Router\Controllers')

// Exemplo de rotas simples
$router->get('/home', 'WebController:home', 'web.home');
$router->post('/registrar', 'WebController:register', 'web.register');

// Exemplo de rotas com parâmetros dinâmicos
$router->get('/usuarios/:user', 'WebController:showUser', 'web.showUser');
$router->post('/usuarios/deletar/:user', 'WebController:deleteUser', 'web.deleteUser');

// Rota dinâmica para receber os erros de requisição
$router->get('/oops/:errorcode', 'App:error', 'app.error');

// Método que faz a classe trabalhar
$router->dispatch();

if ($error = $router->error()) { $router->redirect('app.error', ['errorcode' => $error]); }

```
### Callable

```php
$router->get('/', function(Router $router, array $data, ResponseInterface $response){

    $response->addContent('Olá mundo!')->sendResponse();
    
}, 'web.home');

$router->post('/registrar', function(Router $router, array $data, ResponseInterface $response){

    $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

    $user = new User();
    
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->password = password_hash($data['password'], PASSWORD_DEFAULT);

    $user->save();

}, 'web.register');
```

### Grupos

Os grupos são basicamente prefixos. As rotas criadas depois da definição do grupo estarão dentro do grupo até que o grupo mude ou seja setado como null.

```php
$router->group('/admin');
$router->get('/perfil', 'AdminController:adminProfile', 'admin.adminProfile');
$router->post('/relatorio', 'AdminController:report', 'admin.report');

$router->group('/usuarios');
$router->get('/:user', 'WebController:showUser', 'web.showUser');
$router->post('/deletar/:user', 'WebController:deleteUser', 'web.deleteUser');

$router->group(null);
$router->get('/home', 'WebController:home', 'web.home');
$router->post('/contatos', 'WebController:contacts', 'web.contacts');
```

### Redirecionar

O redirecionamento é feito usando o método redirect que recebe o nome da rota, um path ou uma url completa.

```php
$router->redirect('web.home');
$router->redirect('/postagens/tecnologia/55214');
$router->redirect('http://www.site.com/home');

```

### Controller

```php
class WebController
{
    protected Router $router;
    
    public function __construct($router)
    {
        $this->router = $router;
    }

    public function showUsers($data, ResponseInterface $response): void
    {
       $users = User::find()->all();

       $response->addContent(print_r($data))->sendResponse();
    }
}
```

### Response

```php
class WebController
{
    protected Router $router;
    
    public function __construct($router)
    {
        $this->router = $router;
    }

    public function showUsers($data, ResponseInterface $response): void

    {
        // O status da resposta. O status 200 já é o padrão
        $response->setStatusHttp(200);

        // Conteúdo a ser enviado como resposta
        $response->addContent(mixed $content);

        // Tipo do conteúdo a ser enviado como resposta. O ContentType 'text/html' já é o padrão
        $response->setContentType('text/html');

        // Definindo Headers da resposta
        // O header Content-Type é informado automaticamente usando o setContentType informado
        $response->addHeader(string $key, string $value);

        // Enviando resposta para o browser
        $response->sendResponse();
    }
}
```

### View

```html

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="<?= $router->route('web.showUser', ['user' => 1]) ?>">Usando as rotas</a>
    <!-- retornaria "http://www.site.com/usuarios/1"  -->
</body>
</html>

```

### Requisitos

PHP 8.0 ou superior
