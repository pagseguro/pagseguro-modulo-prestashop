-------------------------------------------------
Módulo de integração PagSeguro para PrestaShop 
v1.1
-------------------------------------------------


= Descrição =

Este módulo tem por finalidade integrar o PagSeguro como meio de pagamento dentro da plataforma PrestaShop.


= Requisitos =

Disponível para as versões 1.5.2 e 1.5.3.1 do PrestaShop.


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

	- email: E-mail cadastrado no PagSeguro
	- token: Token cadastrado no PagSeguro
	- url de redirecionamento: url utilizada para se fazer redirecionamento após o cliente realizar a efetivação da compra no ambiente do PagSeguro. Pode ser uma url do próprio sistema ou uma outra qualquer de interesse do vendedor.
	- charset: codificação do sistema (ISO-8859-1 ou UTF-8)
	- log: diretório a partir da raíz do sistema, onde se deseja criar o arquivo de log . Ex.: /logs/log_pagseguro.log
	
	Notificações de Transação
	
		- Essa funcionalidade tem por objetivo persistir no sistema as atualizações de status das compras realizadas através do PagSeguro. Essa atualização é transparente para o sistema. É necessário somente que seja ativada a funcionalidade de Notificações de Transação no PagSeguro e informar a url que é exibida no ambiente de configuração do módulo do PagSeguro dentro do sistema.
		- Para configurar esses dados no PagSeguro, acesse https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml.
		- Uma vez configuradas essas informações no PagSeguro, o sistema passará a receber e processar automaticamente os novos status das transações com o PagSeguro, o que dá ao vendedor e ao comprador, uma maior facilidade para acompanhar os status de suas vendas e compras respectivamente, dentro do próprio site.

		
= Changelog =

	- Versão 1.1
	- Integração com API de Notificação do PagSeguro.
	- Adequação da licença.
	- Adição da funcionalidade de notificação.
	- Adição de tratamento para duplo espaço no nome do comprador.
	- Adição de link para fazer cadastro no Pagseguro.
	- Alteração da finalização do pagamento. Agora, realizado dentro do ambiente do PagSeguro.
	- Correção de quebra do layout padrão do Prestashop na confirmação da compra.
	- Correção para recuperação de valores de embrulho e de descontos.
	- Correção de redirecionamento de página para url rewrite do PrestaShop.


= Notas =
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial;
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO8859-1 ou UTF8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
	- Para que ocorra normalmente a geração de logs pelo plugin, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.
	- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).