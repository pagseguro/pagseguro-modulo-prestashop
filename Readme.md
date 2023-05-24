![](https://prestabr.com.br/docpagbank/head_git_pagbank_v1.2.0.jpg)

## Enquete

Queremos saber a sua opinião, você gostaria de ver Split de Pagamentos para a Plataforma PrestaShop?

[Responder pesquisa](https://docs.google.com/forms/d/e/1FAIpQLSdNgV6JmI0i8ktw86DN6B1riogiQ8Q8gd1i1rULfL8TmwAZBQ/viewform?usp=sf_link)

## Novo Módulo disponível!

O módulo PagSeguroPro foi descontinuado e substituído pelo novo módulo PagBank. Para atualizar o módulo em sua loja basta seguir o passo-a-passo de instalação e em seguida desativar o módulo PagSeguroPro.

![](https://prestabr.com.br/docpagbank/novo_modulo_disponivel.jpg "Novo Módulo Disponível - Módulo PagBank")

## Introdução

Através do módulo PagBank oferecemos total integração da sua loja PrestaShop com a melhor solução de pagamentos do Brasil.

Este módulo foi desenvolvido rigorosamente dentro dos padrões de segurança PCI DSS (Padrão de Segurança de Dados da Indústria de Pagamento com Cartão) e boas práticas de desenvolvimento recomendadas pela PrestaShop, com o objetivo de simplificar e agilizar o processo de Checkout. Não importa o seu ramo de atividade, este módulo irá potencializar suas vendas otimizando a experiência de compra do seu cliente em sua loja virtual.

## Compatibilidade

- Este módulo não utiliza Override;
- Compatível com PrestaShop 1.6.x, 1.7.x e 8.x;
- Compatível com PHP 5.2.0 ao 8.1.18;
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

## Configuração

#### 1 - Cadastro/Adesão App

O cadastramento no App é o primeiro passo para tornar a sua integração funcional. São três opções disponíveis para cadastro:

- App D14
- App D30
- App Tax

![](https://prestabr.com.br/docpagbank/1_cadastro_adesao_app_0.jpg "Cadastro/Adesão App - Módulo PagBank")

O processo de adesão é muito simples, basta clicar em "Cadastrar" e seguir o passo-a-passo indicado.

![](https://prestabr.com.br/docpagbank/1_cadastro_adesao_app_1_1.jpg "Cadastro/Adesão App - Módulo PagBank")

---
#### 2 - Configurações do App

Após se cadastrar no(s) App(s) desejado(s), marque a opção "Ambiente de Produção" como SIM e no campo "Tipo de Credencial" selecione qual App deve validar e processar os pagamentos.

![](https://prestabr.com.br/docpagbank/2_configuracoes_do_app_0.jpg "Configurações do App - Módulo PagBank")

---
#### 3 - Configurações de Pagamento

O módulo disponibiliza três opções de pagamento via Checkout Transparente: 

- Cartão de Crédito (+30 bandeiras)
- Boleto Bancário
- Pix

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_0.jpg "Configurações de Pagamento - Módulo PagBank")


**3.1 - Cartão de Crédito**

Marque a opção "Cartão de Crédito" como SIM para ativar o meio de pagamento e visualizar as configurações relacionadas.

- Máximo de parcelas aceitas pela loja, coloque 1 para à vista ou de 2 até 12 parcelas.
- Quantidade de parcelas sem juros, coloque de 1 até 12 parcelas.
- Valor da parcela mínima aceita pela loja.
- Comportamento da parcela mínima aceita pela loja.
- Compra com 1 Click.

> Na opção "Compra com 1 Click", o cliente poderá salvar o Cartão de Crédito para futuras compras. O Cartão é criptografado e armazenado pelo PagBank através do processo de Tokenização.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_1.jpg "Configurações de Pagamento - Módulo PagBank")

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

**3.4 - Opções de Descontos**

- Desconto de valor fixo ou percentual.
- Valor do desconto.
- Desconto no Cartão de Crédito (1x).
- Desconto no Boleto Bancário.
- Desconto no Pix.

> No Cartão e Crédito o desconto é calculado e exibido na primeira parcela.

![](https://prestabr.com.br/docpagbank/3_configuracoes_de_pagamento_4.jpg "Configurações de Pagamento - Módulo PagBank")

---
#### 4 - Status de Pedido - Mapeamento

Para facilitar o gerenciamento do pedido disponibilizamos a opção de mapeamento de Status. Desta forma você poderá criar status customizados que servirão especificamente para essa finalidade. 

Após criar os Status customizados na PrestaShop - no Menu "Compras > Status" - basta acessar a configuração do módulo para fazer a associação.

Os Status disponíveis são:

- Pagamento Autorizado
- Pedido Cancelado
- Pedido Estornado
- Pagamento em Análise
- Aguardando Pagamento

![](https://prestabr.com.br/docpagbank/4_status_de_pedido_0.jpg "Status de Pedido - Módulo PagBank")

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