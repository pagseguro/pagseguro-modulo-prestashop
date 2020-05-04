{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<div class="container-boleto checkout_pagseguro clearfix">
	<form id="boleto_pagseguropro" name="checkout" action="{$link->getModuleLink('pagseguropro', 'validation', [], true)|escape:'html'}" method="post" target="_top" onsubmit="return ps_finalizarBoleto();" class="clearfix">
		<input type="hidden" name="ps_tipo" id="ps_tipo" value="boleto" />
		<input type="hidden" name="ps_boleto_hash" id="ps_boleto_hash" />
		<fieldset class="pagamento col-xs-12">
			<input type="hidden" id="payment_type" name="payment_type" value="BankSlip">
			<div class="clearfix row" align="center">
				<div class="col-xs-12 payments nofloat" style="float:none;">
					<img title="Boleto Bancário" src="{$this_path}views/img/boleto.png" alt="{l s='Boleto Bancário' d='Modules.PagSeguroPro.Shop'}" ondrag="return false" onselec="return false" oncontextmenu="return false" />
				</div>
				<div class="paymentsb col-xs-12 clearfix nofloat" style="float:none; margin:0 auto;">
					<div class="pagseguroprocontrolaErro text-center" style="display:none;float:none; text-align:center; margin:0 auto; width:200px;"></div>
					<br>
				</div>
				<div class="form-group clearfix text-center nofloat" align="center">
					<label class="text-center col-xs-12 nofloat" for="boleto_doc">{l s='Por favor, informe o CPF/CNPJ:' d='Modules.PagSeguroPro.Shop'}</label>
					<div class="input-group col-xs-11 col-sm-9 col-md-8 col-lg-7 nofloat">
						<input id="boleto_doc" class="form-control" name="boleto_doc" {if $device == 'm'}type="tel"{else}type="text"{/if} maxlength="18" onblur="verifica('boleto_doc');$('#card_doc, #transf_doc').val(this.value);" value="{if (isset($cpf) && $cpf)}{$cpf}{/if}" size="30" placeholder="{l s='Somente números' d='Modules.PagSeguroPro.Shop'}" />
					</div>
				</div>
				<div class="form-group clearfix text-center nofloat" align="center">
					<label class="text-center col-xs-12 nofloat" for="boleto_phone">{l s='Telefone de contato:' d='Modules.PagSeguroPro.Shop'}</label>
					<div class="input-group col-xs-11 col-sm-9 col-md-8 col-lg-7 nofloat">
						<input id="boleto_phone" class="form-control" name="boleto_phone" {if $device == 'm'}type="tel"{else}type="text"{/if} maxlength="15" onkeypress="mascara(this,telefone)" onblur="validarTel('boleto_phone');mascara(this,telefone);$('#card_phone, #transf_phone').val(this.value);" value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
					</div>
				</div>
				<div class="text-center col-xs-10 col-sm-8 alert alert-warning nofloat" align="center">
					<strong>{l s='Após a confirmação do pedido, lembre-se de quitar o boleto o mais rápido possível.'  d='Modules.PagSeguroPro.Shop'}</strong>
				</div>
			</div>
		</fieldset>
	</form>
	<div class="clear clearfix"><br></div>
</div>
