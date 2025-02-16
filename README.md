![](https://prestabr.com.br/docpagbank/head_git_pagbank.jpg)

<p align="center">PagBank - Checkout Transparente - v.1.8.0</p>

## Taxas Reduzidas!

![](https://prestabr.com.br/docpagbank/taxas_reduzidas.jpg "Taxas Reduzidas - Módulo PagBank")

## Dúvidas, Sugestões, Suporte

Você está com dúvidas, gostaria de sugerir algum novo recurso ou precisa de suporte técnico?

[Enviar mensagem](https://github.com/pagseguro/pagseguro-modulo-prestashop/issues/new/choose)

## Novo Módulo disponível!

O módulo PagSeguroPro foi descontinuado e substituído pelo novo módulo PagBank. Para atualizar o módulo em sua loja basta seguir o passo-a-passo de instalação e em seguida desativar o módulo PagSeguroPro.

![](https://prestabr.com.br/docpagbank/novo_modulo_disponivel.jpg "Novo Módulo Disponível - Módulo PagBank")

## Introdução

Através do módulo PagBank oferecemos total integração da sua loja PrestaShop com a melhor solução de pagamentos do Brasil.

Este módulo foi desenvolvido rigorosamente dentro dos padrões de segurança PCI DSS (Padrão de Segurança de Dados da Indústria de Pagamento com Cartão) e boas práticas de desenvolvimento recomendadas pela PrestaShop, com o objetivo de simplificar e agilizar o processo de Checkout. Não importa o seu ramo de atividade, este módulo irá potencializar suas vendas otimizando a experiência de compra do seu cliente em sua loja virtual.

## Compatibilidade

- Este módulo não utiliza Override;
- Compatível com PrestaShop 1.6.x, 1.7.x e 8.x;
- Compatível com PHP 5.2.0 ao 8.1.27;
- Desenvolvido, testado e homologado com base na API v.4 do PagBank;
- Requer a biblioteca cURL ativa na hospedagem.
- Obrigatório o uso de certificado SSL com o protocolo TLS 1.2 ou superior;

## Instalação ou Atualização

Extraia o arquivo "pagseguro-modulo-prestashop-master.zip" em seu computador.

Acesse a Aba "Módulos > Gerenciador de Módulos", clique em "Enviar um módulo", e envie o arquivo "pagbank.zip".

![](https://prestabr.com.br/docpagbank/instalacao.jpg "Instalação - Módulo PagBank")

Pronto, o módulo está instalado!

**`Dica:`**

> Sempre que você instalar ou atualizar o módulo, acesse a tab "Parâmetros Avançados > Desempenho" e clique em "Limpar Cache". Isso irá garantir que todos os arquivos sejam atualizados no cache da PrestaShop.
>
> Ao atualizar o módulo não é preciso refazer a Configuração de Cadastro/Adesão App, durante a atualização as configurações salvas no módulo ficam intactas.

## Configuração

#### 1 - Cadastro/Adesão App

O cadastramento no App é o primeiro passo para tornar a sua integração funcional. São três opções disponíveis para cadastro:

- App D14
- App D30
- App Tax

![](https://prestabr.com.br/docpagbank/1_cadastro_adesao_app_00.jpg "Cadastro/Adesão App - Módulo PagBank")

O processo de adesão é muito simples, basta clicar em "Cadastrar" e seguir o passo-a-passo indicado.

![](https://prestabr.com.br/docpagbank/1_cadastro_adesao_app_1_1.jpg "Cadastro/Adesão App - Módulo PagBank")

---
#### 2 - Configurações do App

Após se cadastrar no(s) App(s) desejado(s), marque a opção "Ambiente de Produção" como SIM e no campo "Tipo de Credencial" selecione qual App deve validar e processar os pagamentos.

![](https://prestabr.com.br/docpagbank/2_configuracoes_do_app_0.jpg "Configurações do App - Módulo PagBank")

---
#### 3 - Configurações de Pagamento

O módulo disponibiliza quatro opções de pagamento via Checkout Transparente: 

- Cartão de Crédito (+30 bandeiras)
- Boleto Bancário
- Pix
- Pagar com PagBank

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_0.jpg "Configurações de Pagamento - Módulo PagBank")


**3.1 - Cartão de Crédito**

Marque a opção "Cartão de Crédito" como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Máximo de parcelas aceitas pela loja, coloque 1 para à vista ou de 2 até 12 parcelas.
- Quantidade de parcelas sem juros, coloque de 1 até 12 parcelas.
- Valor da parcela mínima aceita pela loja.
- Comportamento da parcela mínima aceita pela loja.
- Compra com 1 Click.
- Tipo de Captura - Automática ou Manual (Pré-autorização).

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_1.jpg "Configurações de Pagamento - Módulo PagBank")

**3.1.1 - Cartão de Crédito - Compra com 1 Click**

Na opção "Compra com 1 Click", o cliente poderá salvar o Cartão de Crédito para futuras compras. O Cartão é criptografado e armazenado pelo PagBank através do processo de Tokenização.

Após realizar a primeira compra com o cartão salvo ele ficará disponível para seleção na tela de checkout, exemplo:

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_1_11.jpg "Configurações de Pagamento - Módulo PagBank")

**3.1.2 - Cartão de Crédito - Tipo de Captura - Automática ou Manual (Pré-autorização)**

Na opção de Captura Automática o valor total da transação é debitado imediatamente do saldo do cartão de crédito, todo o processo é automatizado.

Na opção de Captura Manual (Pré-autorização) o valor total da transação fica reservado no saldo do cartão de crédito, aguardando uma ação para processar um valor parcial ou total da transação. Por exemplo, se o valor total da transação for R$ 1.000,00 e você decidir capturar R$ 800,00 os R$ 200,00 restantes será devolvido automaticamente para o saldo do cartão de crédito.

Com o pedido gerado na loja, com o status de Pagamento Autorizado, é só acessar o histórico do pedido e informar um valor parcial ou total que você deseja debitar do cartão de crédito, exemplo:

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_1_2.jpg "Configurações de Pagamento - Módulo PagBank")

**3.2 - Boleto Bancário**

Marque a opção "Boleto Bancário" como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Prazo de vencimento do boleto (O padrão é 2 dias).
- Texto descritivo para o boleto.

**`Nota:`**

> Não esqueça de configurar a Tarefa Cron para o cancelamento do Boleto.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_2.jpg "Configurações de Pagamento - Módulo PagBank")

**3.3 - PIX**

Marque a opção "PIX" como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Prazo limite de pagamento via PIX (O padrão é 30 minutos).

**`Nota:`**

> Não esqueça de configurar a Tarefa Cron para o cancelamento do PIX.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_3.jpg "Configurações de Pagamento - Módulo PagBank")

**3.4 - Pagar com PagBank**

Marque a opção "Pagar com PagBank" como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.
Nesta opção de pagamento o cliente poderá realizar o pagamento com o saldo em conta ou cartão de crédito salvo no super app PagBank.

**`Nota:`**

> Não esqueça de configurar a Tarefa Cron para o cancelamento do pagamento via Pagar com PagBank.

> Por enquanto, esta opção de pagamento tem algumas limitações:
> - Não é possível especificar o prazo de expiração do qrcode/link de pagamento, o prazo padrão é de 24hs.
> - Não é possível oferecer desconto no pagamento à vista.
> - Não é possível especificar se o pagamento via cartão de crédito será com Pré-autorização.
> - Não é possível especificar se o cliente poderá pagar somente com saldo em conta ou somente com cartão de crédito. As duas opções estarão disponíveis.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_3_4.jpg "Configurações de Pagamento - Módulo PagBank")

**3.5 - Opções de Descontos**

- Desconto de valor fixo ou percentual.
- Valor do desconto.
- Desconto no Cartão de Crédito (1x).
- Desconto no Boleto Bancário.
- Desconto no Pix.

> No Cartão e Crédito o desconto é calculado e exibido na primeira parcela.
> Por enquanto, a opção de desconto não está disponível na modalidade: Pagar com PagBank.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_4.jpg "Configurações de Pagamento - Módulo PagBank")

---
#### 4 - Status de Pedido - Mapeamento

Para facilitar o gerenciamento do pedido disponibilizamos a opção de mapeamento de Status. Desta forma você poderá criar status customizados que servirão especificamente para essa finalidade. 

Após criar os Status customizados na PrestaShop - no Menu "Compras > Status" - basta acessar a configuração do módulo para fazer a associação.

Os Status disponíveis são:

- Pagamento Aceito
- Pagamento Autorizado
- Pedido Cancelado
- Pedido Estornado
- Pagamento em Análise
- Aguardando Pagamento

![](https://prestabr.com.br/docpagbank/4_status_de_pedido_01.jpg "Status de Pedido - Módulo PagBank")

---
#### 5 - Debug & Logs

**5.1 - Debug para soluções de problemas**

Para uma análise técnica mais aprofundada, a fim auxiliar no desenvolvimento ou identificar eventuais conflitos de JavaScript na loja virtual, marque a opção "Exibir parâmetros no Console do navegador?" como SIM. 

![](https://prestabr.com.br/docpagbank/5_debug_e_logs_1.jpg "Debug & Logs - Módulo PagBank")

**`Dica:`**

> Sempre que você instalar ou atualizar o módulo, acesse a tab "Parâmetros Avançados > Desempenho" e clique em "Limpar Cache". Isso irá garantir que todos os arquivos sejam atualizados no cache da PrestaShop.

**5.2 - Registro de Transações & Gerenciamento de Logs**

Marque a opção "Gerar LOGs completos?" como SIM para que o módulo registre tudo o que é enviado e recebido entre a sua loja e o PagBank.

![](https://prestabr.com.br/docpagbank/5_debug_e_logs_2_a.jpg "Debug & Logs - Módulo PagBank")

Para vistualizar os registros de Logs acesse "PagBank > PagBank - Logs", clique em "Ver" para analisar detalhes do que foi enviado e recebido entre a sua loja e o PagBank.

![](https://prestabr.com.br/docpagbank/5_debug_e_logs_2_b.jpg "Debug & Logs - Módulo PagBank")

**`Dica:`**

> O Código de Referência da transação no PagBank é composto pelo ID do Carrinho + APP utilizado + Número randômico, exemplo:
>
>Código de Referência: 78895.TAX.177850

**`Nota:`**

> - Dados sensíveis ao cliente como endereço, nome e sobrenome ou razão social, cpf/cnpj e telefone ou celular não são armazenados em Log.
> - Todas as transações via Cartão de Crédito são Tokenizadas e não são armazenadas em Log.

Para maiores detalhes a respeito do pedido acesse "PagBank > PagBank - Transações", clique em "Ver". As mesmas informações também estão disponíveis no histórico do pedido (consulta em tempo real entre a sua loja e o PagBank).

![](https://prestabr.com.br/docpagbank/5_debug_e_logs_2_c.jpg "Debug & Logs - Módulo PagBank")

**5.3 - Limpeza do Banco de Dados**

A opção "Apagar tabelas do banco?" deve permanecer sempre desativada. Apenas marque a opção como SIM caso deseje desinstalar o módulo. 

Com esta opção ativa todos os registros de transações (informações básicas sobre o pedido, como: ID do pedido na loja e PagBank, ID cliente, ID carrinho, etc.) e principalmente o registro de Logs (todo o registro técnico da comunicação entre a loja e API do PagBank) serão completamente removidos do Banco de Dados da sua loja.

Este recurso serve para evitar a perda de dados ao desinstalar o módulo por acidente ou durante uma atualização da própria loja.

![](https://prestabr.com.br/docpagbank/5_debug_e_logs_3.jpg "Debug & Logs - Módulo PagBank")

---
#### 6 - Tarefa CRON

A Tarefa Cron serve para cancelar os pedidos que não forem pagos dentro do prazo estipulado para Boleto Bancário e Pix, também é útil para o seu gerenciamento de estoque. Para configurar a Tarefa Cron entre em contato com o suporte técnico do seu servidor de hospedagem e informe as URLs geradas para a sua loja.

![](https://prestabr.com.br/docpagbank/6_tarefa_cron_0.jpg "Tarefa CRON - Módulo PagBank")

---
#### 7 - Extra - Estorno Parcial ou Total de um Pedido

Na PrestaShop, no menu "Compras ou Pedidos", acesse o pedido a ser estornado, role a página até localizar "DADOS DO PEDIDO (PAGBANK)". Ao clicar em "Estornar Transação no PagBank", em tempo real, o módulo transmitirá a requisição para o PagBank.

![](https://prestabr.com.br/docpagbank/7_extra_estorno_0.jpg "Extra - Módulo PagBank")

Após realizar o estorno, dentro de alguns segundos, o pedido receberá uma notificação para a troca de status. Pedidos com estorno Total recebem o status mapeado como Cancelado e para estorno Parcial recebe o status mapeado como Estornado. 

Para mais detalhes sobre o pedido estornado acesse a sua conta no PagBank, no menu, "Extratos e Relatórios > Extrato de Transações".

Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".

**`Link de acesso`**

**https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes**

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