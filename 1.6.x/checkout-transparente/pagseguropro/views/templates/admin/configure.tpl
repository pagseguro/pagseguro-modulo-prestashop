{*
* 2018 PrestaBR
* 
* Módulo de Pagamento para Integração com o PagSeguro
*
* Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
* Checkout Transparente 
*
*}
<!-- Header -->
<div class="panel">
   <div class="row moduleconfig-header">
      <div class="col-lg-4 col-xs-12 text-center" align="center">
         <a class="pagseguro" href="https://pagseguro.uol.com.br/" target="_blank" title="Pagseguro | Venda Online com Segurança!">
         <img src="{$module_dir|escape:'html':'UTF-8'}img/logo_pagseguro.png" class="img-responsive" />
         </a>
      </div>
      <div class="documentacao col-lg-4 col-xs-12 text-center" align="center">
         <h2>{l s='PagSeguro - Checkout Transparente' mod='pagseguropro'} 
            <small>v.{$module_version}</small>
         </h2>
      </div>
      <div class="col-lg-4 col-xs-12 text-center" align="center">
         <a class="prestabr" href="https://prestabr.com.br/" target="_blank" title="Desenvolvido por PrestaBR e-Commerce Solutions">
         <img src="//prestabr.com.br/logo_mini.png" class="img-responsive" alt="PrestaBR" />
         </a>
      </div>
   </div>
   <div class="payment_options row">
      <div class="credito col-xs-12 col-sm-6 clearfix nopadding-left">
         <div class="panel clearfix">
            <div class="panel-heading">Cartão de Crédito</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="mastercard brand-logo" title="Mastercard">
                     Mastercard
                     </span>
                  </li>
                  <li>
                     <span class="visa brand-logo" title="Visa">
                     Visa
                     </span>
                  </li>
                  <li>
                     <span class="elo brand-logo" title="Elo">
                     Elo
                     </span>
                  </li>
                  <li>
                     <span class="amex brand-logo" title="American Express">
                     American Express
                     </span>
                  </li>
                  <li>
                     <span class="hipercard brand-logo" title="Hipercard">
                     Hipercard
                     </span>
                  </li>
                  <li>
                     <span class="hiper brand-logo" title="Hiper">
                     Hiper
                     </span>
                  </li>
                  <li>
                     <span class="diners brand-logo" title="Diners">
                     Diners
                     </span>
                  </li>
                  <li>
                     <span class="cabal brand-logo" title="Cabal">
                     Cabal
                     </span>
                  </li>
                  <li>
                     <span class="grandcard brand-logo" title="Grand Card">
                     Grand Card
                     </span>
                  </li>
                  <li>
                     <span class="sorocred brand-logo" title="Soro Cred">
                     Soro Cred
                     </span>
                  </li>
                  <li>
                     <span class="valecard brand-logo" title="Vale Card">
                     Vale Card
                     </span>
                  </li>
                  <li>
                     <span class="brasilcard brand-logo" title="Brasil Card">
                     Brasil Card
                     </span>
                  </li>
                  <li>
                     <span class="mais brand-logo" title="Mais">
                     Mais
                     </span>
                  </li>
                  <li>
                     <span class="fortbrasil brand-logo" title="FortBrasil">
                     FortBrasil
                     </span>
                  </li>
                  <li>
                     <span class="aura brand-logo" title="Aura">
                     Aura
                     </span>
                  </li>
                  <li>
                     <span class="personalcard brand-logo" title="Personal Card">
                     Personal Card
                     </span>
                  </li>
                  <li>
                     <span class="banesecard brand-logo" title="Banese Card">
                     Banese Card
                     </span>
                  </li>
                  <li>
                     <span class="upbrasil brand-logo" title="Up Brasil">
                     Up Brasil
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="boleto col-xs-12 col-sm-3 clearfix">
         <div class="panel clearfix ajust">
            <div class="panel-heading">Boleto Bancário</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="boleto brand-logo" title="Boleto">
                     Boleto
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="debito col-xs-12 col-sm-3 clearfix nopadding-right">
         <div class="panel clearfix ajust">
            <div class="panel-heading">Débito online</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="bradesco brand-logo" title="Bradesco">
                     Bradesco
                     </span>
                  </li>
                  <li>
                     <span class="bb brand-logo" title="Banco do Brasil">
                     Banco do Brasil
                     </span>
                  </li>
                  <li>
                     <span class="itau brand-logo" title="Itaú">
                     Itaú
                     </span>
                  </li>
                  <li>
                     <span class="hsbc brand-logo" title="HSBC">
                     HSBC
                     </span>
                  </li>
                  <li>
                     <span class="banrisul brand-logo" title="Banrisul">
                     Banrisul
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
   </div>
   <div class="row itens">
      <div class="links" align="center">
         <ul class="list list-inline">
            <li>
               <a class="btn btn-primary" href="https://devs.pagseguro.uol.com.br/docs/modulos-prestashop" target="_blank">Documentação do Módulo</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_transacoes}" target="_blank">Minhas Transações</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_logs}" target="_blank">Meus Logs</a>
            </li>
         </ul>
      </div>
   </div>
</div>
