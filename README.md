Módulo de integração PagSeguro para PrestaShop
==============================================
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
 - [PrestaShop] 1.5.3.1
 - [PHP] 5.1.6+
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
- **url de redirecionamento**: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado automaticamente para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de [Pagamentos via API].
- **url de notificação**: para receber e processar automaticamente os novos status das transações com o PagSeguro você deve ativar o serviço de [Notificação de Transações] e informar a URL que aparece dentro da tela de configurações do módulo.
- **charset**: codificação do seu sistema (ISO-8859-1 ou UTF-8).
- **log**: ativa/desativa a geração de logs.
- **diretório**: informe o local a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/ps.log. Caso não informe nada, o log será gravado dentro da pasta ../PagSeguroLibrary/PagSeguro.log.


Changelog
---------
---
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
Copyright 2013 PagSeguro Internet LTDA.

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.


Notas
-----
---
- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).
- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
- Para que ocorra normalmente a geração de logs, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.


[Dúvidas?]
---------
---
Mande um [e-mail] ou acesse o [fórum] de discussões.


Contribuições
-------------
---
Achou e corrigiu um bug ou tem alguma feature em mente e deseja contribuir?

* Faça um fork.
* Adicione sua feature ou correção de bug.
* Adicione seu nome na lista de contribuidores no README.md.
* Envie um pull request no GitHub.


  [API de Pagamentos]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-pagamentos.html
  [API de Notificações]: https://pagseguro.uol.com.br/v2/guia-de-integracao/api-de-notificacoes.html
  [e-mail]: <mailto:desenvolvedores@pagseguro.com.br>
  [fórum]: http://forum.imasters.com.br/forum/244-gateways-e-meios-de-pagamento-online-pagseguro
  [Dúvidas?]: https://pagseguro.uol.com.br/desenvolvedor/comunidade.jhtml
  [Pagamentos via API]: https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
  [Notificação de Transações]: https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml
  [PrestaShop]: http://www.prestashop.com/
  [PHP]: http://www.php.net/
  [SPL]: http://php.net/manual/en/book.spl.php
  [cURL]: http://php.net/manual/en/book.curl.php
  [DOM]: http://php.net/manual/en/book.dom.php