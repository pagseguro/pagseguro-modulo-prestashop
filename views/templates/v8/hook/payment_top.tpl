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

<script type="text/javascript" data-keepinline="true">
	var public_key = '{$public_key}';
</script>
{if isset($pagbank_msg) && $pagbank_msg != ''}
	{literal}
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				jQuery('#pagbank_msg').modal('show');
				setTimeout(function() {}, 10000);
			});
		</script>
	{/literal}
	<div id="pagbank_msg" class="modal fade" style="display:none;" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' d='Modules.PagBank.Shop'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger">{$pagbank_msg|nl2br}</p>
				</div>
			</div>
		</div>
	</div>
{/if}

<div id="pagbank_control_error" class="text-center nofloat-block col-xs-10 col-sm-9" style="display:none;"></div>
<div id="pagbank-container" class="container nopadding dev_{$device} opc-checkout">
	<input type="hidden" name="order_value" id="order_value" value="{$total}" />
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
<form id="pagbank_success" class="hidden" name="checkout"
	action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" method="post" class="clearfix">
	<input type="hidden" id="pagbank_response" name="pagbank_response" value="" />
</form>
{literal}
	<script type="text/javascript">
		var urlImg = '{/literal}{$url_img}{literal}';
		var functionUrl = '{/literal}{$url_update}{literal}';
		var this_path = '{/literal}{$this_path}{literal}';
		var discount_type = {/literal}{$discounts.discount_type}{literal};
		var discount_value = {/literal}{if $discounts.discount_value}{$discounts.discount_value}{else}0{/if}{literal};
		var discount_card = {/literal}{$discounts.credit_card|intval}{literal};
		var credit_card_value = {/literal}{$discounts.credit_card_value|floatval}{literal};
		var discount_bankslip = {/literal}{$discounts.bankslip|intval}{literal};
		var discount_pix = {/literal}{$discounts.pix|intval}{literal};
		var msg_console = {/literal}{$msg_console|intval}{literal};
		var ps_version = '{/literal}{$ps_version}{literal}';
		var pagbank_version = '{/literal}{$pagbank_version}{literal}';
	</script>
{/literal}