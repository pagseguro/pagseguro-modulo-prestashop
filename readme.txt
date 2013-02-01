***********
Módulo PagSeguro para o PrestaShop
Este módulo tem por finalidade realizar transações de pagamentos entre sistema PrestaShop e o PagSeguro
Disponível para as versões 1.5.2 e 1.5.3.1 do PrestaShop
***********

- Instalação

	- Para instalar esse módulo no PrestaShop, vá até a opção Módulos, na área administrativa, clique em Adicionar novo módulo e importe esse módulo compactado.
	- Nesse instante, o módulo foi adicionado ao seu sistema porém não está instalado(ativo). Você precisa acessar a categoria Pagamentos & Gateways, procurar pelo módulo PagSeguro e instalá-lo.
	- Pronto! A instalação foi realizada e a partir de agora o módulo estará disponível como opção de pagamento das compras realizadas em seu sistema.

- Configurações

Após instalado o módulo, é necessário que se faça algumas configurações para que efetivamente seja possível utilizar-se dele. Essas configurações estão disponíveis na opção Configurar do módulo.

	- email: E-mail cadastrado no PagSeguro
	- token: Token cadastrado no PagSeguro
	- url de redirecionamento: url utilizada para se fazer redirecionamento após o cliente realizar a efetivação da compra no ambiente do PagSeguro. Pode ser uma url do próprio sistema ou uma outra qualquer de interesse do vendedor.
	- charset: codificação do sistema (ISO-8859-1 ou UTF-8)
	- log: diretório a partir da raíz do sistema, onde se deseja criar o arquivo de log . Ex.: /logs/log_pagseguro.log
			
* NOTAS:
	
	- Certifique-se que o email e o token informados estejam relacionados a uma conta que possua o perfil de vendedor ou empresarial;
	- Certifique-se que tenha definido corretamente o charset de acordo com a codificação (ISO8859-1 ou UTF8) do seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
	