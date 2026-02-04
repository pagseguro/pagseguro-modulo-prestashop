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
					<button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' d='Modules.Pagbank.Shop'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger"><b>{$pagbank_msg nofilter}</b></p>
				</div>
			</div>
		</div>
	</div>
{/if}
<input type="hidden" name="order_value" id="order_value" value="{$total}" />
<div id="fancy_load" class="clearfix">
	<div id="pagbankproccess" style="display:none;" class="container clearfix">
		<div class="col-xs-12 col-sm-12" id="pagbank_load" align="center">
			<img src="{$img_path}loading.gif" class="img-responsive" />
		</div>
		<div class="col-xs-12 col-sm-12 text-xs-center text-sm-center" id="pagbankmsg"></div>
	</div>
</div>
{literal}
	<script type="text/javascript">
		var pgb_public_key = '{/literal}{$public_key}{literal}';
		var pgb_max_installments = '{/literal}{$max_installments}{literal}';
		var pgb_installments_min_value = '{/literal}{$installments_min_value}{literal}';
		var pgb_installments_min_type = '{/literal}{$installments_min_type}{literal}';
		var pgb_function_url = '{/literal}{$url_update}{literal}';
		var pgb_img_path = '{/literal}{$img_path}{literal}';
		var pgb_shop_name = '{/literal}{$shop_name}{literal}';
		var pgb_discount_type = {/literal}{$active_discounts.discount_type}{literal};
		var pgb_discount_value = {/literal}{if $active_discounts.discount_value}{$active_discounts.discount_value}{else}0{/if}{literal};
		var pgb_discount_card = {/literal}{$active_discounts.credit_card|intval}{literal};
		var pgb_credit_card_value = {/literal}{$active_discounts.credit_card_value|floatval}{literal};
		var pgb_discount_bankslip = {/literal}{$active_discounts.bankslip|intval}{literal};
		var pgb_discount_pix = {/literal}{$active_discounts.pix|intval}{literal};
		var pgb_discount_wallet = {/literal}{$active_discounts.wallet|intval}{literal};
		var pgb_discount_google = {/literal}{$active_discounts.google_pay|intval}{literal};
		var pgb_google_pay_value = {/literal}{$active_discounts.google_pay_value|floatval}{literal};
		var pgb_account_id = '{/literal}{$account_id}{literal}';
		var pgb_payment_google_pay = '{/literal}{$active_payments.google_pay}{literal}';
		var pgb_google_merchant_id = '{/literal}{$google_merchant_id}{literal}';
		var pgb_google_environment = '{/literal}{$google_environment}{literal}';
		var pgb_msg_console = {/literal}{$msg_console|intval}{literal};
		var pgb_ps_version = '{/literal}{$ps_version}{literal}';
		var pgb_pagbank_version = '{/literal}{$pagbank_version}{literal}';
	</script>
{/literal}