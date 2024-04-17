{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Pix, Boleto e Cartão de Crédito
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2024 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2024 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

<!-- Header -->
<div id="pagbank_config" class="panel">
	<div class="row moduleconfig-header">
		<div class="col-xs-12 col-sm-3 text-center" align="center">
			<a class="pagbank" href="https://pagseguro.uol.com.br/" target="_blank"
				title="PagBank | Venda Online com Segurança!">
				<img src="{$module_dir|escape:'html':'UTF-8'}img/logo_pagbank.png" class="img-responsive" />
			</a>
		</div>
		<div class="documentacao col-xs-12 col-sm-6 text-center" align="center">
			<center><img src="{$module_dir|escape:'html':'UTF-8'}img/prestashop-logo.png" class="img-responsive" />
			</center>
		</div>
		<div class="col-xs-12 col-sm-3 text-center" align="center">
			<a class="prestabr" href="https://prestabr.com.br/" target="_blank"
				title="Desenvolvido por PrestaBR e-Commerce Solutions">
				<img src="//prestabr.com.br/logo_mini.png" class="img-responsive" alt="PrestaBR" />
			</a>
		</div>
		<div class="documentacao col-xs-12 col-sm-12 text-center" align="center">
			<h2>{l s='PagBank  - Checkout Transparente - v.' mod='pagbank'}{$module_version}</h2>
		</div>
	</div>
	<div class="row">
		<div class="links" align="center">
			<ul class="list list-inline">
				<li>
					<a class="btn btn-primary" href="https://github.com/pagseguro/pagseguro-modulo-prestashop"
						target="_blank">{l s='Documentação do Módulo' mod='pagbank'}</a>
				</li>
				<li>
					<a class="btn btn-primary" href="https://github.com/pagseguro/pagseguro-modulo-prestashop/issues/new/choose"
						target="_blank">{l s='Suporte Técnico' mod='pagbank'}</a>
				</li>
				<li>
					<button class="btn btn-primary collapsed" type="button" data-toggle="collapse"
						data-target="#collapseCron" aria-expanded="false" aria-controls="collapseCron">
						{l s='Tarefa Cron' mod='pagbank'}
					</button>
				</li>
				<li>
					<a class="btn btn-primary" href="{$transactions_link}"
						target="_blank">{l s='Minhas Transações' mod='pagbank'}</a>
				</li>
				<li>
					<a class="btn btn-primary" href="{$logs_link}" target="_blank">{l s='Meus Logs' mod='pagbank'}</a>
				</li>
			</ul>
		</div>
		<div id="collapseCron" class="collapse" data-parent="#accordionCron">
			<div class="info-cron">
				<p class="comment">
					{l s='Recomendamos que configure a Tarefa Cron para ser executada a cada 1x ao dia.' mod='pagbank'}
				</p>
				<h2>{l s='Pix' mod='pagbank'}</h2>
				<p>
					<b>{l s='URL:' mod='pagbank'}</b> {$callback_url}?action=cancelNotPaidPix&token={$token_cron}
				</p>
				<p class="comment">
					{l s='Recomendamos que configure a Tarefa Cron para ser executada a cada 1hr.' mod='pagbank'}
				</p>
				<h2>{l s='Boleto' mod='pagbank'}</h2>
				<p>
					<b>{l s='URL:' mod='pagbank'}</b> {$callback_url}?action=cancelNotPaidBankslip&token={$token_cron}
				</p>
				<p class="comment">
					{l s='Recomendamos que configure a Tarefa Cron para ser executara 1x ao dia.' mod='pagbank'}
				</p>
				<h2>{l s='Nota:' mod='pagbank'}</h2>
				<p>
					<b>{l s='-' mod='pagbank'}</b>
					{l s='O cancelamento do pedido via Tarefa Cron é útil para o seu gerenciamento de estoque, se o pedido não for pago dentro do prazo estipulado.' mod='pagbank'}<br />
					<b>{l s='-' mod='pagbank'}</b>
					{l s='Para configurar a Tarefa Cron entre em contato com o suporte técnico do seu servidor de hospedagem e informe as URLs acima.' mod='pagbank'}
				</p>
			</div>
		</div>
	</div>

	<div id="app-rates" class="row">
		<h2 class="app-bigtitle">{l s='Aproveite as condições diferenciadas do PagBank e aceite' mod='pagbank'} <br />
			{l s='pagamentos via' mod='pagbank'} {l s='Cartão de Crédito' mod='pagbank'}
			{l s='Boleto Bancário e Pix.' mod='pagbank'}</h2>
		<div class="allapps col-xs-12 col-sm-12">
			<div class="row">
				<div class="col-xs 12 col-sm-4">
					<h2 class="text-center">{l s='App D14' mod='pagbank'}</h2>
					<div class="card {if (isset($tokenD14) && $tokenD14 != false)}already-registered{/if}">
						<div class="card-body text-center">
							<div class="icon-box icon-box--primary">
								{l s='Cartão de Crédito - 14 Dias' mod='pagbank'}
							</div>
							{assign var="strAppInfoD14" value="{$app_info_d14.credit_tax}"}
							{assign var="splAppInfoD14" value=","|explode:$strAppInfoD14}
							<p class="text-muted">
								<i class="icon-check"></i> {$splAppInfoD14[0]}<br />
								<i class="icon-check"></i> {$splAppInfoD14[1]}<br />
								<i class="icon-check"></i> {$splAppInfoD14[2]}
							</p>
							<div class="icon-box icon-box--primary">
								{l s='Boleto Bancário - 2 dias' mod='pagbank'}
							</div>
							<p class="text-muted">
								<i class="icon-check"></i> {l s='Boleto:' mod='pagbank'} {$app_info_d14.bankslip_tax}
								<br />
							</p>
							<div class="icon-box icon-box--primary">
								{l s='Pix - Na hora' mod='pagbank'}
							</div>
							<p class="text-muted">
								<i class="icon-check"></i> {l s='Pix:' mod='pagbank'} {$app_info_d14.pix_tax} <br />
							</p>
							<p>
								{if (isset($tokenD14) && $tokenD14 != false)}
									<span class="btn btn-success signit"
										onclick="javascript:alert('App D14 já cadastrado!');">{l s='Já Cadastrado' mod='pagbank'}</span>
								{else}
									<a href="{$signin_connect_url}?response_type=code&client_id={$client_id.d14}&redirect_uri={$callback_url}&scope={$scope}&state={$new_user_state_d14}&code_challenge={$code_challenge_d14}&code_challenge_method=S256"
										onclick="return confirm('Tem certeza que deseja se cadastrar na opção App D14?');"
										class="btn btn-primary" target="_blank">
										Cadastrar
									</a>
								{/if}
							</p>
						</div>
					</div>
				</div>
				<div class="col-xs 12 col-sm-4">
					<h2 class="text-center">{l s='App D30' mod='pagbank'}</h2>
					<div class="card {if (isset($tokenD30) && $tokenD30 != false)}already-registered{/if}">
						<div class="card-body text-center">
							<div class="icon-box icon-box--primary">
								{l s='Cartão de Crédito - 30 Dias' mod='pagbank'}
							</div>
							{assign var="strAppInfoD30" value="{$app_info_d30.credit_tax}"}
							{assign var="splAppInfoD30" value=","|explode:$strAppInfoD30}
							<p class="text-muted">
								<i class="icon-check"></i> {$splAppInfoD30[0]}<br />
								<i class="icon-check"></i> {$splAppInfoD30[1]}<br />
								<i class="icon-check"></i> {$splAppInfoD30[2]}
							</p>
							<div class="icon-box icon-box--primary">
								{l s='Boleto Bancário - 2 dias' mod='pagbank'}
							</div>
							<p class="text-muted">
								<i class="icon-check"></i> {l s='Boleto:' mod='pagbank'} {$app_info_d30.bankslip_tax}
								<br />
							</p>
							<div class="icon-box icon-box--primary">
								{l s='Pix - Na hora' mod='pagbank'}
							</div>
							<p class="text-muted">
								<i class="icon-check"></i> {l s='Pix:' mod='pagbank'} {$app_info_d30.pix_tax} <br />
							</p>
							<p>
								{if (isset($tokenD30) && $tokenD30 != false)}
									<span class="btn btn-success signit"
										onclick="javascript:alert('App D30 já cadastrado!');">{l s='Já Cadastrado' mod='pagbank'}</span>
								{else}
									<a href="{$signin_connect_url}?response_type=code&client_id={$client_id.d30}&redirect_uri={$callback_url}&scope={$scope}&state={$new_user_state_d30}&code_challenge={$code_challenge_d30}&code_challenge_method=S256"
										onclick="return confirm('Tem certeza que deseja se cadastrar na opção App D30?');"
										class="btn btn-primary" target="_blank">
										Cadastrar
									</a>
								{/if}
							</p>
						</div>
					</div>
				</div>
				<div class="col-xs 12 col-sm-4">
					<h2 class="text-center">{l s='App Tax' mod='pagbank'}</h2>
					<div class="card {if (isset($tokenTax) && $tokenTax != false)}already-registered{/if}">
						<div class="card-body text-center">
							<div class="icon-box icon-box--primary">
								{l s='Taxa Negociada' mod='pagbank'}
							</div>
							<p class="text-muted">
								<i class="icon-check"></i>
								{l s='As taxas, tarifas e prazo de recebimento são negociados diretamente em sua conta por um gerente comercial do PagBank.' mod='pagbank'}<br />{l s='Entre em contato:' mod='pagbank'}<br /><a
									href="mailto:relacionamentoweb@pagseguro.com"
									target="_blank">relacionamentoweb@pagseguro.com</a>
							</p>
							<p>
								{if (isset($tokenTax) && $tokenTax != false)}
									<span class="btn btn-success signit"
										onclick="javascript:alert('App Tax já cadastrado!');">{l s='Já Cadastrado' mod='pagbank'}</span>
								{else}
									<a href="{$signin_connect_url}?response_type=code&client_id={$client_id.tax}&redirect_uri={$callback_url}&scope={$scope}&state={$new_user_state_tax}&code_challenge={$code_challenge_tax}&code_challenge_method=S256"
										onclick="return confirm('Tem certeza que deseja se cadastrar na opção App Tax?');"
										class="btn btn-primary" target="_blank">
										Cadastrar
									</a>
								{/if}
							</p>
						</div>
					</div>
				</div>
			</div>
			<div id="app-steps" class="col-xs-12 col-sm-12" align="center">
				<h2>{l s='É simples aderir as condições comerciais, veja:' mod='pagbank'}</h2>
				<ul class="col-xs-12 col-sm-6 list-inline">
					<li>
						<img src="{$module_dir|escape:'html':'UTF-8'}img/app-step-01.jpg" class="img-responsive" />
					</li>
					<li>
						<img src="{$module_dir|escape:'html':'UTF-8'}img/app-step-02.jpg" class="img-responsive" />
					</li>
					<li>
						<img src="{$module_dir|escape:'html':'UTF-8'}img/app-step-03.jpg" class="img-responsive" />
					</li>
					<p class="app-step-info">
						<b>{l s='Nota:' mod='pagbank'}</b>
						{l s='Caso você ainda não tenha uma conta PagBank, será preciso enviar documentação e aguardar a aprovação para realizar movimentações financeiras.' mod='pagbank'}
					</p>
				</ul>
				<div class="col-xs-12 col-sm-6 info-app pull-right">
					<div {if ($ps_version >= '1.7')}class="accordion ps8" {else}class="accordion ps16" {/if}
						id="accordionApp">
						<div class="card">
							<div class="card-header">
								<h5>
									<button class="btn btn-link collapsed" type="button" data-toggle="collapse"
										data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
										{l s='Posso aderir as duas condições comerciais, App D14 e App D30?' mod='pagbank'}
									</button>
								</h5>
							</div>
							<div id="collapseOne" class="collapse" data-parent="#accordionApp">
								<div class="card-body">
									{l s='Sim, você pode aderir as duas condições comerciais e especificar qual delas deve processar os pagamentos. Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "App D14" ou "App D30" e em seguida clique em Salvar.' mod='pagbank'}
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header">
								<h5>
									<button class="btn btn-link collapsed" type="button" data-toggle="collapse"
										data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
										{l s='Eu não quero essas condições comerciais, como faço?' mod='pagbank'}
									</button>
								</h5>
							</div>
							<div id="collapseTwo" class="collapse" data-parent="#accordionApp">
								<div class="card-body">
									{l s='As condições comerciais são opcionais, você pode aderir a opção "App Tax" a qualquer momento. Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "App Tax" e em seguida clique em Salvar.' mod='pagbank'}
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header">
								<h5>
									<button class="btn btn-link collapsed" type="button" data-toggle="collapse"
										data-target="#collapseThree" aria-expanded="false"
										aria-controls="collapseThree">
										{l s='Eu já tenho minhas taxas e tarifas negociadas, como faço?' mod='pagbank'}
									</button>
								</h5>
							</div>
							<div id="collapseThree" class="collapse" data-parent="#accordionApp">
								<div class="card-body">
									{l s='Nas configurações do módulo no campo "Tipo de Credenciais" selecione a opção "App Tax" e em seguida clique em Salvar.' mod='pagbank'}
								</div>
							</div>
						</div>
						<div class="card">
							<div class="card-header">
								<h5>
									<button class="btn btn-link collapsed" type="button" data-toggle="collapse"
										data-target="#collapseFour" aria-expanded="false"
										aria-controls="collapseFour">
										{l s='Posso me cadastrar mais de 1 vez no mesmo App?' mod='pagbank'}
									</button>
								</h5>
							</div>
							<div id="collapseFour" class="collapse" data-parent="#accordionApp">
								<div class="card-body">
									{l s='Sim, se por qualquer motivo você precisou reiniciar ou reinstalar o módulo para atualizar a loja, trocar de versão da PrestaShop, trocar de domínio, etc. Basta refazer o passo-a-passo de adesão no App desejado.' mod='pagbank'}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{if isset($appCode) && $appCode}
	<div id="hidden_div" style="display:none;">
		<div class="alert alert-info" align="center">
			<p style="text-align: center;font-size: 15px;">
				{l s='Após autorizar a sua aplicação, não esqueça de alterar o tipo de credencial configurada.' mod='pagbank'}
			</p>
			<p align="center" style="padding: 15px 0 0 0;">
				<a href="javascript:void(0);" class="btn btn-success" onclick="reloadPage();"
					style="text-align: center;background: #93e89a;padding: 10px 30px;-webkit-border-radius: 6px;-moz-border-radius: 6px;border-radius: 6px;color: #fff;font-weight: bold;text-decoration: none;">{l s='OK' mod='pagbank'}</a>
			</p>
		</div>
	</div>
	{literal}
		<script type="text/javascript">
			var openurl = '{/literal}{$link_app}{$appCode}{literal}';
			window.open(openurl, 'Gerar Código da Aplicação');

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

{literal}
	<script type="text/javascript">
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

		function showPaymentOptions(opt) {
			var value, switchItems;
			if (opt == 'credit_card') {
				value = document.querySelector('input[name="PAGBANK_CREDIT_CARD"]:checked').value;
				switchItems = ['PAGBANK_SAVE_CREDIT_CARD_on', 'PAGBANK_DISCOUNT_CREDIT_on'];
			} else if (opt == 'bankslip') {
				value = document.querySelector('input[name="PAGBANK_BANKSLIP"]:checked').value;
				switchItems = ['PAGBANK_DISCOUNT_BANKSLIP_on'];
			} else if (opt == 'pix') {
				value = document.querySelector('input[name="PAGBANK_PIX"]:checked').value;
				switchItems = ['PAGBANK_DISCOUNT_PIX_on'];
			}
			document.querySelectorAll('.' + opt + '_option').forEach(function(el) {
				if (value == 1) {
					el.parentElement.parentElement.style.display = 'block';
				} else {
					el.parentElement.parentElement.style.display = 'none';
				}
			});

			//Switch
			switchItems.forEach(function(itemId) {
				var elem = document.getElementById(itemId);
				if (value == 1) {
					elem.parentElement.parentElement.parentElement.style.display = 'block';
				} else {
					elem.parentElement.parentElement.parentElement.style.display = 'none';
				}
			});
		}
		$(document).ready(function() {
			showPaymentOptions('credit_card');
			showPaymentOptions('bankslip');
			showPaymentOptions('pix');

			document.getElementById('PAGBANK_CREDIT_CARD_on').parentElement.addEventListener('click', function(e) {
				showPaymentOptions('credit_card');
			});
			document.getElementById('PAGBANK_BANKSLIP_on').parentElement.addEventListener('click', function(e) {
				showPaymentOptions('bankslip');
			});
			document.getElementById('PAGBANK_PIX_on').parentElement.addEventListener('click', function(e) {
				showPaymentOptions('pix');
			});
		});
	</script>
{/literal}