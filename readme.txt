-------------------------------------------------
Módulo de integração PagSeguro para PrestaShop 
v1.1
-------------------------------------------------


= Descrição =

Este módulo tem por finalidade integrar o PagSeguro como meio de pagamento dentro da plataforma PrestaShop.


= Requisitos =

Disponível para as versões 1.5.2 e 1.5.3.1 do PrestaShop.

PHP 5.1.6+
SPL
cURL
DOM


= Instalação =

1a. Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
2a. Para instalar esse módulo, vá até a opção Módulos, na área administrativa, clique em Adicionar novo módulo e importe esse módulo compactado;
3a. Acesse a categoria Pagamentos & Gateways, procure pelo módulo PagSeguro e faça a instalação.

Alternativamente, é possível fazer a instalação da seguinte maneira:

1b. Certifique-se de que não há instalação de outros módulos para o PagSeguro em seu sistema;
2b. Descompacte o conteúdo do arquivo zip e copie a pasta 'pagseguro' para dentro da pasta 'modules' em sua instalação PrestaShop;
3b. Acesse a categoria Pagamentos & Gateways, procure pelo módulo PagSeguro e faça a instalação;


= Configuração =

Após instalado o módulo, é necessário que se faça algumas configurações para que efetivamente seja possível utilizar-se dele. Essas configurações estão disponíveis na opção Configurar do módulo.

	- email: e-mail cadastrado no PagSeguro
	- token: token cadastrado no PagSeguro
	- url de redirecionamento: ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para a página de confirmação em sua loja ou então para a URL que você informar neste campo. Para ativar o redirecionamento ao final do pagamento é preciso ativar o serviço de Pagamentos via API.
		- https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
	- charset: codificação do seu sistema (ISO-8859-1 ou UTF-8)
	- log: ativa/desativa a geração de logs
		- diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_pagseguro.log
	- Notificações de Transação
		- Para receber e processar automaticamente os novos status das transações com o PagSeguro você deve ativar o serviço de Notificação de Transações. Basta acessar o painel de controle de sua conta PagSeguro e informe a url que aparece nas configurações do módulo;
		- https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml

		
= Changelog =

	- v1.1
	- Integração com API de Notificação do PagSeguro
	- Adequação da licença
	- Adição da funcionalidade de notificação
	- Adição de tratamento para duplo espaço no nome do comprador
	- Adição de link para fazer cadastro no Pagseguro
	- Alteração da finalização do pagamento. Agora, realizado dentro do ambiente do PagSeguro
	- Correção de quebra do layout padrão do Prestashop na confirmação da compra
	- Correção para recuperação de valores de embrulho e de descontos
	- Correção de redirecionamento de página para url rewrite do PrestaShop

	- v1.0
	- Versão inicial. Integração com API de checkout do PagSeguro


= Notas =
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO8859-1 ou UTF8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual
	- Para que ocorra normalmente a geração de logs pelo plugin, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita;
	- O PagSeguro somente aceita pagamentos utilizando a moeda Real brasileiro (BRL)
