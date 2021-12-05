# Router

Um simples gerenciador de rotas

### Como usar

#### Apache

```apache
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . / [L]

Options -Indexes
</IfModule>
```
#### Rotas

```routes
<?php

require(dirname(__DIR__) . '/vendor/autoload.php');

use Source\Router\Router;

$router = new Router(root_url);

// Exemplo de rotas simples

$router->get("/home", "WebController:home", 'web.home');
$router->post("/registrar", "WebController:register", 'web.register');

// Exemplo de rotas que possuem parametros dinâmicos

$router->get("/usuario/{user}", "WebController:showUser", 'web.showUser');
$router->post("/usuario/deletar/{user}", "WebController:deleteUser", 'web.deleteUser');

// Esse método faz a classe trabalhar

$router->dispatch();

```


