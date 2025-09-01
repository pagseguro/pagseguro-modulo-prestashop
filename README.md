![](https://prestabr.com.br/docpagbank/0525/head_git_pagbank.jpg)

<p align="center">PagBank - Checkout Transparente - v.2.0.0</p>

## Taxas Reduzidas!

![](https://prestabr.com.br/docpagbank/0525/taxas_reduzidas.jpg "Taxas Reduzidas - Módulo PagBank")

## Dúvidas, Sugestões, Suporte

Você está com dúvidas, gostaria de sugerir algum novo recurso ou precisa de suporte técnico?

[Enviar mensagem](https://github.com/pagseguro/pagseguro-modulo-prestashop/issues/new/choose)


## Introdução

Através do módulo PagBank oferecemos total integração da sua loja PrestaShop com a melhor solução de pagamentos do Brasil.

Este módulo foi desenvolvido rigorosamente dentro dos padrões de segurança PCI DSS (Padrão de Segurança de Dados da Indústria de Pagamento com Cartão) e boas práticas de desenvolvimento recomendadas pela PrestaShop, com o objetivo de simplificar e agilizar o processo de Checkout. Não importa o seu ramo de atividade, este módulo irá potencializar suas vendas otimizando a experiência de compra do seu cliente em sua loja virtual.

## Compatibilidade

- Este módulo não utiliza Override;
- Compatível com PrestaShop 1.6.x ao 9.x;
- Compatível com PHP 5.2.0 ao 8.4.11;
- Desenvolvido, testado e homologado com base na API v.4 do PagBank;
- Requer a biblioteca cURL ativa na hospedagem.
- Obrigatório o uso de certificado SSL com o protocolo TLS 1.2 ou superior;

## Funcionalidades

- Aceite pagamentos via Cartão de Crédito, Google Pay, Pix, Boleto Bancário e Pagar com PagBank.
- Transações criptografadas para Cartão de Crédito e Google Pay.
- Compra com 1 clique utilizando tokenização e armazenamento seguro do cartão via PagBank.
- Parcelamento em até 12x, com ou sem repasse de juros.
- Defina o valor mínimo da parcela e configure se transações abaixo desse valor serão processadas.
- Descontos configuráveis (percentual ou valor fixo) em todas as formas de pagamento - no Cartão de Crédito e Google Pay, o desconto aparece na primeira parcela.
- Pagamento com ou sem pré-autorização, disponível para Cartão de Crédito e Google Pay.
- Google Pay com 3DS embarcado para as bandeiras Visa, MasterCard, Elo e Amex.
- Gestão de risco com inteligência artificial, integrada ao antifraude do PagBank.
- Cadastro em múltiplas condições comerciais, com opção de definir qual será usada para processar os pagamentos.
- Consulta do pedido junto ao PagBank em tempo real no histórico do pedido na loja.
- Listagem exclusiva de pedidos processados via PagBank.
- Estorno total ou parcial de transações.
- Mapeamento de status personalizado, facilitando o gerenciamento de pedidos.
- Notificações automáticas de atualização de status.
- Logs completos da comunicação entre loja e PagBank, com visualização via CMS.
- Tarefa Cron automática para cancelamento de pedidos via Pix, Boleto e Pagar com PagBank.
- Defina o tempo máximo para pagamento via Pix (em minutos).
- Configure o prazo de vencimento do Boleto Bancário (mínimo de 2 dias úteis, com cancelamento automático).
- Adicione um texto descritivo personalizado no Boleto Bancário.
- Disponibilidade de ambientes Produção e Sandbox, inclusive com opção de criar pedido de demonstração para homologar o Google Pay com a loja em produção.
- Menu de acesso rápido aos recursos do módulo.
- Debug de JavaScript via Console Log.
- Proteção contra perda de dados: o banco de dados do módulo não é apagado em caso de desinstalação acidental.

## Instalação ou Atualização

Para realizar a instalação do módulo PagBank, você precisará enviar o arquivo pagbank.zip. Para realizar essa operação, siga os passos a seguir:

1. Faça o download dos arquivos. Para isso, clique em **Code** e depois selecione a opção **Download Zip**.
2. Após fazer o download, descompacte o arquivo em seu computador e verifique se dentro da pasta de arquivos descompactados está o arquivo pagbank.zip.
3. Localizando o arquivo **pagbank.zip**, acesse o painel admin da sua loja e navegue até **Módulos > Gerenciador de Módulos**.
4. Clique em **Enviar um módulo** e selecione o arquivo **pagbank.zip** baixado anteriormente.

![](https://prestabr.com.br/docpagbank/0525/instalacao.jpg "Instalação - Módulo PagBank")

Ao finalizar os passos descritos acima o módulo estará instalado!

**`Otimize sua loja:`**

> - Sempre que você instalar ou atualizar o módulo, acesse a tab **Parâmetros Avançados > Desempenho** e clique em **Limpar Cache**. Isso irá garantir que todos os arquivos sejam atualizados no cache da PrestaShop.

**`Nota:`**

> - Ao atualizar o módulo não é preciso refazer a Configuração de Cadastro/Adesão App, durante a atualização as configurações salvas no módulo são preservadas.

## Configuração

#### 1 - Cadastro/Adesão App

O cadastramento no App é o primeiro passo para tornar a sua integração funcional. São três opções disponíveis para cadastro:

- App D14
- App D30
- App Tax

![](https://prestabr.com.br/docpagbank/0525/1_cadastro_adesao_app_0.jpg "Cadastro/Adesão App - Módulo PagBank")

O processo de adesão é muito simples, basta clicar em **Cadastrar** e seguir o passo-a-passo indicado.

![](https://prestabr.com.br/docpagbank/0525/1_cadastro_adesao_app_1.jpg "Cadastro/Adesão App - Módulo PagBank")

---
#### 2 - Configurações do App

Após se cadastrar no(s) App(s) desejado(s), marque a opção **Ambiente de Produção** como SIM e no campo **Tipo de Credencial** selecione qual App deve validar e processar os pagamentos.

![](https://prestabr.com.br/docpagbank/0525/2_configuracoes_do_app_0.jpg "Configurações do App - Módulo PagBank")

---
#### 3 - Pagamento via Cartão de Crédito

Marque a opção **Cartão de Crédito** como SIM para ativar o meio de pagamento.

![](https://prestabr.com.br/docpagbank/0525/3_pagamento_via_cartao_de_credito_0.jpg "Pagamento via Cartão de Crédito - Módulo PagBank")

Na opção **Compra com 1 Click**, o cliente poderá salvar o Cartão de Crédito para futuras compras. O Cartão é criptografado e armazenado pelo PagBank através do processo de Tokenização.

Após realizar a primeira compra com o cartão salvo ele ficará disponível para seleção na tela de checkout, exemplo:

![](https://prestabr.com.br/docpagbank/0525/3_pagamento_via_cartao_de_credito_1.jpg "Pagamento via Cartão de Crédito - Módulo PagBank")

#### 4 - Pagamento via Cartão de Crédito com Google Pay

Marque a opção **Google Pay** como **SIM** para ativar o meio de pagamento.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_00.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Para aderir ao Google Pay e obter o seu **Google Merchant ID** é preciso ter uma conta do tipo **Bussiness** que pode ser criada gratuitamente junto ao Google.

Se você já tem uma conta ou deseja criar uma basta acessar o endereço abaixo e seguir as orientações de cadastro:

**https://pay.google.com/business/console/**

Ao acessar o Google Pay navegue até o menu **Perfil da Empresa** e localize as seções **Business identity** e **Business information** para conferir se há alguma informação pendente em seu cadastro, são dados obrigatórios como: nome da empresa, categoria, endereço, telefone e contato para suporte ao cliente.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_1.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

**`Nota:`**

> - Após o envio a aprovação pode levar até 1 dia útil mas geralmente é concluída dentro de alguns minutos.

Em seguida navegue até o menu **Api Google Pay**, clique em **Começar** e aceite os termos de serviço.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_2.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Após aceitar os termos role a página até a seção **Integrate with your website** e clique em **+ Add website**.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_3.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Na seção **Your website** em **Website URL** informe a url da sua loja virtual.
Na seção **Your Google Pay API integration type** em **Tipo de Integração** escolha a opção **Gateway**.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_4.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Na seção **Screenshots of your buyflow** você precisará tirar prints das etapas do seu fluxo compras, que começa na página do produto e vai até a tela de confirmação do pedido/pagamento em sua loja virtual.

Os prints necessários são:

1. **Item selection** - Tela da página do Produto;
2. **Pre-purchase screen** - Tela da página do Carrinho de compras com um produto adicionado;
3. **Payment method screen** - Tela da página de checkout com as opções de pagamento disponíveis;
4. **Google Pay API payment screen** - Tela de checkout com a opção do Google Pay selecionada junto com o poup de selecão do cartão;
5. **Post-purchase screen** - Tela de confirmação do pedido/pagamento;

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_5.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Para enviar o print **Post-purchase screen** você precisará criar um pedido em modo **SandBox**. Para isso marque a opção **Ambiente de Produção** como **SandBox / NÃO** e habilite a opção **Pedido Demo** como **SIM**, para poder criar o pedido e tirar um print real da tela de confirmação do pedido/pagamento.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_6.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

Após enviar os prints e clicar em **Salvar**, role a página até o topo e localize **Web integration** e marque as três
opções de conformidade e clique em **Submit for approval**.

![](https://prestabr.com.br/docpagbank/0525/4_pagamento_via_cartao_de_credito_com_google_pay_7.jpg "Pagamento via Cartão de Crédito com Google Pay - Módulo PagBank")

**`Nota:`**

> - A aprovação pode levar até 1 dia útil e você será notificado por email pelo Google.
> - Durante este período, nas configurações do módulo, você pode desativar a opção de pagamento via Google Pay para aguardar a aprovação do Google, marcando a opção **Google Pay** como **NÃO** e em seguida clicando em **Salvar** .

Quando aprovado basta acessar a tela de configuração do módulo novamente, marcar a opção **Google Pay** como **SIM**, mudar a opção **Ambiente de Produção** para **Produção / SIM** e **Pedido Demo** como **NÃO** e em seguida **Salvar**. E a sua loja estará pronta para processar pagamentos via Google Pay em Produção!

#### 5 - Configurações de Pagamento via Cartão de Crédito

Você tem a opção de configurar o seguinte:

- **Quantidade máxima de parcelas**: selecione 1 para pagamento à vista ou de 2 até 12 parcelas para pagamento parcelado.
- **Quantidade de parcelas sem juros**.
- **Valor da parcela mínima**: define o valor mínimo da parcela aceita pela loja no momento do parcelamento da compra.
- **Comportamento da parcela mínima**: define o comportamento do checkout caso o valor da parcela seja inferior ao valor estabelecido para o valor da parcela mínima.
- **Tipo de Captura**: Escolha entre captura automática ou manual (pré-autorização).

![](https://prestabr.com.br/docpagbank/0525/5_configuracoes_de_pagamento_via_cartao_de_credito_0.jpg "Configurações de Pagamento via Cartão de Crédito - Módulo PagBank")

Na opção de **Tipo de Captura: Captura Automática** o valor total da transação é debitado imediatamente do saldo do cartão de crédito, todo o processo é automatizado.

Na opção de **Tipo de Captura: Captura Manual (Pré-autorização)** o valor total da transação fica reservado no saldo do cartão de crédito, até que você decida capturar um valor parcial ou total. Por exemplo, se o valor total da transação for R$ 1.000,00 e você decidir capturar R$ 800,00 os R$ 200,00 restantes será devolvido automaticamente para o saldo do cartão de crédito.

Com o pedido gerado na loja, com o status de Pagamento Autorizado, é só acessar o histórico do pedido e informar um valor parcial ou total que você deseja debitar do cartão de crédito, exemplo:

![](https://prestabr.com.br/docpagbank/0525/5_configuracoes_de_pagamento_via_cartao_de_credito_1.jpg "Configurações de Pagamento via Cartão de Crédito - Módulo PagBank")

#### 6 - Pagamento via PIX

Marque a opção **PIX** como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Prazo limite de pagamento via PIX (O padrão é 30 minutos).

![](https://prestabr.com.br/docpagbank/0525/6_pagamento_via_pix_0.jpg "Pagamento via PIX - Módulo PagBank")

**`Nota:`**

> - Não esqueça de configurar a **Tarefa Cron** para o cancelamento do PIX.

#### 7 - Pagamento via Boleto Bancário

Marque a opção **Boleto Bancário** como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Prazo de vencimento do boleto (O padrão é 2 dias).
- Texto descritivo para o boleto.

![](https://prestabr.com.br/docpagbank/0525/7_pagamento_via_boleto_bancario_0.jpg "Pagamento via Boleto Bancário - Módulo PagBank")

**`Nota:`**

> - Não esqueça de configurar a **Tarefa Cron** para o cancelamento do Boleto.

#### 8 - Pagamento via Pagar com PagBank (Wallet)

Marque a opção **Pagar com PagBank** como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.
Nesta opção de pagamento o cliente poderá realizar o pagamento com o saldo em conta ou cartão de crédito salvo no super app PagBank.

![](https://prestabr.com.br/docpagbank/0525/8_pagamento_via_pagar_com_pagbank_wallet_0.jpg "Pagamento via Pagar com PagBank (Wallet) - Módulo PagBank")

**`Nota:`**

> - Não esqueça de configurar a **Tarefa Cron** para o cancelamento do pagamento via Pagar com PagBank.

> Por enquanto, esta opção de pagamento tem algumas limitações:
> - Não é possível especificar o prazo de expiração do qrcode/link de pagamento, o prazo padrão é de 24hs.
> - Não é possível oferecer desconto no pagamento à vista.
> - Não há suporte para pré-autorização em pagamentos com cartão de crédito.
> - Não é possível restringir a opção de pagamento, o cliente poderá pagar tanto com saldo em conta quanto com cartão de crédito.

#### 9 - Opções de Descontos

Você tem a opção de escolher entre:

- Descontos de valor fixo. Nesse caso você irá definir um valor em reais a ser aplicado a todas as compras.
- Descontos percentuais. Estipule um valor percentual de desconto que será aplicado a todas as compras.

Além de definir o tipo e o montante do desconto, você tem a opção de escolher a quais meios de pagamento esse desconto será aplicado. Para isso, você deve ativar as opções desejadas dentre:

- Desconto no Cartão de Crédito (1x).
- Desconto no Cartão de Crédito com Google Pay (1x).
- Desconto no Boleto Bancário.
- Desconto no Pix.

![](https://prestabr.com.br/docpagbank/0525/9_opcoes_de_descontos_0.jpg "Opções de Descontos - Módulo PagBank")

**`Nota:`**

> - No Cartão de Crédito e Google Pay o desconto é calculado e exibido na primeira parcela.
> - Por enquanto, a opção de desconto não está disponível na modalidade: Pagar com PagBank.

#### 10 - Mapeamento de Status

Para facilitar o gerenciamento do pedido disponibilizamos a opção de mapeamento de Status. Desta forma você poderá criar status customizados que servirão especificamente para essa finalidade. 

Após criar os Status customizados na PrestaShop - no Menu **Compras > Status** - basta acessar a configuração do módulo para fazer a associação.

Os Status disponíveis são:

- Pagamento Aceito
- Pagamento Autorizado
- Pedido Cancelado
- Pedido Estornado
- Pagamento em Análise
- Aguardando Pagamento

![](https://prestabr.com.br/docpagbank/0525/10_mapeamento_de_status_0.jpg "Mapeamento de Status - Módulo PagBank")

---
#### 11 - Debug & Logs

Para uma análise técnica mais aprofundada, a fim auxiliar no desenvolvimento ou identificar eventuais conflitos de JavaScript na loja virtual, marque a opção **Exibir parâmetros no Console do navegador?** como SIM.

Marque a opção **Gerar LOGs completos?** como SIM para que o módulo registre tudo o que é enviado e recebido entre a sua loja e o PagBank.

A opção **Apagar tabelas do banco?** deve permanecer sempre desativada. Apenas marque a opção como SIM caso deseje desinstalar o módulo e remover completamente todas as informações vinculadas.

**`Utilize esse recurso com cautela:`**

Com esta opção ativa todos os registros de transações (informações básicas sobre o pedido, como: ID do pedido na loja e PagBank, ID cliente, ID carrinho, etc.) e principalmente o registro de Logs (todo o registro técnico da comunicação entre a loja e API do PagBank) serão completamente removidos do Banco de Dados da sua loja.

Este recurso serve para evitar a perda de dados ao desinstalar o módulo por acidente ou durante uma atualização da própria loja.

![](https://prestabr.com.br/docpagbank/0525/11_debug_e_logs_0.jpg "Debug & Logs - Módulo PagBank")

**`Otimize sua loja:`**

> - Sempre que você instalar ou atualizar o módulo, acesse a tab **Parâmetros Avançados > Desempenho** e clique em **Limpar Cache**. Isso irá garantir que todos os arquivos sejam atualizados no cache da PrestaShop.

Para vistualizar os registros de Logs acesse **PagBank > PagBank - Logs**, clique em **Ver** para analisar detalhes do que foi enviado e recebido entre a sua loja e o PagBank.

![](https://prestabr.com.br/docpagbank/0525/11_debug_e_logs_1.jpg "Debug & Logs - Módulo PagBank")

**`Dica:`**

> - O Código de Referência da transação no PagBank é composto pelo ID do Carrinho + APP utilizado + Número randômico.
> - Exemplo, Código de Referência: 78895.TAX.177850

**`Nota:`**

> - Dados sensíveis ao cliente como endereço, nome e sobrenome ou razão social, cpf/cnpj e telefone ou celular não são armazenados em Log.
> - Todas as transações via Cartão de Crédito são Tokenizadas e não são armazenadas em Log.

Para maiores detalhes a respeito do pedido acesse **PagBank > PagBank - Transações**, clique em **Ver**. As mesmas informações também estão disponíveis no histórico do pedido (consulta em tempo real entre a sua loja e o PagBank).

![](https://prestabr.com.br/docpagbank/0525/11_debug_e_logs_2.jpg "Debug & Logs - Módulo PagBank")

---
#### 12 - Tarefa Cron

A **Tarefa Cron** serve para cancelar os pedidos que não forem pagos dentro do prazo estipulado para Boleto Bancário, Pix e Pagar com PagBank, também é útil para o seu gerenciamento de estoque. Para configurar a **Tarefa Cron** entre em contato com o suporte técnico do seu servidor de hospedagem e informe as URLs geradas para a sua loja.

![](https://prestabr.com.br/docpagbank/0525/12_tarefa_cron_01.jpg "Tarefa Cron - Módulo PagBank")

**`Nota:`**

> - As URLs de Tarefa Cron mudaram a partir da versão 2.0.0. Por favor, verifique e atualize junto ao seu servidor de hospedagem.

---
#### 13 - Extra - Estorno Parcial ou Total de um Pedido

Na PrestaShop, no menu **Compras ou Pedidos**, acesse o pedido a ser estornado, role a página até localizar **DADOS DO PEDIDO - PAGBANK**. Ao clicar em **Estornar Transação no PagBank**, em tempo real, o módulo transmitirá a requisição para o PagBank.

![](https://prestabr.com.br/docpagbank/0525/13_extra_estorno_0.jpg "Extra - Módulo PagBank")

Após realizar o estorno, dentro de alguns segundos, o pedido receberá uma notificação para a troca de status. Pedidos com estorno Total recebem o status mapeado como Cancelado e para estorno Parcial recebe o status mapeado como Estornado. 

Para mais detalhes sobre o pedido estornado acesse a sua conta no PagBank, no menu, **Extratos e Relatórios**.

Ao acessar a transação role a página para localizar **Extrato de movimentações da transação**.

**`Link de acesso`**

**https://minhaconta.pagbank.com.br/meu-negocio/vendas-e-recebimentos**

**`Atenção`**

Só é possível realizar o estorno se o pedido estiver em um destes status:

- Aprovada
- Em análise
- Em disputa

---
## CHANGELOG

**v.1.0.0**

- Lançamento;

**v.1.1.0**

- Correção da validação do JavaScript no Checkout para o CPF/CNPJ;
- Correção da associação automática de mapeamento de Status na instalação (Em Análise e Aguardando Pagamento);
- Correção p/ remover os Status na desinstalação;
- Correção p/ exibir somente 1 ou 2 meios de pagamento quando selecionado - Só p/ PrestaShop 1.6;
- Ajustes de layout para o Back e FrontOffice;

**v.1.2.0**
- Ajustes de layout para o FrontOffice (Boleto e Pix) - Só p/ PrestaShop 1.6;
- Correção da opção de configuração para 1x sem juros;
- Correção p/ não duplicar status no pedido na notificação pós transacional;
- Correção p/ add o status de reembolsado no estorno total e parcial;

**v.1.3.0**
- Revisão, melhorias de processos e funcionalidades;
- Correções gerais de bugs;

**v.1.3.1**
- Correção no desconto cumulativo do carrinho (voucher + desconto no meio de pagamento);
- Correções gerais de bugs;

**v.1.4.0**
- Atualização do repasse de taxa para o parcelamento com juros no Cartão de Crédito;

**v.1.5.0**
- Add informativo do total da transação com juros no histórico do pedido;
- Correção do refundTransaction para estornar o valor total da transação considerando juros (se houver), sem a necessidade de informar o valor no campo;
- Add NSU no banco de dados e histórico do pedido;
- Melhorias na usabilidade do checkout;
- Correção da validação dos campos c/ e s/ Termos de Serviço Ativo;

**v.1.5.1**
- Atualização do payload do Cartão de Crédito para a nova regra da API - Log: FIELD BUYER CANNOT BE EMPTY;

**v.1.5.2**
- Correção - validação dos campos c/ Termos de Serviço Ativo - de acordo com a opção de pagamento;

**v.1.5.3**
- Correção/Revisão geral da validação dos campos na tela de checkout;

**v.1.6.0**
- Correção e revisão do ambiente Sandbox;
- Correção no RefreshToken;
- Melhorias de processos e validações;
- Correção JS - CWE-79 e 116;

**v.1.6.1**
- Ajustes complementares p/ Sandbox;

**v.1.7.0**
- Melhoria na tratativa de retorno p/ bin não mapeada e validações gerais;
- Add opção p/ captura manual da transação via Cartão de Crédito;
- Melhorias e correções gerais de bugs;

**v.1.7.1**
- Melhorias na tratativa de Logs;

**v.1.7.2**
- Correções de bugs;
- Add mecanismo p/ informar sobre novas updates na Tab de Módulos no Admin;

**v.1.8.0**
- Otimizações e correções de bugs;
- Melhorias de compatibilidade com Multilojas;
- Add nova opção de pagamento: pagar com PagBank;

**v.1.9.0**
- Otimizações gerais de performance, segurança e correções de bugs;
- Add nova opção de pagamento: Google Pay;

**v.1.9.1**
- Correções de bugs;

**v.1.9.2**
- Correções de bugs p/ o Google Pay;
- Correção de bug da Tarefa Cron do Pagar com PagBank (cancelNotPaidWallet);

**v.2.0.0**
- Otimizações e correções de bugs;
- Compatibilidade com PrestaShop 9;