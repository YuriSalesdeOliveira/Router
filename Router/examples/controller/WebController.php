<?php

class WebController{

    public function home()
    {
        // exibir conteúdo da página.
    }

    public function showPost($data)
    {
        /**
         *  Podemos pegar parametros de uma rota get usando a var data
         *  dessa forma poderiamos exibir um post especifico simplismente
         *  criando um parametro id e buscando esse id na nossa tabela.
         * 
         *  Exemplo de como ficaria essa rota: 
         * 
         *  $router->get("/post/{id}", "WebController:showPost", 'web.showPost');
         */
    }

    public function authentication($data)
    {
        /**
         *  Se utilizarmos uma rota usando o metodo post, os dados enviados pelo
         *  formulário para essa rota serão armazenados na váriavel data, e assim
         *  poderemos utiliza-los dentro do nosso controller.
         */
    }

}