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

<div class="container-card clearfix mb-2">
	<div id="pagbank_card_error" class="col-xs-10 col-sm-10 col-lg-9 text-xs-center text-sm-center text-lg-center nofloat-block" style="display:none;"></div>
	<form id="card_pagbank" method="post" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" 
	class="pagbank_form clearfix" onsubmit="return psCardCheckout(event);" novalidate>
		<input type="hidden" name="payment_type" id="payment_type" value="credit_card" />
		<input type="hidden" name="recaptcha_credit_card" id="recaptcha_credit_card" />
		<input type="hidden" name="card_value_pagbank" id="card_value_pagbank" value="{$card_value_pagbank}" />
		{if isset($pay_two_card_enable) && $pay_two_card_enable == 1}
			<div class="mb-1 clearfix col-xs-12 col-sm-12 col-lg-12">
				<label class="form-label" for="pay_two_card">
					<input id="pay_two_card" name="pay_two_card" type="checkbox" value="1" />
					<b>{l s='Pagar com 2 cartões?' d='Modules.PagBank.Shop'}</b>
				</label>
			</div>
			<div id="choose_card" class="mb-1 clearfix col-xs-12 col-sm-12 col-lg-12" style="display: none;">
				<div class="mb-1 row">
					<div class="col-xs-12 col-sm-6 col-lg-4">
						<label class="form-label" for="card_one_input">{l s='Valor no cartão 1:' d='Modules.PagBank.Shop'}</label>
						<div class="input-group input-group-prepend">
							<span class="input-group-addon input-prices input-group-text">R$</span>
							<input id="card_one_input" class="form-control" name="card_one_input" type="text" inputmode="numeric" pattern="[0-9]*" 
							value="" size="10" onkeydown="mascara(this,valorcardmask);" onblur="psValidateCard();" required/>
						</div>
						<span class="form-control-comment">{l s='(somente números)' d='Modules.PagBank.Shop'}</span>
					</div>
					<div class="col-xs-12 col-sm-6 col-lg-4">
						<label class="form-label" for="card_two_input">{l s='Valor no cartão 2:' d='Modules.PagBank.Shop'}</label>
						<div class="input-group input-group-prepend">
							<span class="input-group-addon input-prices input-group-text">R$</span>
							<input id="card_two_input" class="form-control" name="card_two_input" type="text" inputmode="numeric" pattern="[0-9]*"  
							value="" size="10" required readonly />
						</div>
					</div>
				</div>
				<div class="mb-1">
					<button type="button" id="card_one_tab" class="card_one_tab btn btn-info">
						{l s='Cartão 1' d='Modules.PagBank.Shop'}
					</button>
					<button type="button" id="card_two_tab" class="card_two_tab btn btn-info">
						{l s='Cartão 2' d='Modules.PagBank.Shop'}
					</button>
				</div>
			</div>
		{/if}
		<div id="card_one" class="clearfix">
			<input type="hidden" name="card_brand" id="card_brand" />
			<input type="hidden" name="card_bin" id="card_bin" />
			<input type="hidden" name="card_token_id" id="card_token_id" />
			<input type="hidden" name="encrypted_card" id="encrypted_card" />
			<input type="hidden" name="card_installments" id="card_installments" />
			<input type="hidden" name="saved_card" id="saved_card" value="0" />
			<input type="hidden" name="pay_two_card_check" id="pay_two_card_check" value="0" />
			<div id="card_show" class="col-lg-6 float-xs-right float-sm-right float-right float-end p-0" align="center">
				<div class="card_title clearfix text-xs-center text-sm-center text-lg-center" style="display: none;"><b>{l s='Cartão 1' d='Modules.PagBank.Shop'}</b></div>
				<div id="card_wrapper" class="nofloat">
					<div id="card_container">
						<div id="mockup_number_card" class="mockup_number"></div>
						<div class="mockup_name"></div>
						<div class="mockup_expiry"><span class="mockup_expiry_month"></span> / <span class="mockup_expiry_year"></span></div>
						<div class="mockup_brand"></div>
						<span class="mockup_cvv"></span>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-lg-6 float-xs-left float-sm-left float-left float-start">
				<div class="mb-1">
					<label class="form-label" for="card_name">{l s='Titular do cartão:' d='Modules.PagBank.Shop'}</label>
					<input id="card_name" class="form-control" name="card_name" type="text" data-validate="isName" pattern="[A-Za-zÀ-ÿ\s]+"
						value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
						onblur="sendToCard(this.id, 'mockup_name');psValidateCard();" 
						oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '')" required />
				</div>
				<div class="mb-1">
					<label class="form-label" for="card_doc">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
					<input id="card_doc" class="form-control" name="cpf_cnpj" type="text" maxlength="18"
						onkeydown="this.value.length > 14 ? mascara(this,cnpjmask) : mascara(this,cpfmask); this.value = this.value.toUpperCase();"
						onblur="psValidateCard();" value="" size="18" required />
					<span class="form-control-comment">{l s='(cpf/cnpj do titular do cartão)' d='Modules.PagBank.Shop'}</span>
				</div>
				<div class="mb-1">
					<label class="form-label" for="card_phone">{l s='Telefone de contato:' d='Modules.PagBank.Shop'}</label>
					<input id="card_phone" class="form-control" name="telephone" type="text" inputmode="numeric" pattern="[0-9]*" 
						maxlength="15" onkeypress="mascara(this,telefone);"
						onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
						value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
				</div>
				{if ($save_credit_card && isset($customer_token) && is_array($customer_token))}
					<div id="saved_cards" class="mb-1 card text-white clearfix">
						<div class="card-header bg-primary">
							<h3 class="card-title">{l s='Deseja pagar com um dos seus cartões salvos?' d='Modules.PagBank.Shop'}
							</h3>
						</div>
						<div class="card-body">
							<ul class="list">
								{foreach from=$customer_token key=t item=card name=limit}
									<li class="radiobox">
										<label for="token_{$card.id_customer_token}">
											<input id="token_{$card.id_customer_token}" class="mb-1 check_token" name="check_token"
												type="radio" value="{$card.id_customer_token}" 
												data-name="{$card.card_name}" data-brand="{$card.card_brand}"
												data-firstdigits="{$card.card_first_digits}"
												data-lastdigits="{$card.card_last_digits}" data-month="{$card.card_month}"
												data-year="{$card.card_year}" />
											<b class="brand text-uppercase">{$card.card_brand}</b>
											-
											<b class="last_digits">{l s='Final:' d='Modules.PagBank.Shop'} </b>
											<span class="text-uppercase">{$card.card_last_digits}</span>
										</label>
										<i class="cursor-pointer material-icons"
											onclick="deleteCustomerToken({$card.id_customer_token})">
											delete_forever
										</i>
									</li>{if $smarty.foreach.limit.last}{else}<br />{/if}
								{/foreach}
							</ul>
						</div>
						<button id="reload_button" class="float-xs-right float-sm-right float-right float-end" type="button" style="display:none;" onclick="resetFields();">
							<img src="{$img_path}reload.png" title="{l s='Reset' d='Modules.PagBank.Shop'}" />
						</button>
					</div>
					<div id="selected_card_token" class="alert alert-success" style="display:none;"></div>
				{/if}
				<div class="mb-1 card_data">
					<label class="form-label" for="card_number">{l s='Número do cartão:' d='Modules.PagBank.Shop'}</label>
					<div class="input-group">
						<input id="card_number" class="form-control" name="card_number" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="16" min="0" size="16" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');" onblur="psValidateCard();"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							autocomplete="off" onpaste="return false" />
						{if $ps_version >= '8.0.0'}
							<div>
								<span id="credit_icon" class="input-group-addon input-group-text">
									<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>
								</span>
							</div>
						{else}
							<div id="credit_icon" class="input-group-addon">
								<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>
							</div>
						{/if}
					</div>
				</div>
				<div class="mb-1 row p-0 card_data">
					<div class="col-xs-4 col-sm-4 col-lg-4 pr-0">
						<label class="form-label" for="card_month">{l s='Mês:' d='Modules.PagBank.Shop'}</label>
						{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
						<select id="card_month" class="number form-select form-control" name="card_month"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_month');psValidateCard();">
							<option value=""> -- </option>
							{foreach from=$exp_months key=k item=month}
								<option value="{$month}">{$month}&nbsp;</option>
							{/foreach}
						</select>
					</div>
					<div class="col-xs-4 col-sm-4 col-lg-4 pr-0">
						<label class="form-label" for="card_year">{l s='Ano:' d='Modules.PagBank.Shop'}</label>
						<select id="card_year" class="number form-select form-control" name="card_year"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_year');psValidateCard();">
							<option value=""> -- </option>
							{assign var=this_year value={$smarty.now|date_format:"%Y"}}
							{for $ano=$this_year to $this_year+15}
								<option value="{$ano}">{$ano|substr:-2}</option>
							{/for}
						</select>
					</div>
					<div class="col-xs-4 col-sm-4 col-lg-4">
						<label class="form-label" for="card_cvv">{l s='CVV:' d='Modules.PagBank.Shop'}</label>
						<input id="card_cvv" class="form-control" name="card_cvv" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="4" min="0" size="4" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');sendToCard(this.id, 'mockup_cvv');"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							onpaste="return false" autocomplete="off" onfocus="toggleCardBack('add', false);"
							onblur="toggleCardBack('remove', false);psValidateCard();" />
					</div>
				</div>
				{if isset($save_credit_card) && $save_credit_card == 1}
					<div class="mb-1 col-xs-12 col-sm-12 col-lg-12 inner p-0 card_data">
						<label class="form-label" for="save_customer_card">
							<input id="save_customer_card" name="save_customer_card" type="checkbox" value="1" />
							<b>{l s='Salvar este cartão?' d='Modules.PagBank.Shop'}</b>
							<a href="#save-card-faq" class="fancy-button">
								<img src="{$img_path}faq.png" title="{l s='Clique e saiba mais.' d='Modules.PagBank.Shop'}" />
							</a>
						</label>
					</div>
				{/if}
				{if ($active_discounts.discount_type > 0 && $active_discounts.discount_value > 0) && $active_discounts.credit_card}
					<div id="discount_info" class="mb-1">
						<div class="alert alert-success text-xs-center text-sm-center text-lg-center col-xs-12 col-sm-12 col-lg-12">
							<b>{l s='Pague em 1x e com desconto de' d='Modules.PagBank.Shop'}</b>
							<span class="discount">
								{if ($active_discounts.discount_type == 1)}
									{$active_discounts.discount_value}%
								{else}
									{if $ps_version >= '9.0.0'}
										{Context::getContext()->currentLocale->formatPrice($active_discounts.discount_value, $currency->iso_code)}
									{else}
										{Tools::displayPrice($active_discounts.discount_value|escape:'htmlall':'UTF-8')}
									{/if}
								{/if}
							</span>
						</div>
					</div>
				{/if}
				<div id="installments" class="mb-1">
					<label class="form-label" for="card_installment_qty">{l s='Parcelas:' d='Modules.PagBank.Shop'}</label>
					<select id="card_installment_qty" class="number form-select form-control" name="card_installment_qty"
						data-no-uniform="true" onchange="psSetInstallment(this.id);psValidateCard();" required>
						<option value="">- Digite o número do cartão -</option>
					</select>
				</div>
			</div>
		</div>
		{if isset($pay_two_card_enable) && $pay_two_card_enable == 1}
		<div id="card_two" class="clearfix" style="display: none;">
			<input type="hidden" name="card_brand_two" id="card_brand_two" />
			<input type="hidden" name="card_bin_two" id="card_bin_two" />
			<input type="hidden" name="encrypted_card_two" id="encrypted_card_two" />
			<input type="hidden" name="card_installments_two" id="card_installments_two" />
			<div id="card_show_two" class="col-lg-6 float-xs-right float-sm-right float-right float-end p-0" align="center">
				<div class="card_title clearfix text-xs-center text-sm-center text-lg-center" style="display: none;"><b>{l s='Cartão 2' d='Modules.PagBank.Shop'}</b></div>
				<div id="card_wrapper_two" class="nofloat">
					<div id="card_container_two">
						<div id="mockup_number_card_two" class="mockup_number_two"></div>
						<div class="mockup_name_two"></div>
						<div class="mockup_expiry_two"><span class="mockup_expiry_month_two"></span> / <span class="mockup_expiry_year_two"></span></div>
						<div class="mockup_brand_two"></div>
						<span class="mockup_cvv_two"></span>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-lg-6 float-xs-left float-sm-left float-left float-start">
				<div class="mb-1">
					<label class="form-label" for="card_name_two">{l s='Titular do cartão:' d='Modules.PagBank.Shop'}</label>
					<input id="card_name_two" class="form-control" name="card_name_two" type="text" data-validate="isName" pattern="[A-Za-zÀ-ÿ\s]+"
						value="" size="30" onblur="sendToCard(this.id, 'mockup_name_two', this.value, true);psValidateCard(true);" 
						oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '')" required />
				</div>
				<div class="mb-1">
					<label class="form-label" for="card_doc_two">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
					<input id="card_doc_two" class="form-control" name="cpf_cnpj_two" type="text" maxlength="18"
						onkeydown="this.value.length > 14 ? mascara(this,cnpjmask) : mascara(this,cpfmask); this.value = this.value.toUpperCase();"
						onblur="psValidateCard(true);" value="" size="18" required />
					<span class="form-control-comment">{l s='(cpf/cnpj do titular do cartão)' d='Modules.PagBank.Shop'}</span>
				</div>
				<div class="mb-1">
					<label class="form-label" for="card_number_two">{l s='Número do cartão:' d='Modules.PagBank.Shop'}</label>
					<div class="input-group">
						<input id="card_number_two" class="form-control" name="card_number_two" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="16" min="0" size="16" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');" onblur="psValidateCard(true);"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							autocomplete="off" onpaste="return false" />
						{if $ps_version >= '8.0.0'}
							<div>
								<span id="credit_icon_two" class="input-group-addon input-group-text">
									<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>
								</span>
							</div>
						{else}
							<div id="credit_icon_two" class="input-group-addon">
								<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>
							</div>
						{/if}
					</div>
				</div>
				<div class="mb-1 row p-0">
					<div class="col-xs-4 col-sm-4 col-lg-4 pr-0">
						<label class="form-label" for="card_month_two">{l s='Mês:' d='Modules.PagBank.Shop'}</label>
						{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
						<select id="card_month_two" class="number form-select form-control" name="card_month_two"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_month_two', this.value, true);psValidateCard(true);">
							<option value=""> -- </option>
							{foreach from=$exp_months key=k item=month}
								<option value="{$month}">{$month}&nbsp;</option>
							{/foreach}
						</select>
					</div>
					<div class="col-xs-4 col-sm-4 col-lg-4 pr-0">
						<label class="form-label" for="card_year_two">{l s='Ano:' d='Modules.PagBank.Shop'}</label>
						<select id="card_year_two" class="number form-select form-control" name="card_year_two"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_year_two', this.value, true);psValidateCard(true);">
							<option value=""> -- </option>
							{assign var=this_year value={$smarty.now|date_format:"%Y"}}
							{for $ano=$this_year to $this_year+15}
								<option value="{$ano}">{$ano|substr:-2}</option>
							{/for}
						</select>
					</div>
					<div class="col-xs-4 col-sm-4 col-lg-4">
						<label class="form-label" for="card_cvv_two">{l s='CVV:' d='Modules.PagBank.Shop'}</label>
						<input id="card_cvv_two" class="form-control" name="card_cvv_two" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="4" min="0" size="4" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');sendToCard(false, 'mockup_cvv_two', this.value, true);"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							onpaste="return false" autocomplete="off" onfocus="toggleCardBack('add', true);"
							onblur="checkCVV();toggleCardBack('remove', true);psValidateCard(true);" />
					</div>
				</div>
				<div id="installments_two" class="mb-1">
					<label class="form-label" for="card_installment_qty_two">{l s='Parcelas:' d='Modules.PagBank.Shop'}</label>
					<select id="card_installment_qty_two" class="number form-select form-control" name="card_installment_qty_two"
						data-no-uniform="true" onchange="psSetInstallment(this.id);psValidateCard(true);" required>
						<option value="">- Digite o número do cartão -</option>
					</select>
				</div>
			</div>
		</div>
		{/if}
		<div class="mb-1 clearfix col-xs-12 col-sm-12 col-lg-12">
			<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#card_address" 
			data-bs-toggle="collapse" data-bs-target="#card_address">
				{l s='Confirmar endereço de cobrança' d='Modules.PagBank.Shop'}
			</button>
		</div>
		<div class="mb-1 clearfix col-xs-12 col-sm-12 col-lg-12 collapse" id="card_address">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-lg-6 float-xs-left float-sm-left float-left float-start">
					<div class="mb-1">
						<label class="form-label" for="card_postcode_invoice">{l s='CEP:' d='Modules.PagBank.Shop'}</label>
						<input id="card_postcode_invoice" class="form-control" name="postcode_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="9"
							value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="card_address_invoice">{l s='Endereço:' d='Modules.PagBank.Shop'}</label>
						<input id="card_address_invoice" class="form-control" name="address_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="80"
							value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="card_number_invoice">{l s='Número:' d='Modules.PagBank.Shop'}</label>
						<input id="card_number_invoice" class="form-control" name="number_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="10"
							value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="card_other_invoice">{l s='Complemento:' d='Modules.PagBank.Shop'}</label>
						<input id="card_other_invoice" class="form-control" name="other_invoice" type="text"
							autocomplete="off" maxlength="40"
							value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-lg-6 float-xs-right float-sm-right float-right float-end">
					<div class="mb-1">
						<label class="form-label" for="card_address2_invoice">{l s='Bairro:' d='Modules.PagBank.Shop'}</label>
						<input id="card_address2_invoice" class="form-control" name="address2_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="card_city_invoice">{l s='Cidade:' d='Modules.PagBank.Shop'}</label>
						<input id="card_city_invoice" class="form-control" name="city_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="card_state_invoice">{l s='Estado:' d='Modules.PagBank.Shop'}</label>
						<select id="card_state_invoice" class="form-select form-control" name="state_invoice"
							data-no-uniform="true" onchange="psValidateCard();" required>
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
	</form>
	<div id="save-card-faq" style="display:none;">
		<img src="{$img_path}saved-card-faq.jpg" title="{l s='Salvar este cartão para futuras compras?' d='Modules.PagBank.Shop'}" />
	</div>
</div>