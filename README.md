# Simple Form Sender
Plugin de gerenciamento simples de envios de formulários para o October CMS. Através dele, você pode mensurar e acompanhar as informações captadas pelos formulários do seu site.

## Como instalar
Para instalar o plugin, clone o repositório ou faça o download do mesmo para a pasta plugins > seuNome. Depois execute os seguintes comandos:

```
php artisan october:update
php artisan plugin:refresh SeuNome.SimpleFormSender
```

Pronto! Você já pode utilizar o plugin.

## Como utilizar o plugin
Existem alguns detalhes que precisam de uma atenção especial. Veja:

- No menu lateral do plugin, será criado automaticamente um submenu para cada formulário à medida em que eles sejam submetidos.
- O campo type dos formulários e o code do templademail precisam iniciar obrigatoriamente com "_", underline. Este valor será inserido em um input do tipo hidden automaticamente.
- É importante cadastrar labels de acordo com os campos e nomes dos formulários, não é necessário duplicar.
- É necessário cadastrar um formulário sempre antes de utilizá-lo.
- Lembre-se de "arrastar" o componente na pagina em que o formulário será utilizado, substituindo a tag <form> do html.
- Para o envio por email, basta configurar corretamente o sistema de emails do próprio October.
- Importante criar um template de email chamado "default" para ser usado por padrão, caso o formulário não encontre um à ser usado.
- Os campos dos formulários chegam no template email através da variavel "vars".


#### Em breve um passo-a-passo melhor elaborado com imagens e dicas.
