***********
Módulo de integração PagSeguro para PrestaShop
v.1.0
***********


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
	- url de redirecionamento: url utilizada para se fazer redirecionamento após o cliente realizar a efetivação da compra no ambiente do PagSeguro. Pode ser uma url do próprio sistema ou uma outra qualquer de interesse do vendedor
	- charset: codificação do sistema (ISO-8859-1 ou UTF-8)
	- log: diretório a partir da raíz do sistema, onde se deseja criar o arquivo de log . Ex.: /logs/log_pagseguro.log


= Changelog =

v1.0
Versão inicial. Integração com API de checkout do PagSeguro.


= NOTAS =
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial.
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO-8859-1 ou UTF-8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
	- Para que ocorra normalmente a geração de logs pelo plugin, certifique-se que o diretório e o arquivo de log tenham permissões de leitura e escrita.
	- O PagSeguro somente aceita pagamento utilizando a moeda Real brasileiro (BRL).