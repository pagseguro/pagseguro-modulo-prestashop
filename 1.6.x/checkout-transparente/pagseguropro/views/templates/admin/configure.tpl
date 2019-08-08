{*
* 2018 PrestaBR
* 
* Módulo de Pagamento para Integração com o PagSeguro
*
* Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - PrestaShop 1.6.x
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
            <div class="panel-heading">{l s='Cartão de Crédito' mod='pagseguropro'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="mastercard brand-logo" title="Mastercard">
                     {l s='Mastercard' module='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="visa brand-logo" title="Visa">
                     {l s='Visa' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="elo brand-logo" title="Elo">
                     {l s='Elo' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="amex brand-logo" title="American Express">
                     {l s='American Express' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="hipercard brand-logo" title="Hipercard">
                     {l s='Hipercard' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="hiper brand-logo" title="Hiper">
                     {l s='Hiper' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="diners brand-logo" title="Diners">
                     {l s='Diners' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="cabal brand-logo" title="Cabal">
                     {l s='Cabal' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="grandcard brand-logo" title="Grand Card">
                     {l s='Grand Card' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="sorocred brand-logo" title="Soro Cred">
                     {l s='Soro Cred' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="valecard brand-logo" title="Vale Card">
                     {l s='Vale Card' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="brasilcard brand-logo" title="Brasil Card">
                     {l s='Brasil Card' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="mais brand-logo" title="Mais">
                     {l s='Mais' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="fortbrasil brand-logo" title="FortBrasil">
                     {l s='FortBrasil' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="aura brand-logo" title="Aura">
                     {l s='Aura' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="personalcard brand-logo" title="Personal Card">
                     {l s='Personal Card' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="banesecard brand-logo" title="Banese Card">
                     {l s='Banese Card' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="upbrasil brand-logo" title="Up Brasil">
                     {l s='Up Brasil' mod='pagseguropro'}
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="boleto col-xs-12 col-sm-3 clearfix">
         <div class="panel clearfix ajust">
            <div class="panel-heading">{l s='Boleto Bancário' mod='pagseguropro'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="boleto brand-logo" title="Boleto">
                     {l s='Boleto' mod='pagseguropro'}
                     </span>
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div class="debito col-xs-12 col-sm-3 clearfix nopadding-right">
         <div class="panel clearfix ajust">
            <div class="panel-heading">{l s='Débito online' mod='pagseguropro'}</div>
            <div class="panel-body">
               <ul class="list list-inline">
                  <li>
                     <span class="bradesco brand-logo" title="Bradesco">
                     {l s='Bradesco' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="bb brand-logo" title="Banco do Brasil">
                     {l s='Banco do Brasil' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="itau brand-logo" title="Itaú">
                     {l s='Itaú' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="hsbc brand-logo" title="HSBC">
                     {l s='HSBC' mod='pagseguropro'}
                     </span>
                  </li>
                  <li>
                     <span class="banrisul brand-logo" title="Banrisul">
                     {l s='Banrisul' mod='pagseguropro'}
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
               <a class="btn btn-primary" href="https://github.com/pagseguro/prestashop/tree/master/1.6.x/checkout-transparente/pagseguropro" target="_blank">{l s='Documentação do Módulo' mod='pagseguropro'}</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_transacoes}" target="_blank">{l s='Minhas Transações' mod='pagseguropro'}</a>
            </li>
            <li>
               <a class="btn btn-primary" href="{$link_logs}" target="_blank">{l s='Meus Logs' mod='pagseguropro'}</a>
            </li>
         </ul>
      </div>
   </div>
</div>

<div class="panel">
	<div id="app-rates" class="row">
		<h2 class="app-bigtitle">{l s='Aproveite as condições diferenciadas' mod='pagseguropro'}<sup>1</sup> {l s='do PagSeguro e aceite as' mod='pagseguropro'}<br /> {l s='principais bandeiras de débito on-line, crédito' mod='pagseguropro'}<sup>2</sup> {l s='ou pagamento via boleto' mod='pagseguropro'}</h2>

		<div class="allapps col-lg-12 col-xs-12">
		<div class="appd14 col-lg-6 col-xs-12" align="center">
			<img src="{$module_dir|escape:'html':'UTF-8'}img/app-d14.jpg" class="img-responsive" />
			<p>
				{if (isset($authd14) && $authd14 != false)}
					<span class="btn btn-success signit" onclick="javascript:alert('Recebimento em 14 dias já autorizado!');">{l s='JÁ AUTORIZADO!' mod='pagseguropro'}</span>
				{else}
					<a class="btn btn-warning signit" href="{$link_page}&gerarAutorizacao=1&tipoAutorizacao=d14" onclick="return confirm('Tem certeza que deseja gerar um código de Autorização para uma taxa exclusiva e Recebimento em 14 dias?');">{l s='ASSINE JÁ!' mod='pagseguropro'}</a>
				{/if}
			</p>
		</div>

		<div class="appd30 col-lg-6 col-xs-12" align="center">
			<img src="{$module_dir|escape:'html':'UTF-8'}img/app-d30.jpg" class="img-responsive" />
			<p>
				{if (isset($authd30) && $authd30 != false)}
					<span class="btn btn-success signit" onclick="javascript:alert('Recebimento em 30 dias já autorizado!');">{l s='JÁ AUTORIZADO!' mod='pagseguropro'}</span>
				{else}
					<a class="btn btn-warning signit" href="{$link_page}&gerarAutorizacao=1&tipoAutorizacao=d30" onclick="return confirm('Tem certeza que deseja gerar um código de Autorização para uma taxa exclusiva e Recebimento em 30 dias?');">{l s='ASSINE JÁ!' mod='pagseguropro'}</a>
				{/if}
			</p>
		</div>
		</div>

		<div id="app-steps" class="col-lg-12 col-xs-12" align="center">
			<h2>{l s='É simples aderir à parceria PrestaBR e PagSeguro, veja:' mod='pagseguropro'}</h2>
			<ul class="list-inline">
				<li>
					<a href="{$module_dir|escape:'html':'UTF-8'}docs/Termo-de-Acordo-Comercial-PagSeguro.pdf" target="_blank"><img src="{$module_dir|escape:'html':'UTF-8'}img/passo-a-passo-01.jpg" class="img-responsive" /></a>
				</li>
				<li>
					<a href="https://github.com/pagseguro/prestashop" target="_blank"><img src="{$module_dir|escape:'html':'UTF-8'}img/passo-a-passo-02.jpg" class="img-responsive" /></a>
				</li>
				<li>
					<img src="{$module_dir|escape:'html':'UTF-8'}img/passo-a-passo-03.jpg" class="img-responsive" />
				</li>
				<li>
					<img src="{$module_dir|escape:'html':'UTF-8'}img/passo-a-passo-04.jpg" class="img-responsive" />
				</li>
				<li>
					<img src="{$module_dir|escape:'html':'UTF-8'}img/passo-a-passo-05.jpg" class="img-responsive" />
				</li>

			</ul>
		</div>

		<div class="col-lg-12 col-xs-12 info-app">
			{l s='1. Taxas válidas apenas para transações via aplicações "PrestaShop 1.6 14D" e "PrestaShop 1.6 30D". 2. Com o PagSeguro você pode' mod='pagseguropro'} <br />{l s='oferecer parcelamento sem acréscimo, para isso você paga uma taxa de 2.99% ao mês. 3. Ao clicar em "Autorizar" você aceita os termos da parceria.' mod='pagseguropro'}
		</div>

		<div class="col-lg-8 col-lg-offset-2 col-xs-12 col-xs-offset-0 info-app">
			<h2 class="app-faq">{l s='Dúvidas, perguntas?' mod='pagseguropro'}</h2>
			<div class="accordion" id="accordionApp">
			  <div class="card z-depth-0 bordered">
			    <div class="card-header" id="headingOne">
			      <h5 class="mb-0">
				<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseOne"
				  aria-expanded="false" aria-controls="collapseOne">
				  {l s='Posso assinar as duas parcerias, Recebimento em 14 e 30 dias?' mod='pagseguropro'}
				</button>
			      </h5>
			    </div>
			    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionApp">
			      <div class="card-body">
					{l s='Sim, você pode assinar as duas parcerias e especificar qual delas deve processar os pagamentos. Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "Receber em 14 dias" ou "Receber em 30 dias" e em seguida clique em Salvar.' mod='pagseguropro'}
			      </div>
			    </div>
			  </div>
			  <div class="card z-depth-0 bordered">
			    <div class="card-header" id="headingTwo">
			      <h5 class="mb-0">
				<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo"
				  aria-expanded="false" aria-controls="collapseTwo">
				  {l s='Eu não quero assinar parceria, como faço?' mod='pagseguropro'}
				</button>
			      </h5>
			    </div>
			    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionApp">
			      <div class="card-body">
					{l s='A parceria é opcional, você pode utilizar suas credenciais habituais (E-mail + Token) à qualquer momento. Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "Padrão (E-mail + Token)", preencha com o seu E-mail e Token e em seguida clique em Salvar.' mod='pagseguropro'}
			      </div>
			    </div>
			  </div>
			  <div class="card z-depth-0 bordered">
			    <div class="card-header" id="headingThree">
			      <h5 class="mb-0">
				<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree"
				  aria-expanded="false" aria-controls="collapseThree">
				  {l s='Eu já tenho minhas taxas e tarifas negociadas, como faço?' mod='pagseguropro'}
				</button>
			      </h5>
			    </div>
			    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionApp">
			      <div class="card-body">
					{l s='Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "Padrão (E-mail + Token)", preencha com o seu E-mail e Token e em seguida clique em Salvar.' mod='pagseguropro'}
			      </div>
			    </div>
			  </div>
			</div>
		</div>
	</div>
</div>

{literal} 
<script type="text/javascript">
	function showCredentials(campo) {
		if (campo == 'TOKEN'){
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
		<p style="text-align: center;font-size: 15px;">{l s='Após autorizar a sua aplicação, não esqueça de alterar o tipo de credencial configurada.' mod='pagseguropro'}</p>
		<p align="center" style="padding: 15px 0 0 0;">
			<a href="javascript:void(0);" class="btn btn-success" onclick="reloadPage();" style="text-align: center;background: #93e89a;padding: 10px 30px;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;color: #fff;font-weight: bold;text-decoration: none;">{l s='OK' mod='pagseguropro'}</a>
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
