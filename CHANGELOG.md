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

**v.2.1.0**
- Add validação para CNPJ alfanumérico;
- Revisão e ajustes gerais de layout, otimizado para Bootstrap 3, 4 e 5.
- Melhorias e correções gerais de bugs;

**v.2.2.0**
- Add opção de pagamento com 2 Cartões de Crédito;
- Add opção de integração com o Google reCAPTCHA v3;
- Otimizações e melhorias de processos, validações, layouts e correções gerais de bugs;