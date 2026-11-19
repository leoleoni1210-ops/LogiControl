
# LogiControl - Sistema de Gerenciamento Logístico

## 1. Apresentação

O LogiControl é uma aplicação web desenvolvida para auxiliar no gerenciamento de operações logísticas, permitindo organizar produtos, endereços, transportadores e fretes em um único sistema.

## 2. Objetivo

Desenvolver uma aplicação web que facilite o controle de informações logísticas, tornando o gerenciamento de fretes mais organizado e eficiente.

## 3. Tecnologias utilizadas

- PHP
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- MySQL
- XAMPP

## 4. Funcionalidades

O sistema possui as seguintes funcionalidades:

- Cadastro e consulta de produtos.
- Cadastro e consulta de endereços de origem e destino.
- Cadastro e consulta de transportadores.
- Cadastro e gerenciamento de fretes.
- Associação de vários produtos a um mesmo frete.
- Seleção de transportador, origem e destino.
- Controle de status dos fretes.
- Consulta de fretes por data.
- Download de relatórios em formato CSV.

## 5. Banco de dados

O banco de dados utilizado é o MySQL, com o nome `logiControl`.

As tabelas criadas são:

- produtos
- enderecos
- transportadores
- fretes
- frete_produtos

A tabela `frete_produtos` permite relacionar vários produtos a um mesmo frete.

## 6. Como executar o projeto

1. Instale o XAMPP.
2. Inicie os serviços Apache e MySQL.
3. Copie a pasta `logiControl` para `C:\xampp\htdocs`.
4. Acesse o phpMyAdmin pelo endereço `http://localhost/phpmyadmin`.
5. Crie o banco de dados com o nome `logiControl`.
6. Importe o arquivo `database.sql` para criar as tabelas.
7. Configure a conexão no arquivo `config/conexao.php`.
8. Acesse o sistema pelo endereço:

   `http://localhost/logiControl/`

## 7. Desenvolvedora

Projeto acadêmico desenvolvido como Atividade Prática Orientada (APO), na disciplina Fundamentos de Programação para Internet.

## 8. Considerações finais

O desenvolvimento do LogiControl proporcionou a aplicação prática dos conceitos de programação para internet, integração com banco de dados e desenvolvimento de sistemas web.
