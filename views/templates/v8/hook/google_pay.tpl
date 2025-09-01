{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
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

<div class="container-google clearfix mt-2 mb-2">
	<form id="google_pagbank" name="checkout" method="post" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" class="clearfix"
	onsubmit="return ps_googleCheckout(event);">
		<input type="hidden" name="pagbank_type" id="pagbank_type" value="google_pay"/>
		<input type="hidden" name="google_card_brand" id="google_card_brand" />
		<input type="hidden" name="google_card_bin" id="google_card_bin" />
		<input type="hidden" name="google_last_digits" id="google_last_digits" />
		<input type="hidden" name="google_signature" id="google_signature" />
		<input type="hidden" name="google_installment_value" id="google_installment_value" />
		<input type="hidden" name="google_installments" id="google_installments" />
		<input type="hidden" name="google_get_installments_fees" id="google_get_installments_fees" />
		<fieldset class="col-xs-12 col-sm-6 float-xs-left float-sm-left">
			<div class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="google_name">{l s='Titular do cartão:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input name="google_name" data-validate="isName" class="form-control" type="text" id="google_name"
						value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
						onblur="checkField(this.id);" required />
				</div>
			</div>
			<div class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="google_doc">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input id="google_doc" class="form-control" name="cpf_cnpj" {if $device == 'm'}type="tel"
						{else}type="text" 
						{/if} maxlength="18"
						onkeyup="this.value.length == 14 || this.value.length == 18 ? checkField(this.id) : ''"
						onkeydown="this.value.length > 14 ? mascara(this,cnpjmask): mascara(this,cpfmask)"
						onblur="checkField(this.id);" value="" size="30"
						placeholder="{l s='Somente números' d='Modules.PagBank.Shop'}" required />
				</div>
				<span class="col-sm-12 form-control-comment">{l s='(cpf do titular do cartão)' d='Modules.PagBank.Shop'}</span>
			</div>
			<div class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="google_phone">{l s='Telefone de contato:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input id="google_phone" class="form-control" name="telephone" {if $device == 'm'}type="tel"
						{else}type="text" 
						{/if} maxlength="15" onkeypress="mascara(this,telefone)"
						onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
						value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
				</div>
			</div>
			<p>
				{l s='Clique no botão abaixo para fazer o login e selecionar o seu cartão:' d='Modules.PagBank.Shop'}
			</p>
			<div id="show_btn_google"></div>
			{if ($discounts.discount_type > 0 && $discounts.discount_value > 0) && $discounts.google_pay}
				<div class="clearfix text-xs-center text-sm-center">
					<div class="alert alert-success text-xs-center text-sm-center col-xs-12 col-sm-12">
						<b>{l s='Pague em 1x e com desconto de' d='Modules.PagBank.Shop'}</b>
						<span class="discount">
							{if ($discounts.discount_type == 1)}
								{$discounts.discount_value}%
							{else}
								{if $ps_version >= '9.0.0'}
									{Context::getContext()->currentLocale->formatPrice($discounts.discount_value, $currency->iso_code)}
								{else}
									{Tools::displayPrice($discounts.discount_value|escape:'htmlall':'UTF-8')}
								{/if}
							{/if}
						</span>
					</div>
				</div>
			{/if}
			<div id="google_selected_card" class="alert alert-success" style="display:none;"></div>
			<div id="installments" class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<div class="col-xs-12 col-sm-12">
					<label class="text-xs-left text-sm-left col-xs-12 col-sm-12 nopadding"
						for="google_card_installment_qty">{l s='Parcelas:' d='Modules.PagBank.Shop'}</label>
					<select id="google_card_installment_qty" name="google_card_installment_qty"
						class="number form-control not_uniform" data-no-uniform="true"
						onchange="ps_setInstallment(this.id);" onblur="checkField(this.id);" required>
						<option value="">- Selecione o cartão -</option>
					</select>
				</div>
			</div>
		</fieldset>
		<div class="col-xs-12 col-sm-12 clearfix">
			<br />
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#google_address">
					{l s='Confirmar endereço do titular' d='Modules.PagBank.Shop'}
				</button>
			</div>
		</div>
		<fieldset class="col-xs-12 col-sm-12 collapse clearfix address nomargin" id="google_address">
			<div class="panel panel-info">
				<div class="panel-body">
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_postcode_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='CEP:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="postcode_invoice"
									onblur="checkField(this.id);"
									id="google_postcode_invoice" autocomplete="off" maxlength="9"
									value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_address_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Endereço:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="address_invoice" id="google_address_invoice" autocomplete="off"
									maxlength="80"
									value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_number_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Número:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="number_invoice"
									onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
									onblur="checkField(this.id);"
									id="google_number_invoice" autocomplete="off" maxlength="10"
									value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_other_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Complemento:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="other_invoice"
									id="google_other_invoice" autocomplete="off" maxlength="40"
									value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_address2_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Bairro:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="address2_invoice" id="google_address2_invoice" autocomplete="off"
									maxlength="60"
									value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_city_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Cidade:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="city_invoice" id="google_city_invoice" autocomplete="off" maxlength="60"
									value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="google_state_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Estado:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<select id="google_state_invoice" name="state_invoice"
									class="form-control not_uniform" data-no-uniform="true" 
									onchange="checkField(this.id);" required>
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
			</div>
		</fieldset>
		<div class="clear clearfix"></div>
	</form>
</div>