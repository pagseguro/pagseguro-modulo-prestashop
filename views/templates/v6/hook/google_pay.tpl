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

<div class="container-google clearfix">
	<div id="pagbank_google_error" class="col-xs-10 col-sm-10 col-lg-9 text-center nofloat-block" style="display:none;"></div>
	<form id="google_pagbank" method="post" target="_top" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" 
	class="clearfix">
		<input type="hidden" name="payment_type" id="payment_type" value="google_pay"/>
		<input type="hidden" name="recaptcha_google_pay" id="recaptcha_google_pay" />
		<input type="hidden" name="card_value_pagbank" id="card_value_pagbank" value="{$card_value_pagbank}" />
		<input type="hidden" name="google_card_brand" id="google_card_brand" />
		<input type="hidden" name="google_card_bin" id="google_card_bin" />
		<input type="hidden" name="google_signature" id="google_signature" />
		<input type="hidden" name="google_installments" id="google_installments" />
		<input type="hidden" name="google_get_installments_fees" id="google_get_installments_fees" />
		<div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
			<div class="form-group">
				<label for="google_name">{l s='Titular do cartão:' mod='pagbank'}</label>
				<input id="google_name" class="form-control" name="google_name" type="text" data-validate="isName"
					value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
					onblur="psValidateGoogle();" required />
			</div>
			<div class="form-group">
				<label for="google_doc">{l s='CPF/CNPJ:' mod='pagbank'}</label>
				<input id="google_doc" class="form-control" name="cpf_cnpj" type="text" maxlength="18"
					onkeydown="this.value.length > 14 ? mascara(this,cnpjmask) : mascara(this,cpfmask); this.value = this.value.toUpperCase();"
					onblur="psValidateGoogle();" value="" size="18" required />
				<span class="form_info">{l s='(cpf/cnpj do titular do cartão)' mod='pagbank'}</span>
			</div>
			<div class="form-group">
				<label for="google_phone">{l s='Telefone de contato:' mod='pagbank'}</label>
				<input id="google_phone" class="form-control" name="telephone" type="text" inputmode="numeric" pattern="[0-9]*" 
					maxlength="15" onkeypress="mascara(this,telefone)"
					onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
					value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
			</div>
			<p>
				<br /><b>{l s='Clique no botão abaixo para fazer o login e selecionar o seu cartão:' mod='pagbank'}</b>
			</p>
			<div id="show_btn_google"></div>
			{if ($active_discounts.discount_type > 0 && $active_discounts.discount_value > 0) && $active_discounts.google_pay}
				<div class="clearfix">
					<div class="col-xs-12 col-sm-12 col-lg-12 alert alert-success text-center">
						<b>{l s='Pague em 1x e com desconto de' mod='pagbank'}</b>
						<span class="discount">
							{if ($active_discounts.discount_type == 1)}
								{$active_discounts.discount_value}%
							{else}
								{displayPrice price=$active_discounts.discount_value currency=$currency->id}
							{/if}
						</span>
					</div>
				</div>
			{/if}
			<div id="google_selected_card" class="alert alert-success" style="display:none;"></div>
			<div id="installments" class="form-group">
				<label for="google_card_installment_qty">{l s='Parcelas:' mod='pagbank'}</label>
				<select id="google_card_installment_qty" class="number form-control" name="google_card_installment_qty"
					data-no-uniform="true" onchange="psSetInstallment(this.id);" onblur="psValidateGoogle();" required>
					<option value="">- Selecione o cartão -</option>
				</select>
			</div>
		</div>
		<div class="form-group clearfix col-xs-12 col-sm-12 col-lg-12">
			<br />
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#google_address">
					{l s='Confirmar endereço do titular' mod='pagbank'}
				</button>
			</div>
		</div>
		<div class="form-group clearfix col-xs-12 col-sm-12 col-lg-12 collapse" id="google_address">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
					<div class="form-group">
						<label for="google_postcode_invoice">{l s='CEP:' mod='pagbank'}</label>
						<input id="google_postcode_invoice" class="form-control" name="postcode_invoice" type="text"
							onblur="psValidateGoogle();" autocomplete="off" maxlength="9"
							value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="google_address_invoice">{l s='Endereço:' mod='pagbank'}</label>
						<input id="google_address_invoice" class="form-control" name="address_invoice" type="text"
							onblur="psValidateGoogle();" autocomplete="off" maxlength="80"
							value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="google_number_invoice">{l s='Número:' mod='pagbank'}</label>
						<input id="google_number_invoice" class="form-control" name="number_invoice" type="text"
							onblur="psValidateGoogle();" autocomplete="off" maxlength="10"
							value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
					</div>
					<div class="form-group">
						<label for="google_other_invoice">{l s='Complemento:' mod='pagbank'}</label>
						<input id="google_other_invoice" class="form-control" name="other_invoice" type="text"
							autocomplete="off" maxlength="40"
							value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-lg-6 pull-right">
					<div class="form-group">
						<label for="google_address2_invoice">{l s='Bairro:' mod='pagbank'}</label>
						<input id="google_address2_invoice" class="form-control" name="address2_invoice" type="text"
							onblur="psValidateGoogle();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="google_city_invoice">{l s='Cidade:' mod='pagbank'}</label>
						<input id="google_city_invoice" class="form-control" name="city_invoice" type="text"
							onblur="psValidateGoogle();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
					</div>
					<div class="form-group">
						<label for="google_state_invoice">{l s='Estado:' mod='pagbank'}</label>
						<select id="google_state_invoice" class="form-control" name="state_invoice"
							data-no-uniform="true" onchange="psValidateGoogle();" required>
							<option value=""> -- </option>
							{foreach from=$states item=state name=uf}
								<option value="{$state.iso_code}"
									{if (isset($address_invoice->id_state) && $address_invoice->id_state == $state.id_state)}selected="selected"
									{/if}>
									{$state.iso_code}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="clear clearfix"></div>
		<p class="cart_navigation clearfix col-xs-12 col-sm-12 col-lg-12">
			<button id="submitGoogle" type="button" name="submitGoogle" class="btn btn-success btn-lg hideOnSubmit pull-right">
				{l s='Pagar' mod='pagbank'} &nbsp;<i class="icon icon-check fa fa-check"></i>
			</button>
		</p>
	</form>
</div>