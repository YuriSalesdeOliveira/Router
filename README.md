# Router

Esse projeto é um simples gerenciador de rotas que fiz para entender o funcionamento de um gerenciador de rotas.
Tive como base o coffeecode/router que é um projeto profissional que uso em outros projetos postados aqui mesmo
no github.
## Instalação

Instalação através do composer

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
### Rotas

A classe Router recebe como parametro duas outras classes,
Resquest e Response além da url base do site.

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

Exemplo de rotas usando Controllers

```php
$router->namespace('YuriOliveira\Router\Controllers')

$router->get('/home', 'WebController:home', 'web.home');
$router->post('/registrar', 'WebController:register', 'web.register');
```

Exemplo de rotas usando Closure. A classe router se encarrega de passar os dados de $_GET, $_POST e $_FILES para os métodos de rotas GET que possuem parâmetros dinâmicos ou rotas POST que automaticamente precisam dos dados enviados de formulários. Além disso os métodos também recebem uma instancia de Response e no caso das Closures os métodos recebem uma instancia de Router.

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

Exemplo de rotas que possuem parâmetros dinâmicos.

```php
$router->get('/usuarios/:user', 'WebController:showUser', 'web.showUser');
$router->post('/usuarios/deletar/:user', 'WebController:deleteUser', 'web.deleteUser');
```

Definindo grupo de rotas. Os grupos são basicamente prefixos. As rotas criadas depois da
definição do gropo estarão dentro do grupo até que o grupo mude ou seja setado como null.

```php
$router->group('/admin');
$router->get('/perfil', 'AdminController:adminProfile', 'admin.adminProfile');
$router->post('/senha/editar', 'AdminController:updatePassword', 'admin.updatePassword');

$router->group('/usuarios');
$router->get('/:user', 'WebController:showUser', 'web.showUser');
$router->post('/deletar/:user', 'WebController:deleteUser', 'web.deleteUser');

$router->group(null);
$router->get('/home', 'WebController:home', 'web.home');
$router->post('/contatos', 'WebController:contacts', 'web.contacts');
```

O redirecionamento é feito usando o método redirect que recebe o nome da rota,
um path ou uma url completa.

```php
$router->redirect('web.home',);
$router->redirect('app.error', ['errorcode' => $error]);
$router->redirect('app.error', ['errorcode' => $error]);

```

Agora fazemos a classe trabalhar. Depois de fazermos a classe trabalhar podemos capturar os erros
e redirecionar o usuário para uma página personalizada quando algum erro for detectado.

```php

$router->get('/oops/:errorcode', 'App:error', 'app.error');

$router->dispatch();

if ($error = $router->error()) { $router->redirect('app.error', ['errorcode' => $error]); }

```

A classe router é automaticamente passada no construtor para o controller, dessa forma pode-se ter acesso a classe router dentro dos controllers. A classe router também se encarrega de passar os dados de $_GET, $_POST e $_FILES para os métodos de rotas GET que possuem parâmetros dinâmicos ou rotas POST que automaticamente precisam dos dados enviados de formulários. Além disso os métodos também recebem uma instancia de Response.

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

Trabalhando com a classe Response.

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
        // O status da resposta. O status 200 já o padrão
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

### Requisitos

PHP 8.0 ou superior
