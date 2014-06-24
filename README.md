Módulo de integração PagSeguro para PrestaShop 1.4 e 1.5
========================================================
---
Descrição
---------
---
Com o módulo instalado e configurado, você pode pode oferecer o PagSeguro como opção de pagamento em sua loja. O módulo utiliza as seguintes funcionalidades que o PagSeguro oferece na forma de APIs:

 - Integração com a [API de Pagamentos]
 - Integração com a [API de Notificações]


Requisitos
----------
---
 - [PrestaShop] 1.4.5.1 a 1.5.6.0
 - [PHP] 5.3.3+
 - [SPL]
 - [cURL]
 - [DOM]


Instalação
----------
---
- Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
- Baixe o repositório como arquivo zip ou faça um clone;
- Na área administrativa do seu sistema, acesse o menu Módulos -> Modules -> Add new module, aponte para o caminho do arquivo pagseguro.zip e faça o upload;
- Acesse a categoria Payments & Gateways, localize o módulo PagSeguro e faça a instalação.

Alternativamente, é possível fazer a instalação da seguinte maneira:

- Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
- Baixe o repositório como arquivo zip ou faça um clone;
- Copie a pasta *pagseguro* para dentro da pasta *modules* em sua instalação PrestaShop;
- Certifique-se de que as permissões das pastas e arquivos recém copiados sejam, respectivamente, definidas como 755 e 644;
- Acesse a categoria Payments & Gateways, localize o módulo PagSeguro e faça a instalação


Configuração
------------
---
Para acessar e configurar o módulo acesse o menu Módulos -> Modules -> Payments & Gateways -> PagSeguro -> Configure. As opções disponíveis estão descritas abaixo.

- **e-mail**: e-mail cadastrado no PagSeguro.
- **token**: token cadastrado no PagSeguro.
- **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API]. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje que seus clientes sejam redirecionados para outro local.
- **url de notificação**: sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo. Obs.: Esta URL é informada automaticamente e você só deve alterá-la caso deseje receber as notificações em outro local.
- **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
- **checkout**: especifica o modelo de checkout que será utilizado. É possível escolher entre checkout padrão e checkout lightbox.
- **log**: ativa/desativa a geração de logs.
- **diretório**: informe o local a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/ps.log. Caso não informe nada, o log será gravado dentro da pasta ../PagSeguroLibrary/PagSeguro.log.


Changelog
---------
---
1.9

- Fix: Possibilidade de consultar transações no PagSeguro para conciliar os status com a base local.
- Adicionado opção para recuperação de carrinho

1.8

- Mudanças no layout do painel de configuração.
- Possibilidade de consultar transações no PagSeguro para conciliar os status com a base local.

1.7

- Adicionando opção para utilização do Checkout Lightbox. Obs.: Recomenda-se limpar o cache do PrestaShop antes da instalação desta versão.

1.6

- Atualização da lib PHP no módulo.
- Compatibilidade com a versão 1.4.5.1+ do PrestaShop.
- Verificar se a moeda Real esta ativa, para envio ao PagSeguro.
- Conclusão de pagamento em qualquer moeda.
- Cancelamento do carrinho caso ocorra erro durante checkout.
- Conformidade com PSR-2.

1.5

 - Melhorias no tratamento de endereço.
 - Ajustes de CSS.
 - Não utilizar URLs de localhost para notificação/redirecionamento.
 - Verificar se o ambiente atende os requisitos.
 - Armazenamento do ID da transação gerada pelo PagSeguro.

1.4

 - Compatibilidade com a versão 1.5.4.1 do PrestaShop.

1.3

 - Tornando o código compliance com os requisitos do PrestaShop.

1.2

 - A URL de notificação passa a ser enviada no parâmetro notificationURL.
 - Atualização de biblioteca.
 - Melhoria de layout.
 - Correção: Remoção de lista de status quando o módulo é removido.

1.1

- Integração com API de Notificação do PagSeguro.
- Adequação da licença.
- Adição da funcionalidade de notificação.
- Adição de tratamento para duplo espaço no nome do comprador.
- Adição de link para fazer cadastro no Pagseguro.
- Alteração da finalização do pagamento. Agora, realizado dentro do ambiente do PagSeguro.
- Correção de quebra do layout padrão do Prestashop na confirmação da compra.
- Correção para recuperação de valores de embrulho e de descontos.
- Correção de redirecionamento de página para url rewrite do PrestaShop.

1.0

- Versão inicial. Integração com API de Pagamento do PagSeguro.


Licença
-------
---
Este módulo inclui software desenvolvido por PagSeguro Internet LTDA (http://www.pagseguro.com.br), licenciado sobre os termos da Apache Software License 2.0.

Este módulo inclui software desenvolvido por PrestaShop SA (http://www.prestashop.com), licenciado sobre os termos da Academic Free License 3.0.

Este módulo inclui software desenvolvido por Nicola Hibbert (http://nicolahibbert.com/liteaccordion-v2), licenciado sobre os termos da MIT License.


Notas
-----
---
- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
- Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.


[Dúvidas?]
----------
---
Em caso de dúvidas mande um e-mail para desenvolvedores@pagseguro.com.br


Contribuições
-------------
---
Achou e corrigiu um bug ou tem alguma feature em mente e deseja contribuir?

* Faça um fork.
* Adicione sua feature ou correção de bug.
* Envie um pull request no [GitHub].


  [API de Pagamentos]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-pagamentos.html
  [API de Notificações]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
  [Dúvidas?]: https://pagseguro.uol.com.br/desenvolvedor/comunidade.jhtml
  [Pagamentos via API]: https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
  [Notificação de Transações]: https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml
  [PrestaShop]: http://www.prestashop.com/
  [PHP]: http://www.php.net/
  [SPL]: http://php.net/manual/en/book.spl.php
  [cURL]: http://php.net/manual/en/book.curl.php
  [DOM]: http://php.net/manual/en/book.dom.php
  [GitHub]: https://github.com/pagseguro/prestashop

