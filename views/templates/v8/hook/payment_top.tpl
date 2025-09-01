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

<script type="text/javascript" data-keepinline="true">
	var public_key = '{$public_key}';
</script>
{if isset($pagbank_msg) && $pagbank_msg != ''}
	{literal}
	<script type="text/javascript">
		window.onload = function() {
			$('#pagbank_msg').modal('show');
		};
	</script>
	{/literal}

	<div id="pagbank_msg" class="modal fade" style="display:none;" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' d='Modules.Pagbank.Shop'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger"><b>{$pagbank_msg nofilter}</b></p>
				</div>
			</div>
		</div>
	</div>
{/if}

<div id="pagbank_control_error" class="text-center nofloat-block col-xs-10 col-sm-9" style="display:none;"></div>
<div id="pagbank-container" class="container nopadding dev_{$device} opc-checkout">
	<input type="hidden" name="order_value" id="order_value" value="{$total}" />
	<input type="hidden" name="max_installments" id="max_installments" value="{$max_installments}" />
	<input type="hidden" name="installments_min_value" id="installments_min_value" value="{$installments_min_value}" />
	<input type="hidden" name="installments_min_type" id="installments_min_type" value="{$installments_min_type}" />
</div>
<div id="fancy_load" class="form-group clearfix row" align="center">
	<div id="pagbankproccess" style="display:none;" class="container clearfix">
		<div class="row">
			<div class="col-xs-3 col-sm-2 nopadding" id="pagbank_load" align="center">
				<img src="{$this_path}img/loading.gif" class="img-responsive" />
			</div>
			<div class="col-xs-6 col-sm-7 text-center" id="pagbankmsg">
				{l s='Por favor aguarde. Processando pagamento...' d='Modules.PagBank.Shop'}
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