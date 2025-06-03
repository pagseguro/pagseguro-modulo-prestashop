{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

{if $page_name == 'order-opc' && isset($method) && $method}
	{literal}
		<script type="text/javascript">
			location.reload(true);
		</script>
	{/literal}
{else}
	<div id="pagbank-container"
		class="container nopadding dev_{$device}{if isset($checkout) && $checkout != false && $checkout != 0} opc-checkout{/if}">
		<input type="hidden" name="order_value" id="order_value" value="{$total}" />
		<input type="hidden" name="max_installments" id="max_installments" value="{$max_installments}" />
		<input type="hidden" name="installments_min_value" id="installments_min_value" value="{$installments_min_value}" />
		<input type="hidden" name="installments_min_type" id="installments_min_type" value="{$installments_min_type}" />
		<input type="hidden" name="msg_console" id="msg_console" value="{$msg_console}" />

		<img title="{l s='PagBank' mod='pagbank'}" class="pagbank-logo pull-right hidden-xs"
			src="{$this_path}img/pagbank-logo-animado_35px.gif" alt="{l s='PagBank' mod='pagbank'}"
			ondrag="return false" onselec="return false" oncontextmenu="return false" /><br /><br />
		<ul class="nav nav-tabs horizontal" tabindex="-1">
			{if $payments.credit_card}
				<li id="credit-tab" class="nopadding col-xs-12 col-sm-3 active">
					<a class="active" data-toggle="tab" href="#pagbank-credit-card">
						<i class="icon icon-credit-card fa fa-credit-card"></i>
						{l s='Cartão de Crédito' mod='pagbank'}
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $discounts.discount_type > 0 && $discounts.discount_value > 0 && $discounts.credit_card}
							{if ($discounts.discount_type == 1)}
								(- {$discounts.discount_value}%)
							{else}
								(- {displayPrice price=($discounts.credit_card_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $payments.google_pay && $google_merchant_id|strlen >= 13}
				<li id="google-tab" class="nopadding col-xs-12 col-sm-3 {if (!$payments.credit_card)}active{/if}">
					<a class="{if (!$payments.credit_card && !$payments.bankslip && !$payments.pix && !$payments.wallet)}active{/if}" data-toggle="tab" href="#pagbank-google-pay">
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-xs" />
						{l s='Google Pay' mod='pagbank'}
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $discounts.discount_type > 0 && $discounts.discount_value > 0 && $discounts.google_pay}
							{if ($discounts.discount_type == 1)}
								(- {$discounts.discount_value}%)
							{else}
								(- {displayPrice price=($discounts.google_pay_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $payments.pix}
				<li id="pix-tab" class="nopadding col-xs-12 col-sm-2 {if (!$payments.credit_card && !$payments.google_pay)}active{/if}">
					<a class="{if (!$payments.credit_card && !$payments.bankslip)}active{/if}" data-toggle="tab" href="#pagbank-pix">
						<img src="{$this_path}img/pix-mini-blue.png" class="active" />
						<img src="{$this_path}img/pix-mini.png" />
						{l s='Pix' mod='pagbank'}
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $discounts.discount_type > 0 && $discounts.discount_value > 0 && $discounts.pix}
							{if ($discounts.discount_type == 1)}
								(- {$discounts.discount_value}%)
							{else}
								(- {displayPrice price=($discounts.pix_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $payments.bankslip}
				<li id="bankslip-tab" class="nopadding col-xs-12 col-sm-2 {if (!$payments.credit_card && !$payments.google_pay && !$payments.pix)}active{/if}">
					<a class="{if (!$payments.credit_card)}active{/if}" data-toggle="tab" href="#pagbank-bankslip">
						<i class="icon icon-barcode fa fa-barcode"></i>
						{l s='Boleto' mod='pagbank'}
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $discounts.discount_type > 0 && $discounts.discount_value > 0 && $discounts.bankslip}
							{if ($discounts.discount_type == 1)}
								(- {$discounts.discount_value}%)
							{else}
								(- {displayPrice price=($discounts.bankslip_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $payments.wallet}
				<li id="wallet-tab" class="nopadding col-xs-12 col-sm-3 {if (!$payments.credit_card && !$payments.google_pay && !$payments.pix && !$payments.bankslip)}active{/if}">
					<a class="{if (!$payments.credit_card && !$payments.bankslip && !$payments.pix)}active{/if}" data-toggle="tab" href="#pagbank-wallet">
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-xs" />
						{l s='Pagar com PagBank' mod='pagbank'}
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $discounts.discount_type > 0 && $discounts.discount_value > 0 && $discounts.wallet}
							{if ($discounts.discount_type == 1)}
								(- {$discounts.discount_value}%)
							{else}
								(- {displayPrice price=($discounts.wallet_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
		</ul>
		<div id="pagbank-content" class="tab-content">
			<a href="#fancy_load" id="fancy_btn" style="display:none;"></a>
			<div id="pagbank_control_error" class="text-center nofloat-block col-xs-10 col-sm-9" style="display:none;">
			</div>
			{if $payments.credit_card}
				<div class="tab-pane active in" id="pagbank-credit-card" class="clearfix">
					{include file="$tpl_dir/credit_card.tpl"}
				</div>
			{/if}
			{if $payments.google_pay && $google_merchant_id|strlen >= 13}
				<div class="tab-pane {if (!$payments.credit_card)}active in{/if}" id="pagbank-google-pay" class="clearfix">
					{include file="$tpl_dir/google_pay.tpl"}
				</div>
			{/if}
			{if $payments.pix}
				<div class="tab-pane {if (!$payments.credit_card && !$payments.google_pay)}active in{/if}" id="pagbank-pix" class="clearfix">
					{include file="$tpl_dir/pix.tpl"}
				</div>
			{/if}
			{if $payments.bankslip}
			<div class="tab-pane {if (!$payments.credit_card && !$payments.google_pay && !$payments.pix)}active in{/if}" id="pagbank-bankslip" class="clearfix">
					{include file="$tpl_dir/bankslip.tpl"}
				</div>
			{/if}
			{if $payments.wallet}
				<div class="tab-pane {if (!$payments.credit_card && !$payments.google_pay && !$payments.pix && !$payments.bankslip)}active in{/if}" id="pagbank-wallet" class="clearfix">
					{include file="$tpl_dir/wallet.tpl"}
				</div>
			{/if}
		</div>
	</div>

	<div id="fancy_load" class="form-group clearfix row" align="center">
		<div id="pagbankproccess" style="display:none;" class="container clearfix">
			<div class="row">
				<div class="col-xs-3 col-sm-2 nopadding" align="center">
					<img src="{$this_path}img/loading.gif" class="img-responsive" />
				</div>
				<div class="col-xs-6 col-sm-7 text-center" id="pagbankmsg">
					{l s='Por favor aguarde. Processando pagamento...' mod='pagbank'}
				</div>
				{if $device == 'd' || $device == 't'}
					<div class="col-sm-3 nopadding-left" id="pagbank_logo" align="center">
						<img src="{$this_path}img/pagbank-logo-animado_35px.gif" class="img-responsive" />
					</div>
				{else}
					<div class="col-xs-3 nopadding-left" id="pagbank_logo" align="center">
						<img src="{$this_path}img/logo_pagbank_mini_mobile.png" class="img-responsive" />
					</div>
				{/if}
			</div>
		</div>
	</div>
	{literal}
		<script type="text/javascript">
			var urlImg = '{/literal}{$url_img}{literal}';
			var functionUrl = '{/literal}{$url_update}{literal}';
			var this_path = '{/literal}{$this_path}{literal}';
			var shop_name = '{/literal}{$shop_name}{literal}';
			var discount_type = {/literal}{$discounts.discount_type}{literal};
			var discount_value = {/literal}{if $discounts.discount_value}{$discounts.discount_value}{else}0{/if}{literal};
			var discount_card = {/literal}{$discounts.credit_card|intval}{literal};
			var credit_card_value = {/literal}{$discounts.credit_card_value|floatval}{literal};
			var discount_bankslip = {/literal}{$discounts.bankslip|intval}{literal};
			var discount_pix = {/literal}{$discounts.pix|intval}{literal};
			var discount_wallet = {/literal}{$discounts.wallet|intval}{literal};
			var discount_google = {/literal}{$discounts.google_pay|intval}{literal};
			var google_pay_value = {/literal}{$discounts.google_pay_value|floatval}{literal};
			var account_id = '{/literal}{$account_id}{literal}';
			var payment_google_pay = '{/literal}{$payments.google_pay}{literal}';
			var google_merchant_id = '{/literal}{$google_merchant_id}{literal}';
			var google_environment = '{/literal}{$google_environment}{literal}';
			var msg_console = {/literal}{$msg_console|intval}{literal};
			var ps_version = '{/literal}{$ps_version}{literal}';
			var pagbank_version = '{/literal}{$pagbank_version}{literal}';
		</script>
	{/literal}
{/if}