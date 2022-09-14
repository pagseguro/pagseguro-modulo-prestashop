![](https://prestabr.com.br/docpagseguropro/16/head_github_checkout_transparente_ps.jpg)

## Introdução

Através do módulo PagSeguroPro oferecemos total integração da sua loja PrestaShop com a melhor solução de pagamentos do Brasil.

Este módulo foi desenvolvido rigorosamente dentro dos padrões de segurança PCI DSS (Padrão de Segurança de Dados para a Indústria de Cartões de Pagamento) e boas práticas de desenvolvimento recomendadas pela PrestaShop, com o objetivo de simplificar e agilizar o processo de Checkout. Não importa o seu ramo de atividade, este módulo irá potencializar suas vendas otimizando a experiência de compra do seu cliente em sua loja virtual.

## Compatibilidade

- Este módulo não utiliza Override;
- Compatível com o todas as versões da série 1.6.x da PrestaShop;
- Testado e desenvolvido com base na API v.2 e v.3 do PagSeguro;
- Compatível com PHP 5.4.x à 7.4.33;
- Requer a biblioteca cURL ativa na hospedagem.
- Obrigatório o uso de certificado SSL com o protocolo TLS 1.2 ou superior;

## Instalação

Extraia o arquivo .ZIP em seu computador e envie a pasta do módulo desejado para o diretório /modules/ no FTP da sua loja.

Em seguida, acesse a Aba Módulos, localize o módulo pesquisando por "pagseguropro" e clique no botão "Instalar".

![](https://prestabr.com.br/docpagseguropro/16/instalacao.jpg "Instalação - Módulo PagSeguroPro")

## Configuração

---
#### 1 - Ativação

##### E-mail & Token

A Ativação, E-mail & Token são os primeiros passos para tornar a sua integração funcional. Após se cadastrar e formalizar a contratação do serviço junto ao PagSeguro, você receberá um Token que será utilizado para referenciar a sua conta e validar os pagamentos processados.

Com os dados em mãos basta marcar a opção Ambiente de Produção como SIM e em seguida, no campo Tipo de Credenciais, selecionar a opção Padrão (E-mail + Token) e copiar e colar o seu E-mail de cadastro e o seu Token nos campos indicados abaixo.

![](https://prestabr.com.br/docpagseguropro/16/ativacao.jpg "Configuração - Ativação, E-mail & Token - Módulo PagSeguroPro")

##### Parceria (Recebimento em 14 ou 30 dias)

Você também pode optar por utilizar o modelo de parceria. O processo de adesão é muito simples, basta clicar em "Assine Já!" e seguir o passo-a-passo indicado.

![](https://prestabr.com.br/docpagseguropro/16/parceria1.jpg "Configuração - Ativação, E-mail & Token - Parceria - Módulo PagSeguroPro")

Passo-a-passo de Adesão

![](https://prestabr.com.br/docpagseguropro/16/parceria2.jpg "Configuração - Ativação, E-mail & Token - Parceria - Módulo PagSeguroPro")

Após assinar uma ou as duas parcerias oferecidas, nas configurações do módulo, vá até o campo Tipo de Credenciais e selecione qual das parcerias deve validar e processar os pagamentos.

![](https://prestabr.com.br/docpagseguropro/16/parceria3.jpg "Configuração - Ativação, E-mail & Token - Parceria - Módulo PagSeguroPro")

---
#### 2 - Opções de Pagamento

O módulo disponibiliza 3 opções de pagamento via Checkout Transparente:

- Cartão de Crédito

![](https://prestabr.com.br/docpagseguropro/16/cartao.jpg "Configuração - Opções de Pagamento - Cartão de Crédito - Módulo PagSeguroPro")

- Boleto Bancário

![](https://prestabr.com.br/docpagseguropro/16/boleto.jpg "Configuração - Opções de Pagamento - Boleto Bancário - Módulo PagSeguroPro")

- Débito Online 

![](https://prestabr.com.br/docpagseguropro/16/transferencia.jpg "Configuração - Opções de Pagamento - Débito online - Módulo PagSeguroPro")

Marque a opção como SIM para ativar o meio de pagamento em sua loja:

![](https://prestabr.com.br/docpagseguropro/16/pagamentos.jpg "Configuração - Opções de Pagamento - Módulo PagSeguroPro")

**`Atenção`**

Não se esqueça de verificar se o meio de pagamento está ativo em sua conta no PagSeguro.

**https://pagseguro.uol.com.br/preferences/receiving.jhtml**

---
#### 3 - Configurações de Pagamento: Parcelamento

- Defina o máximo de parcelas aceitas pela loja, coloque 1 para à vista ou de 2 até 12 parcelas.

- Defina a quantidade de parcelas sem juros, coloque de 1 até 12 parcelas.

- Defina o valor da parcela mínima aceita pela loja.

- Defina o comportamento da parcela mínima aceita pela loja.

![](https://prestabr.com.br/docpagseguropro/16/parcelamento.jpg "Configuração - Parcelamento - Módulo PagSeguroPro")

**`Observação:`**

A taxa de juros pode variar de acordo com o teto de faturamento da loja ou a sua negociação contratual junto ao PagSeguro.

---
#### 4 - Configurações de Pagamento: Desconto no Boleto Bancário

Na opção "Desconto no Boleto Bancário?" você pode definir se o Boleto Bancário irá receber um desconto em percentual ou valor fixo, além da opção "Nenhum Desconto".

![](https://prestabr.com.br/docpagseguropro/16/descontoboleto.jpg "Configuração - Desconto no Boleto Bancário - Módulo PagSeguroPro")

Após definir o tipo de desconto aplicado no Boleto Bancário basta definir o valor, exemplo: 5.00.

---
#### 5 - Status de Pedido - Mapeamento

Para facilitar o gerenciamento do pedido disponibilizamos a opção de mapeamento de Status. Desta forma você poderá criar status customizados que servirão especificamente para essa finalidade. 

Após criar os Status customizados na PrestaShop - no Menu "Compras > Status" - basta acessar a configuração do módulo para fazer a associação.

Os Status disponíveis são:

- Pagamento Autorizado
- Pedido Cancelado
- Pedido Estornado
- Pagamento em Análise
- Aguardando Pagamento do Boleto

![](https://prestabr.com.br/docpagseguropro/16/status.jpg "Configuração - Status de Pedido - Módulo PagSeguroPro")

**`Atenção`**

Exitem 02 formas de cancelar um pedido:

**a)** Na PrestaShop, no menu "Compras ou Pedidos", acessando o pedido em questão, role a página até localizar "DADOS DO PEDIDO (PAGSEGURO)". Clique no botão "Cancelar Pedido" e em tempo real o módulo transmitirá a requisição para o PagSeguro.

**b)** Diretamente em sua conta no PagSeguro em "Extrato de Transações". Ao cancelar o pedido o PagSeguro irá transmitir para a sua loja a requisição de cancelamento que receberá o Satus Mapeado no módulo.

**https://pagseguro.uol.com.br/transaction/search.jhtml**

---
#### 6 - Debug para soluções de problemas

Para determinar se o módulo está funcional, sem nenhum tipo de problema de JavaScript, marque a opção "Exibir parâmetros no Console do navegador?" como SIM. 

![](https://prestabr.com.br/docpagseguropro/16/debug.jpg "Configuração - Debug para soluçoes de problemas - Módulo PagSeguroPro")

Após a checagem acima vá até a tela de Checkout da Loja, acesse o Inspetor de Elementos do
Navegador pressionando “CTRL+Shift+i” ou F12.

Ao abrir o Inspetor de Elementos, clique na tab “Console” e verifique se estão presentes as
seguintes informações:

- SessionID
- SenderHash
- paymentMethods
- installments
- valorPedido

Com as 05 informações disponíveis no Console do Inspetor de Elementos do Navegador,
significa que não houve problemas de conflitos interferindo no funcionamento do JavaScript
do módulo, e a Loja está pronta para processar pagamentos.

**`Observações:`**

**a)** Demais informações como “Token do Cartão de Crédito” não estão disponíveis por
motivos de segurança.

**b)** Para consultar o valor do pedido comece a digitar “valorPedido”, vide print do
exemplo abaixo.

**`Exemplo na Prática:`**

![](https://prestabr.com.br/docpagseguropro/16/debugpratica.jpg "Configuração - Debug para soluçoes de problemas - Módulo PagSeguroPro")

---
#### 7 - Registro de Transações & Gerenciamento de Logs

Marque a opção como SIM para que o módulo registre tudo o que é enviado e recebido entre a sua loja e o PagSeguro.

![](https://prestabr.com.br/docpagseguropro/16/logativa.jpg "Configuração - Registro & Gerenciamento de Logs - Módulo PagSeguroPro")

Para vistualizar os registros de Logs acesse "Compras > PagSeguro - Logs", clique em "Ver" para analisar detalhes do que foi enviado e recebido entre a sua loja e o PagSeguro.

![](https://prestabr.com.br/docpagseguropro/16/loglista.jpg "Configuração - Registro & Gerenciamento de Logs - Módulo PagSeguroPro")

Para maiores detalhes a respeito do pedido acesse "Compras > PagSeguro - Transações", clique em "Ver". As mesmas informações também estão disponíveis no histórico do pedido (consulta em tempo real entre a sua loja e o PagSeguro).

![](https://prestabr.com.br/docpagseguropro/16/logtransacoes.jpg "Configuração - Registro & Gerenciamento de Logs - Módulo PagSeguroPro")

---
#### 8 - Limpeza do Banco de Dados

A opção "Apagar tabelas do banco?" deve permanecer sempre desativada. Apenas marque a opção como SIM caso deseje desinstalar o módulo. 

Com esta opção ativa todos os registros de transações (informações básicas sobre o pedido, como: ID do pedido na loja e PagSeguro, ID cliente, ID carrinho, etc.) e principalmente o registro de Logs (todo o registro técnico da comunicação entre a loja e API do PagSeguro) serão completamente removidos do Banco de Dados da sua loja.

Este recurso serve para evitar a perda de dados ao desinstalar o módulo por acidente ou durante uma atualização da própria loja.

![](https://prestabr.com.br/docpagseguropro/16/limpezabanco.jpg "Configuração - Limpeza do Banco de Dados - Módulo PagSeguroPro")

---
#### 9 - Extra - Estorno Parcial ou Total de um Pedido

Na PrestaShop, no menu "Compras ou Pedidos", acesse o pedido a ser estornado, role a página até localizar "DADOS DO PEDIDO (PAGSEGURO)". Ao clicar em "Estornar Transação no PagSeguro", em tempo real, o módulo transmitirá a requisição para o PagSeguro.

Após realizar o estorno, para mais detalhes acesse a sua conta no PagSeguro, no menu, "Extratos e Relatórios > Extrato de Transações".

Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".

Link de acesso: **https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes**

**`Atenção`**

Só é possível realizar o estorno se o pedido estiver em um destes status:

- 3 - Pagamento confirmado
- 4 - Valor disponível
- 5 - Em disputa

---
## CHANGELOG

**v.1.0.0**

- Lançamento;

**v.1.1.0**

- Add modelo de aplicação para adesão de parceria;
- Add opção para estorno integral e parcial do pagamento já aprovado;

**v.1.1.1**

- Melhorias diversas e correções gerais de bugs;

**v.1.1.2**

- Correções gerais de bugs;

**v.1.1.3**

- Fix Bug renderView AdminPagSeguroProController;
- Fix Bug GetInstallments/installmentValue;

**v.1.1.4**

- Fix Bug templateVars updateOrderStatus;
- Fix Bug callback updateOrderStatus p/ Boleto Pendente (Status 2 - Aguardando Pgto);
- Fix data_pedido (sql) p/ formato 24hs;
- Add novo Logo do PagSeguro;

**v.1.1.5**

- Fix Bug callback updateOrderStatus p/ Boleto Pendente (Status 2 - Aguardando Pgto);

**v.1.1.6**

- Fix Bug cancelTransaction;
- Fix Bug refundTransaction;
- Add opção p/ mapear o status de reembolso (Status 6 - Reembolsado);

**v.1.1.7**

- Ajustes, melhorias e correções no layout Back/Front - Desk/Mobile;
- Add status fixo de "Boleto Bancário" para o Cliente/Comprador;
- Add verificador para orientações sobre UF e SandBox;
- Add novo Logo do PagSeguro (Versão Final);

**v.1.1.8**

- Fix GetInstallments/maxInstallmentNoInterest;

**v.1.2.0**

- Maior compatibilidade com padrões de cadastros de CPF;
- Add opção de Parcela Mínima aceita no cartão;
- Melhorias e correções gerais de bugs;
- Fix jQuery CVE-2020-11023;

**v.1.3.0**

- Correções gerais de bugs;
- Add opção p/ definir o comportamento da Parcela Mínima aceita no cartão;
- Add tutorial para orientar o lojista durante o setup inicial da loja, c/ validações e informações gerais sobre o cadastro do cliente, configurações diversas, etc;
- Melhorias na validação e criação de histórico local de pedidos que receberem reebolso parcial ou total;
- Revisão detalhada, melhorias e otimizações na validação dos dados para a interação com o cliente na opção de pagamento via Cartão de Crédito;

**v.1.4.0**

- Revisão da modalidade débito online e remoção do banco HSBC;
- Melhorias e correções;

**v.1.5.0**
- Revisão e melhorias no mecanismo de Logs, otimização p/ os métodos Get & Callback;
- Correção do status duplicado p/ o Boleto Bancário (Status 1 - Aguardando Pgto);

**v.1.5.1**
- Correções gerais de bugs;

**v.1.5.2**
- Correções gerais de bugs de Notificação;

**v.1.6.0**
- Reformulação da interface de configuração no BackOffice & atualização da Doc;
- Correção do registro de Logs para Callback pós transacional;
- Fix deprecated e testes de compatibilidade para o PHP 7.4.33;
