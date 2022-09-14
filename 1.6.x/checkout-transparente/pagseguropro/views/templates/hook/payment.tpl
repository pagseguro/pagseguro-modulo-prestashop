{*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 *}

{if $page_name == 'order-opc' && isset($method) && $method}
	{literal}
	<script type="text/javascript">
		location.reload(true);
	</script>
	{/literal}
{else}
	<div id="pagseguro-container" class="container nopadding dev_{$device}{if isset($checkout) && $checkout != false && $checkout != 0} opc-checkout{/if}">
		<input type="hidden" name="valor_pedido" id="valor_pedido" value="{$total}" />
		<input type="hidden" name="msg_console" id="msg_console" value="{$msg_console}" />
		
		<ul class="nav nav-tabs" tabindex="-1">
			{if $cartao}
			<li id="cartao-tab" class="nopadding col-xs-12 col-sm-3 active">
				<a id="toggle-credito" class="active" data-toggle="tab" href="#pagseguro-credito">
					<i class="icon icon-credit-card fa fa-credit-card"></i>
					{l s='Cartão de Crédito' mod='pagseguropro'}
					<img src="{$this_path}img/logo_pagseguro_mini_mobile.png" class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" /> <i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
				</a>
			</li>
			{/if}
			{if $boleto}
			<li id="boleto-tab" class="nopadding col-xs-12 col-sm-3">
				<a id="toggle-boleto" data-toggle="tab" href="#pagseguro-boleto">
					<i class="icon icon-barcode fa fa-barcode"></i>
					{l s='Boleto' mod='pagseguropro'}
					<img src="{$this_path}img/logo_pagseguro_mini_mobile.png" class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" /> <i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
				</a>
			</li>
			{/if}
			{if $transf}
			<li id="debito-tab" class="nopadding col-xs-12 col-sm-3">
				<a id="toggle-debito" data-toggle="tab" href="#pagseguro-debito">
					<i class="icon icon-exchange fa fa-exchange"></i>
					{l s='Transferência' mod='pagseguropro'}
					<img src="{$this_path}img/logo_pagseguro_mini_mobile.png" class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" /> <i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
				</a>
			</li>
			{/if}
			<li class="nopadding col-xs-3 col-sm-2 pull-right">
				<img class="pagseguro-logo pull-right hidden-xs" src="{$this_path}img/logo_pagseguro_mini.png" style="max-height:32px" />
			</li>
		</ul>
		<div id="pagseguroContent" class="tab-content">
			<a href="#fancy_load" id="fancy_btn" style="display:none;"></a>
			<div id="pagseguroprocontrolaErro" class="text-center nofloat-block col-xs-10 col-sm-9" style="display:none;"></div>
			{if $cartao}
			<div class="tab-pane active in" id="pagseguro-credito" class="clearfix">
				{include file="$tpl_dir/cartao.tpl"}
			</div>
			{/if}
			{if $boleto}
			<div class="tab-pane" id="pagseguro-boleto" class="clearfix">
				{include file="$tpl_dir/boleto.tpl"}
			</div>
			{/if}
			{if $transf}
			<div class="tab-pane" id="pagseguro-debito" class="clearfix">
				{include file="$tpl_dir/transferencia.tpl"}
			</div>
			{/if}
		</div>
	</div>
	
	<div id="fancy_load" class="form-group clearfix row" align="center">
		<div id="pagseguroproproccess" style="height:auto; width:600px; max-width:100%; display:none;" class="container clearfix">
			<div class="row">
				<div class="col-xs-3 col-sm-2 nopadding" align="center">
					<img src="{$this_path}img/loading.gif" class="img-responsive" />
				</div>
				<div class="col-xs-6 col-sm-7 text-center" id="pagseguromsg">
					{l s='Por favor aguarde. Processando pagamento...' mod='pagseguropro'}
				</div>
				<div class="hidden-xs col-sm-3 nopadding-left" id="pagseguro_logo" align="center">
					<img src="{$this_path}img/logo_pagseguro.png" class="img-responsive" />
				</div>
				<div class="hidden-lg hidden-md hidden-sm col-xs-3 nopadding-left" id="pagseguro_logo" align="center">
					<img src="{$this_path}img/logo_pagseguro_mini_mobile.png" class="img-responsive" />
				</div>
			</div>
		</div>
	</div>	
	{literal}
	<script type="text/javascript">
		var urlImg = '{/literal}{$url_img}{literal}';
		var urlFuncoes = '{/literal}{$url_update}{literal}';
		{/literal}{if $msg_console == 1}{literal}
			var msg_console = 1;
		{/literal}{else}{literal}
			var msg_console = 0;
		{/literal}{/if}{literal}
	</script>
	{/literal}
{/if}
