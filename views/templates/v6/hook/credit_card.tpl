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

<div class="container-card clearfix">
	<div id="pagbank_card_error" class="col-xs-10 col-sm-9 text-center nofloat-block" style="display:none;"></div>
	<form id="card_pagbank" name="checkout" method="post" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" target="_top"
	onsubmit="showLoading();" class="clearfix">
		<input type="hidden" name="pagbank_type" id="pagbank_type" value="credit_card"/>
		<input type="hidden" name="card_brand" id="card_brand" />
		<input type="hidden" name="card_bin" id="card_bin" />
		<input type="hidden" name="card_token_id" id="card_token_id" />
		<input type="hidden" name="encrypted_card" id="encrypted_card" />
		<input type="hidden" name="card_installment_value" id="card_installment_value" />
		<input type="hidden" name="card_installments" id="card_installments" />
		<input type="hidden" name="get_installments_fees" id="get_installments_fees" />
		<input type="hidden" name="saved_card" id="saved_card" value="0" />
		<div id="card_show" class="col-xs-12 col-sm-6 pull-right nopadding-left" align="center">
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
		<div class="col-xs-12 col-sm-6 pull-left">
			<div class="form-group">
				<label for="card_name">{l s='Titular do cartão:' mod='pagbank'}</label>
				<input id="card_name" class="form-control" name="card_name" type="text" data-validate="isName"
					value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
					onblur="sendToCard(this.id, 'card-name');checkField(this.id);" required />
			</div>
			<div class="form-group">
				<label for="card_doc">{l s='CPF/CNPJ:' mod='pagbank'}</label>
				<input id="card_doc" class="form-control" name="cpf_cnpj" type="text" maxlength="18"
					onkeyup="this.value.length == 14 || this.value.length == 18 ? checkField(this.id) : ''; this.value = this.value.toUpperCase();"
					onkeydown="this.value.length > 14 ? mascara(this,cnpjmask): mascara(this,cpfmask)"
					onblur="checkField(this.id);" value="" size="18" required />
				<span class="form_info">{l s='(cpf/cnpj do titular do cartão)' mod='pagbank'}</span>
			</div>
			<div class="form-group">
				<label for="card_phone">{l s='Telefone de contato:' mod='pagbank'}</label>
				<input id="card_phone" class="form-control" name="telephone" {if $device == 'm'}type="tel"{else}type="text"{/if} 
					maxlength="15" onkeypress="mascara(this,telefone)"
					onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
					value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
			</div>
			{if ($save_credit_card && isset($customer_token) && is_array($customer_token))}
				<div id="saved_cards" class="panel panel-primary clearfix">
					<div class="panel-heading">
						{l s='Deseja pagar com um dos seus cartões salvos?' mod='pagbank'}
					</div>
					<div class="panel-body">
						<ul class="list">
							{foreach from=$customer_token key=t item=card}
								<li class="radiobox">
									<label for="token_{$card.id_customer_token}">
										<input id="token_{$card.id_customer_token}" class="form-control check_token" 
											name="check_token" type="radio" value="{$card.id_customer_token}"
											data-name="{$card.card_name}" data-brand="{$card.card_brand}"
											data-firstdigits="{$card.card_first_digits}"
											data-lastdigits="{$card.card_last_digits}" data-month="{$card.card_month}"
											data-year="{$card.card_year}" />
										<b class="brand text-uppercase">{$card.card_brand}</b>
										-
										<b class="last_digits">{l s='Final:' mod='pagbank'} </b>
										<span class="text-uppercase">{$card.card_last_digits}</span>
									</label>
									<i class="icon icon-trash pull-right cursor-pointer"
										onclick="deleteCustomerToken({$card.id_customer_token})"></i>
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
				<div id="selected_card_token" class="alert alert-success" style="display:none;"></div>
			{/if}
			<div class="form-group card_data">
				<label for="card_number">{l s='Número do cartão:' mod='pagbank'}</label>
				<div class="input-group">
					<input id="card_number" class="form-control" name="card_number" {if $device == 'm'}type="tel" {else}type="text" {/if} 
						maxlength="16" min="0" size="16" value=""
						onkeyup="this.value = this.value.replace(/\D/g, '');"
						onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
						autocomplete="off" onpaste="return false" />
					<div id="credit-icon" class="input-group-addon">
						<i class="icon icon-credit-card fa fa-credit-card"></i>
					</div>
				</div>
			</div>
			<div class="row nopadding card_data">
				<div class="form-group col-xs-4 col-sm-4 nopadding-right">
					<label for="card_month">{l s='Validade:' mod='pagbank'}</label>
					{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
					<select id="card_month" class="number form-control" name="card_month"
						data-no-uniform="true"
						onchange="sendToCard(this.id, 'card-expiry-month');checkField(this.id);">
						<option value=""> Mês </option>
						{foreach from=$exp_months key=k item=month}
							<option value="{$month}">{$month}&nbsp;</option>
						{/foreach}
					</select>
				</div>
				<div class="form-group col-xs-4 col-sm-4 nopadding-right">
					<label for="card_year">{l s='Ano:' mod='pagbank'}</label>
					<select id="card_year" class="number form-control" name="card_year"
						data-no-uniform="true"
						onchange="sendToCard('card_year', 'card-expiry-year');checkField(this.id);"
						onblur="sendToCard('card_year', 'card-expiry-year');">
						<option value=""> Ano </option>
						{assign var=this_year value={$smarty.now|date_format:"%Y"}}
						{for $ano=$this_year to $this_year+15}
							<option value="{$ano}">{$ano|substr:-2}</option>
						{/for}
					</select>
				</div>
				<div class="form-group col-xs-4 col-sm-4">
					<label for="card_cvv">{l s='CVV:' mod='pagbank'}</label>
					<input id="card_cvv" class="form-control" name="card_cvv" {if $device == 'm'}type="tel" {else}type="text" {/if} 
						maxlength="4" min="0" size="4" value=""
						onkeyup="this.value = this.value.replace(/\D/g, '');sendToCard(this.id, 'card-cvv');"
						onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
						onpaste="return false" autocomplete="off" onfocus="toggleCardBack('add');"
						onblur="checkCVV();toggleCardBack('remove');checkField(this.id);" />
				</div>
			</div>
			{if ($save_credit_card)}
				<div class="form-group checkbox card_data">
					<div class="form-group col-xs-12 col-sm-12 inner">
						<label for="save_customer_card">
							<input id="save_customer_card" type="checkbox" name="save_customer_card" value="1" />
							<b>{l s='Salvar este cartão?' mod='pagbank'}</b>
							<a href="#save-card-faq" class="fancy-button">
								<img src="{$img_path}faq.png" title="{l s='Clique e saiba mais.' mod='pagbank'}" />
							</a>
						</label>
					</div>
				</div>
			{/if}
			{if ($active_discounts.discount_type > 0 && $active_discounts.discount_value > 0) && $active_discounts.credit_card}
				<div class="form-group">
					<div class="alert alert-success text-center col-xs-12 col-sm-12">
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
			<div id="installments" class="form-group">
				<label for="card_installment_qty">{l s='Parcelas:' mod='pagbank'}</label>
				<select id="card_installment_qty" class="number form-control" name="card_installment_qty"
					data-no-uniform="true" onchange="ps_setInstallment(this.id);" onblur="checkField(this.id);" required>
					<option value="">- Digite o número do cartão -</option>
				</select>
			</div>
		</div>
		<div class="form-group clearfix col-xs-12 col-sm-12">
			<br />
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#card_address">
					{l s='Confirmar endereço do titular' mod='pagbank'}
				</button>
			</div>
		</div>
		<div class="form-group clearfix col-xs-12 col-sm-12 collapse" id="card_address">
			<div class="row">
				<div class="col-xs-12 col-sm-6 pull-left">
					<div class="form-group">
						<label for="card_postcode_invoice">{l s='CEP:' mod='pagbank'}</label>
						<input id="card_postcode_invoice" class="form-control" name="postcode_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="9"
							value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_address_invoice">{l s='Endereço:' mod='pagbank'}</label>
						<input id="card_address_invoice" class="form-control" name="address_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="80"
							value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_number_invoice">{l s='Número:' mod='pagbank'}</label>
						<input id="card_number_invoice" class="form-control" name="number_invoice" type="text"
							onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
							onblur="checkField(this.id);" autocomplete="off" maxlength="10"
							value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
					</div>
					<div class="form-group">
						<label for="card_other_invoice">{l s='Complemento:' mod='pagbank'}</label>
						<input id="card_other_invoice" class="form-control" name="other_invoice" type="text"
							autocomplete="off" maxlength="40"
							value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 pull-right">
					<div class="form-group">
						<label for="card_address2_invoice">{l s='Bairro:' mod='pagbank'}</label>
						<input id="card_address2_invoice" class="form-control" name="address2_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_city_invoice">{l s='Cidade:' mod='pagbank'}</label>
						<input id="card_city_invoice" class="form-control" name="city_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
					</div>
					<div class="form-group">
						<label for="card_state_invoice">{l s='Estado:' mod='pagbank'}</label>
						<select id="card_state_invoice" class="form-control" name="state_invoice"
							data-no-uniform="true" onchange="checkField(this.id);" required>
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
		<p class="cart_navigation clearfix col-xs-12 col-sm-12">
			<button id="submitCard" type="button" name="submitCard" class="btn btn-success btn-lg hideOnSubmit pull-right">
				{l s='Pagar' mod='pagbank'} &nbsp;<i class="icon icon-check fa fa-check"></i>
			</button>
		</p>
	</form>
	<div id="save-card-faq" style="display:none;">
		<img src="{$img_path}saved-card-faq.jpg" title="{l s='Salvar este cartão para futuras compras?' mod='pagbank'}" />
	</div>
</div>