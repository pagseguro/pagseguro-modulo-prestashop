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
	<div id="pagbank_card_error" class="col-xs-10 col-sm-10 col-lg-9 text-center nofloat-block" style="display:none;"></div>
	<form id="card_pagbank" method="post" target="_top" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" 
	class="clearfix" novalidate>
		<input type="hidden" name="payment_type" id="payment_type" value="credit_card" />
		<input type="hidden" name="recaptcha_credit_card" id="recaptcha_credit_card" />
		<input type="hidden" name="card_value_pagbank" id="card_value_pagbank" value="{$card_value_pagbank}" />
		{if isset($pay_two_card_enable) && $pay_two_card_enable == 1}
			<div class="form-group checkbox pay_opt">
				<div class="form-group col-xs-12 col-sm-12 col-lg-12">
					<label for="pay_two_card">
						<input id="pay_two_card" name="pay_two_card" type="checkbox" value="1" />
						<b>{l s='Pagar com 2 cartões?' mod='pagbank'}</b>
					</label>
				</div>
			</div>
			<div id="choose_card" class="form-group clearfix col-xs-12 col-sm-12 col-lg-12" style="display: none;">
				<div class="form-group col-xs-12 col-sm-6 col-lg-4 nopadding-left">
					<label for="card_one_input">{l s='Valor no cartão 1:' mod='pagbank'}</label>
					<div class="input-group">
						<span class="input-group-addon input-prices six">R$</span>
						<input id="card_one_input" class="form-control" name="card_one_input" type="text" inputmode="numeric" pattern="[0-9]*" 
						value="" size="10" onkeydown="mascara(this,valorcardmask);" onblur="psValidateCard();" required />
					</div>
					<span class="form-control-comment">{l s='(somente números)' mod='pagbank'}</span>
				</div>
				<div class="form-group col-xs-12 col-sm-6 col-lg-4 nopadding-left">
					<label for="card_two_input">{l s='Valor no cartão 2:' mod='pagbank'}</label>
					<div class="input-group">
						<span class="input-group-addon input-prices six">R$</span>
						<input id="card_two_input" class="form-control" name="card_two_input" type="text" inputmode="numeric" pattern="[0-9]*"  
						value="" size="10" required readonly />
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-lg-12 clearfix nopadding-left">
					<br />
					<button type="button" id="card_one_tab" class="card_one_tab btn btn-info">
						{l s='Cartão 1' mod='pagbank'}
					</button>
					<button type="button" id="card_two_tab" class="card_two_tab btn btn-info">
						{l s='Cartão 2' mod='pagbank'}
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
			<div id="card_show" class="col-lg-6 pull-right nopadding-left" align="center">
				<div class="card_title clearfix text-center" style="display: none;"><b>{l s='Cartão 1' mod='pagbank'}</b></div>
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
			<div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
				<div class="form-group">
					<label for="card_name">{l s='Titular do cartão:' mod='pagbank'}</label>
					<input id="card_name" class="form-control" name="card_name" type="text" data-validate="isName" pattern="[A-Za-zÀ-ÿ\s]+" 
						value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}" size="30"
						onblur="sendToCard(this.id, 'mockup_name');psValidateCard();" 
						oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '')" required />
				</div>
				<div class="form-group">
					<label for="card_doc">{l s='CPF/CNPJ:' mod='pagbank'}</label>
					<input id="card_doc" class="form-control" name="cpf_cnpj" type="text" maxlength="18"
						onkeydown="this.value.length > 14 ? mascara(this,cnpjmask) : mascara(this,cpfmask); this.value = this.value.toUpperCase();"
						onblur="psValidateCard();" value="" size="18" required />
					<span class="form_info">{l s='(cpf/cnpj do titular do cartão)' mod='pagbank'}</span>
				</div>
				<div class="form-group">
					<label for="card_phone">{l s='Telefone de contato:' mod='pagbank'}</label>
					<input id="card_phone" class="form-control" name="telephone" type="text" inputmode="numeric" pattern="[0-9]*" 
						maxlength="15" onkeypress="mascara(this,telefone)"
						onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
						value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
				</div>
				{if ($save_credit_card && isset($customer_token) && is_array($customer_token))}
					<br />
					<div id="saved_cards" class="form-group panel panel-primary clearfix">
						<div class="panel-heading">
							{l s='Deseja pagar com um dos seus cartões salvos?' mod='pagbank'}
						</div>
						<div class="panel-body">
							<ul class="list">
								{foreach from=$customer_token key=t item=card}
									<li class="radiobox">
										<label for="token_{$card.id_customer_token}">
											<input id="token_{$card.id_customer_token}" class="check_token" name="check_token" 
												type="radio" value="{$card.id_customer_token}"
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
						<button id="reload_button" class="pull-right" type="button" style="display:none;" onclick="resetFields();">
							<img src="{$img_path}reload.png" title="{l s='Reset' mod='pagbank'}" />
						</button>
					</div>
					<br />
					<div id="selected_card_token" class="alert alert-success" style="display:none;"></div>
				{/if}
				<div class="form-group card_data">
					<label for="card_number">{l s='Número do cartão:' mod='pagbank'}</label>
					<div class="input-group">
						<input id="card_number" class="form-control" name="card_number" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="16" min="0" size="16" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');" onblur="psValidateCard();"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							autocomplete="off" onpaste="return false" />
						<div id="credit_icon" class="input-group-addon">
							<i class="icon icon-credit-card fa fa-credit-card"></i>
						</div>
					</div>
				</div>
				<div class="row nopadding card_data">
					<div class="form-group col-xs-4 col-sm-4 col-lg-4 nopadding-right">
						<label for="card_month">{l s='Mês:' mod='pagbank'}</label>
						{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
						<select id="card_month" class="number form-control" name="card_month"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_month');psValidateCard();">
							<option value=""> Mês </option>
							{foreach from=$exp_months key=k item=month}
								<option value="{$month}">{$month}&nbsp;</option>
							{/foreach}
						</select>
					</div>
					<div class="form-group col-xs-4 col-sm-4 col-lg-4 nopadding-right">
						<label for="card_year">{l s='Ano:' mod='pagbank'}</label>
						<select id="card_year" class="number form-control" name="card_year"
							data-no-uniform="true" onchange="sendToCard('card_year', 'mockup_expiry_year');psValidateCard();">
							<option value=""> Ano </option>
							{assign var=this_year value={$smarty.now|date_format:"%Y"}}
							{for $ano=$this_year to $this_year+15}
								<option value="{$ano}">{$ano|substr:-2}</option>
							{/for}
						</select>
					</div>
					<div class="form-group col-xs-4 col-sm-4 col-lg-4">
						<label for="card_cvv">{l s='CVV:' mod='pagbank'}</label>
						<input id="card_cvv" class="form-control" name="card_cvv" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="4" min="0" size="4" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');sendToCard(this.id, 'mockup_cvv');"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							onpaste="return false" autocomplete="off" onfocus="toggleCardBack('add');"
							onblur="toggleCardBack('remove');psValidateCard();" />
					</div>
				</div>
				{if ($save_credit_card)}
					<div class="form-group checkbox">
						<div class="form-group col-xs-12 col-sm-12 col-lg-12 inner card_data">
							<label for="save_customer_card">
								<input id="save_customer_card" name="save_customer_card" type="checkbox" value="1" />
								<b>{l s='Salvar este cartão?' mod='pagbank'}</b>
								<a href="#save-card-faq" class="fancy-button">
									<img src="{$img_path}faq.png" title="{l s='Clique e saiba mais.' mod='pagbank'}" />
								</a>
							</label>
						</div>
					</div>
				{/if}
				{if ($active_discounts.discount_type > 0 && $active_discounts.discount_value > 0) && $active_discounts.credit_card}
					<div id="discount_info" class="form-group">
						<div class="alert alert-success text-center col-xs-12 col-sm-12 col-lg-12">
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
			<div id="card_show_two" class="col-lg-6 pull-right nopadding-left" align="center">
				<div class="card_title clearfix text-center" style="display: none;"><b>{l s='Cartão 2' mod='pagbank'}</b></div>
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
			<div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
				<div class="form-group">
					<label for="card_name_two">{l s='Titular do cartão:' mod='pagbank'}</label>
					<input id="card_name_two" class="form-control" name="card_name_two" type="text" data-validate="isName"
						pattern="[A-Za-zÀ-ÿ\s]+" value="" size="30" 
						onblur="sendToCard(this.id, 'mockup_name_two', this.value, true);psValidateCard(true);" 
						oninput="this.value = this.value.replace(/[^A-Za-zÀ-ÿ\s]/g, '')" required />
				</div>
				<div class="form-group">
					<label for="card_doc_two">{l s='CPF/CNPJ:' mod='pagbank'}</label>
					<input id="card_doc_two" class="form-control" name="cpf_cnpj_two" type="text" maxlength="18"
						onkeydown="this.value.length > 14 ? mascara(this,cnpjmask) : mascara(this,cpfmask); this.value = this.value.toUpperCase();"
						onblur="psValidateCard(true);" value="" size="18" required />
					<span class="form-control-comment">{l s='(cpf/cnpj do titular do cartão)' mod='pagbank'}</span>
				</div>
				<div class="form-group">
					<label for="card_number_two">{l s='Número do cartão:' mod='pagbank'}</label>
					<div class="input-group">
						<input id="card_number_two" class="form-control" name="card_number_two" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="16" min="0" size="16" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');" onblur="psValidateCard(true);"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							autocomplete="off" onpaste="return false" />
						<div id="credit_icon_two" class="input-group-addon">
							<i class="icon icon-credit-card fa fa-credit-card"></i>
						</div>
					</div>
				</div>
				<div class="row nopadding">
					<div class="form-group col-xs-4 col-sm-4 col-lg-4 nopadding-right">
						<label for="card_month_two">{l s='Mês:' mod='pagbank'}</label>
						{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
						<select id="card_month_two" class="number form-control" name="card_month_two"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_month_two', this.value, true);psValidateCard(true);">
							<option value=""> Mês </option>
							{foreach from=$exp_months key=k item=month}
								<option value="{$month}">{$month}&nbsp;</option>
							{/foreach}
						</select>
					</div>
					<div class="form-group col-xs-4 col-sm-4 col-lg-4 nopadding-right">
						<label for="card_year_two">{l s='Ano:' mod='pagbank'}</label>
						<select id="card_year_two" class="number form-control" name="card_year_two"
							data-no-uniform="true" onchange="sendToCard(this.id, 'mockup_expiry_year_two', this.value, true);psValidateCard(true);">
							<option value=""> Ano </option>
							{assign var=this_year value={$smarty.now|date_format:"%Y"}}
							{for $ano=$this_year to $this_year+15}
								<option value="{$ano}">{$ano|substr:-2}</option>
							{/for}
						</select>
					</div>
					<div class="form-group col-xs-4 col-sm-4 col-lg-4">
						<label for="card_cvv_two">{l s='CVV:' mod='pagbank'}</label>
						<input id="card_cvv_two" class="form-control" name="card_cvv_two" type="text" inputmode="numeric" pattern="[0-9]*" 
							maxlength="4" min="0" size="4" value=""
							onkeyup="this.value = this.value.replace(/\D/g, '');sendToCard(false, 'mockup_cvv_two', this.value, true);"
							onkeydown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
							onpaste="return false" autocomplete="off" onfocus="toggleCardBack('add', true);"
							onblur="checkCVV();toggleCardBack('remove', true);psValidateCard(true);" />
					</div>
				</div>
				<div id="installments_two" class="form-group">
					<label for="card_installment_qty_two">{l s='Parcelas:' mod='pagbank'}</label>
					<select id="card_installment_qty_two" class="number form-control" name="card_installment_qty_two"
						data-no-uniform="true" onchange="psSetInstallment(this.id);psValidateCard(true);" required>
						<option value="">- Digite o número do cartão -</option>
					</select>
				</div>
			</div>
		</div>
		{/if}
		<div class="form-group clearfix col-xs-12 col-sm-12 col-lg-12">
			<br />
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#card_address">
					{l s='Confirmar endereço do titular' mod='pagbank'}
				</button>
			</div>
		</div>
		<div class="form-group clearfix col-xs-12 col-sm-12 col-lg-12 collapse" id="card_address">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-lg-6 pull-left">
					<div class="form-group">
						<label for="card_postcode_invoice">{l s='CEP:' mod='pagbank'}</label>
						<input id="card_postcode_invoice" class="form-control" name="postcode_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="9"
							value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_address_invoice">{l s='Endereço:' mod='pagbank'}</label>
						<input id="card_address_invoice" class="form-control" name="address_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="80"
							value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_number_invoice">{l s='Número:' mod='pagbank'}</label>
						<input id="card_number_invoice" class="form-control" name="number_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="10"
							value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
					</div>
					<div class="form-group">
						<label for="card_other_invoice">{l s='Complemento:' mod='pagbank'}</label>
						<input id="card_other_invoice" class="form-control" name="other_invoice" type="text"
							autocomplete="off" maxlength="40"
							value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-lg-6 pull-right">
					<div class="form-group">
						<label for="card_address2_invoice">{l s='Bairro:' mod='pagbank'}</label>
						<input id="card_address2_invoice" class="form-control" name="address2_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
							required />
					</div>
					<div class="form-group">
						<label for="card_city_invoice">{l s='Cidade:' mod='pagbank'}</label>
						<input id="card_city_invoice" class="form-control" name="city_invoice" type="text"
							onblur="psValidateCard();" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
					</div>
					<div class="form-group">
						<label for="card_state_invoice">{l s='Estado:' mod='pagbank'}</label>
						<select id="card_state_invoice" class="form-control" name="state_invoice"
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
		<div class="clear clearfix"></div>
		<p class="cart_navigation clearfix col-xs-12 col-sm-12 col-lg-12">
			<button id="submitCard" type="button" name="submitCard" class="btn btn-success btn-lg hideOnSubmit pull-right">
				{l s='Pagar' mod='pagbank'} &nbsp;<i class="icon icon-check fa fa-check"></i>
			</button>
		</p>
	</form>
	<div id="save-card-faq" style="display:none;">
		<img src="{$img_path}saved-card-faq.jpg" title="{l s='Salvar este cartão para futuras compras?' mod='pagbank'}" />
	</div>
</div>