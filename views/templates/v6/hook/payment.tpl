{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2026 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2026 PagBank - https://pagbank.com.br
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
	<div id="pagbank-container" class="container nopadding {if isset($checkout) && $checkout != false && $checkout != 0} opc-checkout{/if}">
		{if $device != 'm'}
			<img title="{l s='PagBank' mod='pagbank'}" class="pagbank-logo pull-right"
				src="{$img_path}pagbank-logo-animado_35px.gif" alt="{l s='PagBank' mod='pagbank'}"
				ondrag="return false" onselec="return false" oncontextmenu="return false" /><br /><br />
		{/if}
		<ul class="nav nav-tabs horizontal" tabindex="-1">
			{if $active_payments.credit_card}
				<li id="credit-tab" class="nopadding col-xs-12 col-sm-3 active">
					<a class="active" data-toggle="tab" href="#pagbank-credit-card">
						<i class="icon icon-credit-card fa fa-credit-card"></i>
						{l s='Cartão de Crédito' mod='pagbank'}
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $active_discounts.discount_type > 0 && $active_discounts.discount_value > 0 && $active_discounts.credit_card}
							{if ($active_discounts.discount_type == 1)}
								(- {$active_discounts.discount_value}%)
							{else}
								(- {displayPrice price=($active_discounts.credit_card_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $active_payments.google_pay && $google_merchant_id|strlen >= 13}
				<li id="google-tab" class="nopadding col-xs-12 col-sm-3 {if (!$active_payments.credit_card)}active{/if}">
					<a class="{if (!$active_payments.credit_card && !$active_payments.bankslip && !$active_payments.pix && !$active_payments.wallet)}active{/if}" data-toggle="tab" href="#pagbank-google-pay">
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-xs" />
						{l s='Google Pay' mod='pagbank'}
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $active_discounts.discount_type > 0 && $active_discounts.discount_value > 0 && $active_discounts.google_pay}
							{if ($active_discounts.discount_type == 1)}
								(- {$active_discounts.discount_value}%)
							{else}
								(- {displayPrice price=($active_discounts.google_pay_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $active_payments.pix}
				<li id="pix-tab" class="nopadding col-xs-12 col-sm-2 {if (!$active_payments.credit_card && !$active_payments.google_pay)}active{/if}">
					<a class="{if (!$active_payments.credit_card && !$active_payments.bankslip)}active{/if}" data-toggle="tab" href="#pagbank-pix">
						<img src="{$img_path}pix-mini-blue.png" class="active" />
						<img src="{$img_path}pix-mini.png" />
						{l s='Pix' mod='pagbank'}
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $active_discounts.discount_type > 0 && $active_discounts.discount_value > 0 && $active_discounts.pix}
							{if ($active_discounts.discount_type == 1)}
								(- {$active_discounts.discount_value}%)
							{else}
								(- {displayPrice price=($active_discounts.pix_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $active_payments.bankslip}
				<li id="bankslip-tab" class="nopadding col-xs-12 col-sm-2 {if (!$active_payments.credit_card && !$active_payments.google_pay && !$active_payments.pix)}active{/if}">
					<a class="{if (!$active_payments.credit_card)}active{/if}" data-toggle="tab" href="#pagbank-bankslip">
						<i class="icon icon-barcode fa fa-barcode"></i>
						{l s='Boleto' mod='pagbank'}
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $active_discounts.discount_type > 0 && $active_discounts.discount_value > 0 && $active_discounts.bankslip}
							{if ($active_discounts.discount_type == 1)}
								(- {$active_discounts.discount_value}%)
							{else}
								(- {displayPrice price=($active_discounts.bankslip_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
			{if $active_payments.wallet}
				<li id="wallet-tab" class="nopadding col-xs-12 col-sm-3 {if (!$active_payments.credit_card && !$active_payments.google_pay && !$active_payments.pix && !$active_payments.bankslip)}active{/if}">
					<a class="{if (!$active_payments.credit_card && !$active_payments.bankslip && !$active_payments.pix)}active{/if}" data-toggle="tab" href="#pagbank-wallet">
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-xs" />
						{l s='Pagar com PagBank' mod='pagbank'}
						<img src="{$img_path}logo_pagbank_mini_mobile.png"
							class="logo-pg-mini pull-left hidden-lg hidden-md hidden-sm" />
						{if $active_discounts.discount_type > 0 && $active_discounts.discount_value > 0 && $active_discounts.wallet}
							{if ($active_discounts.discount_type == 1)}
								(- {$active_discounts.discount_value}%)
							{else}
								(- {displayPrice price=($active_discounts.wallet_value) currency=$currency->id})
							{/if}
						{/if}
						<i class="icon icon-check fa fa-check pull-right hidden-lg hidden-md hidden-sm"></i>
					</a>
				</li>
			{/if}
		</ul>
		<div id="pagbank-content" class="tab-content">
			<a href="#fancy_load" id="fancy_btn" style="display:none;"></a>
			{if $active_payments.credit_card}
				<div class="tab-pane active in" id="pagbank-credit-card" class="clearfix">
					{include file="$tpl_dir/credit_card.tpl"}
				</div>
			{/if}
			{if $active_payments.google_pay && $google_merchant_id|strlen >= 13}
				<div class="tab-pane {if (!$active_payments.credit_card)}active in{/if}" id="pagbank-google-pay" class="clearfix">
					{include file="$tpl_dir/google_pay.tpl"}
				</div>
			{/if}
			{if $active_payments.pix}
				<div class="tab-pane {if (!$active_payments.credit_card && !$active_payments.google_pay)}active in{/if}" id="pagbank-pix" class="clearfix">
					{include file="$tpl_dir/pix.tpl"}
				</div>
			{/if}
			{if $active_payments.bankslip}
			<div class="tab-pane {if (!$active_payments.credit_card && !$active_payments.google_pay && !$active_payments.pix)}active in{/if}" id="pagbank-bankslip" class="clearfix">
					{include file="$tpl_dir/bankslip.tpl"}
				</div>
			{/if}
			{if $active_payments.wallet}
				<div class="tab-pane {if (!$active_payments.credit_card && !$active_payments.google_pay && !$active_payments.pix && !$active_payments.bankslip)}active in{/if}" id="pagbank-wallet" class="clearfix">
					{include file="$tpl_dir/wallet.tpl"}
				</div>
			{/if}
		</div>
	</div>
{/if}