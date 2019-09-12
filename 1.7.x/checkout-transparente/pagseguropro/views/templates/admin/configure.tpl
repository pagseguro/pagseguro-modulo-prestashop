{*
 * 2019 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<!-- Header -->
<div class="panel">
   <div class="row moduleconfig-header">
      <div class="col-lg-4 col-xs-12 text-center" align="center">
         <a class="pagseguro" href="https://pagseguro.uol.com.br/" target="_blank" title="Pagseguro | Venda Online com Segurança!">
         <img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo_pagseguro.png" class="img-responsive" />
         </a>
      </div>
      <div class="documentacao col-lg-4 col-xs-12 text-center" align="center">
         <h2>{l s='PagSeguro - Checkout Transparente' d='Modules.PagSeguroPro.Admin'} 
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
            <div class="panel-heading">{l s='Cartão de Crédito' d='Modules.PagSeguroPro.Admin'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="mastercard brand-logo" title="Mastercard">
                     {l s='Mastercard' module='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="visa brand-logo" title="Visa">
                     {l s='Visa' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="elo brand-logo" title="Elo">
                     {l s='Elo' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="amex brand-logo" title="American Express">
                     {l s='American Express' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="hipercard brand-logo" title="Hipercard">
                     {l s='Hipercard' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="hiper brand-logo" title="Hiper">
                     {l s='Hiper' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="diners brand-logo" title="Diners">
                     {l s='Diners' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="cabal brand-logo" title="Cabal">
                     {l s='Cabal' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="grandcard brand-logo" title="Grand Card">
                     {l s='Grand Card' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="sorocred brand-logo" title="Soro Cred">
                     {l s='Soro Cred' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="valecard brand-logo" title="Vale Card">
                     {l s='Vale Card' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="brasilcard brand-logo" title="Brasil Card">
                     {l s='Brasil Card' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="mais brand-logo" title="Mais">
                     {l s='Mais' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="fortbrasil brand-logo" title="FortBrasil">
                     {l s='FortBrasil' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="aura brand-logo" title="Aura">
                     {l s='Aura' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="personalcard brand-logo" title="Personal Card">
                     {l s='Personal Card' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="banesecard brand-logo" title="Banese Card">
                     {l s='Banese Card' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="upbrasil brand-logo" title="Up Brasil">
                     {l s='Up Brasil' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="boleto col-xs-12 col-sm-3 clearfix">
         <div class="panel clearfix ajust">
            <div class="panel-heading">{l s='Boleto Bancário' d='Modules.PagSeguroPro.Admin'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="boleto brand-logo" title="Boleto">
                     {l s='Boleto' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="debito col-xs-12 col-sm-3 clearfix nopadding-right">
         <div class="panel clearfix ajust">
            <div class="panel-heading">{l s='Débito online' d='Modules.PagSeguroPro.Admin'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="bradesco brand-logo" title="Bradesco">
                     {l s='Bradesco' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="bb brand-logo" title="Banco do Brasil">
                     {l s='Banco do Brasil' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="itau brand-logo" title="Itaú">
                     {l s='Itaú' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="hsbc brand-logo" title="HSBC">
                     {l s='HSBC' d='Modules.PagSeguroPro.Admin'}
                     </span>
                  </li>
                  <li>
                     <span class="banrisul brand-logo" title="Banrisul">
                     {l s='Banrisul' d='Modules.PagSeguroPro.Admin'}
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
               <a class="btn btn-primary" href="https://github.com/pagseguro/prestashop/tree/master/1.7.x/checkout-transparente/pagseguropro" target="_blank">{l s='Documentação do Módulo' d='Modules.PagSeguroPro.Admin'}</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_transacoes}" target="_blank">{l s='Minhas Transações' d='Modules.PagSeguroPro.Admin'}</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_logs}" target="_blank">{l s='Meus Logs' d='Modules.PagSeguroPro.Admin'}</a>
            </li>
         </ul>
      </div>
   </div>
</div>

{literal} 
<script type="text/javascript">
	function showCredentials(campo) {
		if (!campo || campo == 'TOKEN' || campo == ''){
			$('.normal').parent().parent().show('fast');
		}else{
			$('.normal').parent().parent().hide('fast');
		}
	}
	function removeParam(key, sourceURL) {
		var rtn = sourceURL.split("?")[0],
			param,
			params_arr = [],
			queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
		if (queryString !== "") {
			params_arr = queryString.split("&");
			for (var i = params_arr.length - 1; i >= 0; i -= 1) {
				param = params_arr[i].split("=")[0];
				if (param === key) {
					params_arr.splice(i, 1);
				}
			}
			rtn = rtn + "?" + params_arr.join("&");
		}
		return rtn;
	}
	$(document).ready(function() {
		showCredentials('{/literal}{$credential}{literal}');
	});
</script>
{/literal}		
{if isset($appCode) && $appCode}
<div id="hidden_div" style="display:none;">
	<div class="alert alert-info" align="center">
		<p style="text-align: center;font-size: 15px;">{l s='Após autorizar a sua aplicação, não esqueça de alterar o tipo de credencial configurada.' d='Modules.PagSeguroPro.Admin'}</p>
		<p align="center" style="padding: 15px 0 0 0;">
			<a href="javascript:void(0);" class="btn btn-success" onclick="reloadPage();" style="text-align: center;background: #93e89a;padding: 10px 30px;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;color: #fff;font-weight: bold;text-decoration: none;">{l s='OK' d='Modules.PagSeguroPro.Admin'}</a>
		</p>
	</div>
</div>

	{literal} 
	<script type="text/javascript">
		var openurl = '{/literal}{$link_app}{$appCode}{literal}';
		window.open(openurl, 'Gerar Código da Aplicação');
		//console.log(cleanUrl);
		
		function reloadPage() {
			var current_url = '{/literal}{$this_page}{literal}';
			var cleanU = removeParam('gerarAutorizacao', current_url);
			var cleanUrl = removeParam('tipoAutorizacao', cleanU);
			window.location = cleanUrl;
		}
		$(document).ready(function() {
			$.fancybox.open({href: "#hidden_div"});
		});
	</script>
	{/literal}		
{/if}
