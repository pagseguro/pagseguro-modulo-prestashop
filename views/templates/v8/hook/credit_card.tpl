{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto, Pix e super app PagBank
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

<div class="container-card clearfix mt-2 mb-2">
	<form id="card_pagbank" name="checkout" method="post" class="clearfix" onsubmit="return ps_cardCheckout(event);">
		<input type="hidden" name="ps_card_brand" id="ps_card_brand" value="" />
		<input type="hidden" name="ps_card_token_id" id="ps_card_token_id" value="" />
		<input type="hidden" name="ps_card_installment_value" id="ps_card_installment_value" />
		<input type="hidden" name="ps_card_installments" id="ps_card_installments" />
		<input type="hidden" name="ps_max_installments" id="ps_max_installments" value="{$ps_max_installments}" />
		<input type="hidden" name="ps_installments_min_value" id="ps_installments_min_value" value="{$ps_installments_min_value}" />
		<input type="hidden" name="ps_installments_min_type" id="ps_installments_min_type" value="{$ps_installments_min_type}" />
		<input type="hidden" name="get_installments_fees" id="get_installments_fees" />
		<input type="hidden" name="saved_card" id="saved_card" value="0" />
		<div id="card_show" class="col-xs-12 col-sm-6 float-xs-right float-sm-right nopadding-left" align="center">
			<div id="card_wrapper" class="nofloat">
				<div id="card_container">
					<div id="number_card" class="card-number"></div>
					<div class="card-name"></div>
					<div class="card-expiry"><span class="card-expiry-month"></span> / <span class="card-expiry-year"></span></div>
					<div class="card-brand"></div>
					<span class="card-cvv"></span>
				</div>
			</div>
		</div>
		<fieldset class="col-xs-12 col-sm-6 float-xs-left float-sm-left">
			<div class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="card_name">{l s='Titular do cartão:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input name="card_name" data-validate="isName" class="form-control" type="text" id="card_name"
						value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
						onblur="sendToCard(this.id, 'card-name');checkField(this.id);" required />
				</div>
			</div>
			<div class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="card_doc">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input id="card_doc" class="form-control" name="cpf_cnpj" {if $device == 'm'}type="tel"
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
					for="card_phone">{l s='Telefone de contato:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input id="card_phone" class="form-control" name="telephone" {if $device == 'm'}type="tel"
						{else}type="text" 
						{/if} maxlength="15" onkeypress="mascara(this,telefone)"
						onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
						value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
				</div>
			</div>
			{if ($save_credit_card && isset($customer_token) && is_array($customer_token))}
				<div id="saved_cards" class="card text-white clearfix">
					<div class="card-header bg-primary">
						<h3 class="card-title">{l s='Deseja pagar com um dos seus cartões salvos?' d='Modules.PagBank.Shop'}
						</h3>
					</div>
					<div class="card-body">
						<ul class="list">
							{foreach from=$customer_token key=t item=card}
								<li class="checkbox">
									<label for="token_{$card.id_customer_token}" class="float-xs-left">
										<input id="token_{$card.id_customer_token}" type="checkbox" name="check_token"
											value="{$card.id_customer_token}" class="checkbox-inline check_token"
											data-name="{$card.card_name}" data-brand="{$card.card_brand}"
											data-firstdigits="{$card.card_first_digits}"
											data-lastdigits="{$card.card_last_digits}" data-month="{$card.card_month}"
											data-year="{$card.card_year}" />
										<b class="brand text-uppercase">{$card.card_brand}</b>
										-
										<b class="last_digits">{l s='Final:' d='Modules.PagBank.Shop'} </b>
										<span class="text-uppercase">{$card.card_last_digits}</span>
									</label>
									<i class="float-xs-right float-sm-right cursor-pointer material-icons"
										onclick="deleteCustomerToken({$card.id_customer_token})">
										delete_forever
									</i>
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
				<div id="selected_card_token" class="alert alert-success" style="display:none;"></div>
			{/if}
			<div class="form-group clearfix row text-xs-left text-sm-left card_data" align="center">
				<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
					for="card_number">{l s='Número do cartão:' d='Modules.PagBank.Shop'}</label>
				<div class="input-group col-xs-12 col-sm-12">
					<input id="card_number" class="form-control" name="card_number" 
						{if $device == 'm'}type="tel" {else}type="text" {/if} maxlength="16" min="0" 
						onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
						autocomplete="off" onpaste="return false" size="16" value="" />
					{if ($ps_version >= '8.0')}
						<div>
							<span class="input-group-addon" id="credit-icon">
								<i class="icon icon-credit-card fa fa-credit-card material-icons"></i>
							</span>
						</div>
					{else}
						<div id="credit-icon" class="input-group-addon">
							<i class="icon icon-credit-card fa fa-credit-card material-icons"></i>
						</div>
					{/if}
				</div>
			</div>
			<div class="form-group clearfix row text-xs-left text-sm-left">
				<div class="col-xs-12 col-sm-8 nopadding card_data">
					<label
						class="col-xs-12 text-xs-left text-sm-left">{l s='Validade:' d='Modules.PagBank.Shop'}</label>
					<div class="text-xs-right text-sm-right col-xs-5 col-sm-5 nopadding-right">
						{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
						<select id="card_month" name="card_month" class="number form-control not_uniform"
							data-no-uniform="true"
							onchange="sendToCard(this.id, 'card-expiry-month');checkField(this.id);">
							<option value=""> Mês </option>
							{foreach from=$exp_months key=k item=month}
								<option value="{$month}">{$month}&nbsp;</option>
							{/foreach}
						</select>
					</div>
					<div class="text-xs-center text-sm-center col-xs-1 col-sm-1 nopadding">/</div>
					<div class="text-xs-right text-sm-right col-xs-5 col-sm-5 nopadding-left">
						<select id="card_year" name="card_year" class="number form-control not_uniform"
							data-no-uniform="true"
							onchange="sendToCard(this.id, 'card-expiry-year');checkField(this.id);"
							onblur="sendToCard(this.id, 'card-expiry-year');">
							<option value=""> Ano </option>
							{assign var=this_year value={$smarty.now|date_format:"%Y"}}
							{for $ano=$this_year to $this_year+15}
								<option value="{$ano}">{$ano|substr:-2}</option>
							{/for}
						</select>
					</div>
				</div>
				<div class="form-group col-xs-4 col-sm-4 cvv nopadding text-xs-left text-sm-left card_data">
					<label class="text-xs-left text-sm-left col-xs-12 col-sm-12"
						for="card_cvv">{l s='CVV:' d='Modules.PagBank.Shop'}</label>
					<div class="input-group col-xs-12 col-sm-12">
						<input id="card_cvv" class="form-control" name="card_cvv" maxlength="4"
							{if $device == 'm'}type="tel" {else}type="text" {/if} min="0"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							onpaste="return false" autocomplete="off" value="" onfocus="toggleCardBack('add');"
							onblur="checkCVV();toggleCardBack('remove');checkField(this.id);"
							onkeyup="sendToCard(this.id, 'card-cvv');" size="20" />
					</div>
				</div>
			</div>
			{if ($save_credit_card)}
				<div class="form-group clearfix text-xs-left text-sm-left checkbox card_data">
					<div class="col-xs-12 col-sm-12 inner">
						<label for="ps_save_customer_card">
							<input id="ps_save_customer_card" type="checkbox" name="ps_save_customer_card" value="1"
								class="form-checkbox" />
							<b>{l s='Salvar este cartão?' d='Modules.PagBank.Shop'}</b>
							<a href="#save-card-faq" class="fancy-button"><img src="{$this_path}img/faq.png"
									title="{l s='Clique e saiba mais.' d='Modules.PagBank.Shop'}" /></a>
						</label>
					</div>
				</div>
			{/if}
			{if ($discounts.discount_type > 0 && $discounts.discount_value > 0) && $discounts.credit_card}
				<div class="clearfix text-xs-center text-sm-center">
					<div class="alert alert-success text-xs-center text-sm-center col-xs-12 col-sm-12">
						<b>{l s='Pague em 1x e com desconto de' d='Modules.PagBank.Shop'}</b>
						<span class="discount">
							{if ($discounts.discount_type == 1)}
								{$discounts.discount_value}%
							{else}
								{Tools::displayPrice($discounts.discount_value|escape:'htmlall':'UTF-8')}
							{/if}
						</span>
					</div>
				</div>
			{/if}
			<div id="installments" class="form-group clearfix row text-xs-left text-sm-left" align="center">
				<div class="col-xs-12 col-sm-12">
					<label class="text-xs-left text-sm-left col-xs-12 col-sm-12 nopadding"
						for="card_installment_qty">{l s='Parcelas:' d='Modules.PagBank.Shop'}</label>
					<select id="card_installment_qty" name="card_installment_qty"
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
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ps_card_address">
					{l s='Confirmar endereço do titular' d='Modules.PagBank.Shop'}
				</button>
			</div>
		</div>
		<fieldset class="col-xs-12 col-sm-12 collapse clearfix address" id="ps_card_address">
			<div class="panel panel-info">
				<div class="panel-body">
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_postcode_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='CEP:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_postcode_invoice"
									onblur="checkField(this.id);"
									id="ps_card_postcode_invoice" autocomplete="off" maxlength="9"
									value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_address_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Endereço:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_address_invoice" id="ps_card_address_invoice" autocomplete="off"
									maxlength="80"
									value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_number_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Número:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_number_invoice"
									onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
									onblur="checkField(this.id);"
									id="ps_card_number_invoice" autocomplete="off" maxlength="10"
									value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_other_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Complemento:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_other_invoice"
									id="ps_card_other_invoice" autocomplete="off" maxlength="40"
									value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_address2_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Bairro:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_address2_invoice" id="ps_card_address2_invoice" autocomplete="off"
									maxlength="60"
									value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_city_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Cidade:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_city_invoice" id="ps_card_city_invoice" autocomplete="off" maxlength="60"
									value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_card_state_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Estado:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<select id="ps_card_state_invoice" name="ps_state_invoice"
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
	<div id="save-card-faq" style="display:none;">
		<img src="{$this_path}img/saved-card-faq.jpg"
			title="{l s='Salvar este cartão para futuras compras?' d='Modules.PagBank.Shop'}" />
	</div>
</div>