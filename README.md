# Router

Esse projeto é um simples gerenciador de rotas que fiz para entender o funcionamento de um gerenciador de rotas.
Tive como base o coffeecode/router que é um projeto profissional que uso em outros projetos postados aqui mesmo
no github.

## Como usar

### Apache

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
### Rotas

- Carregando o autoload, iniciando a classe com nossa url base e definindo o
namespace onde os nossos controllers serão buscados.

```
<?php

require(dirname(__DIR__) . '/vendor/autoload.php');

use Source\Router\Router;

$router = new Router(root_url);

$router->namespace('Source\Http\Controller');

```

- Exemplo de rotas simples

```
$router->get('/home', 'WebController:home', 'web.home');
$router->post('/registrar', 'WebController:register', 'web.register');
```

- Exemplo de rotas que possuem parâmetros dinâmicos

```
$router->get('/usuario/{user}', 'WebController:showUser', 'web.showUser');
$router->post('/usuario/deletar/{user}', 'WebController:deleteUser', 'web.deleteUser');
```

- Definindo grupo de rotas

```
$router->group('admin');
$router->get('/perfil', 'AdminController:adminProfile', 'admin.adminProfile);
$router->post('/senha/editar', 'AdminController:updatePassword', 'admin.updatePassword');
```

- Esse método faz a classe trabalhar

```
$router->dispatch();
```

- Também podemos usar callables

```
$router->get('/', function ($data) {});

$router->post('/', function ($data) {});
```
- A classe router é automaticamente passada no construtor para o controller executado através dela,
dessa forma pode-se ter acesso a classe router dentro dos controllers.
A classe router também se encarrega de passar os dados de get e post para os métodos de rotas get que possuem
parâmetros dinâmicos ou rotas post que automaticamente precisam dos dados enviados de formulários.
```
class WebController
{
    protected Router $router;
    
    public function __construct($router)
    {
        $this->router = $router;
    }

    public function deleteUser($data): void
    {
       $user_id = $data['user'];
       
       User::find(['id' => $user_id])->first()->delete();
       
       $this->router->redirect('web.home');
    }
}
```
