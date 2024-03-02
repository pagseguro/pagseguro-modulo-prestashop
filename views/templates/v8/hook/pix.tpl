{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
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

<div class="container-pix clearfix mt-2 mb-2">
	<form id="pix_pagbank" name="checkout" method="post" class="clearfix" onsubmit="return ps_pixCheckout(event);">
		<fieldset class="col-xs-12 col-sm-6 float-xs-left float-sm-left">
			<div class="col-xs-12 col-sm-12 clearfix nopadding">
				<div class="form-group row clearfix">
					<label class="col-xs-12 col-sm-12 text-xs-left text-sm-left"
						for="pix_name">{l s='Nome / Razão Social' d='Modules.PagBank.Shop'}</label>
					<div class="input-group col-xs-12 col-sm-12">
						<input id="pix_name" class="form-control" name="pix_name"
							onblur="checkField(this.id)"
							value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}"
							placeholder="Nome / Razão Social" required />
					</div>
				</div>
				<div class="form-group clearfix row">
					<label class="col-xs-12 col-sm-12 text-xs-left text-sm-left"
						for="pix_doc">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
					<div class="input-group col-xs-12 col-sm-12">
						<input id="pix_doc" class="form-control" name="cpf_cnpj" {if $device == 'm'}type="tel"
							{else}type="text" 
							{/if} maxlength="18"
							onkeyup="this.value.length == 14 || this.value.length == 18 ? checkField(this.id) : ''"
							onkeydown="this.value.length > 14 ? mascara(this,cnpjmask): mascara(this,cpfmask)"
							onblur="checkField(this.id);" value="{if (isset($cpf) && $cpf)}{$cpf}{/if}" size="30"
							placeholder="{l s='Somente números' d='Modules.PagBank.Shop'}" required />
					</div>
				</div>
				<div class="form-group clearfix row">
					<label class="col-xs-12 col-sm-12 text-xs-left text-sm-left"
						for="pix_phone">{l s='Telefone de contato:' d='Modules.PagBank.Shop'}</label>
					<div class="input-group col-xs-12">
						<input id="pix_phone" class="form-control" name="telephone" {if $device == 'm'}type="tel"
							{else}type="text" 
							{/if} maxlength="15" onkeypress="mascara(this,telefone)"
							onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
							value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999"
							required />
					</div>
				</div>
			</div>
		</fieldset>
		<div class="logo-pix col-xs-12 col-sm-6" align="center">
			<img title="Pix" src="{$this_path}img/pix.png" alt="{l s='Pix' d='Modules.PagBank.Shop'}"
				ondrag="return false" onselec="return false" oncontextmenu="return false" />
		</div>
		{if ($discounts.discount_type > 0 && $discounts.discount_value > 0) && $discounts.pix}
			<div class="info-discount alert alert-success text-xs-center text-sm-center col-xs-12 col-sm-6">
				<b>{l s='Pague com PIX e ganhe um desconto de' d='Modules.PagBank.Shop'}</b>
				<span class="discount">
					{if ($discounts.discount_type == 1)}
						{$discounts.discount_value}%
					{else}
						{Tools::displayPrice($discounts.discount_value|escape:'htmlall':'UTF-8')}
					{/if}
				</span>
				<br />
				<b>{l s='Total:' d='Modules.PagBank.Shop'}</b>
				<span class="total_discount">
					{Tools::displayPrice($discounts.pix_value|escape:'htmlall':'UTF-8')}
				</span>
			</div>
		{/if}
		{if ($alternate_time != false)}
			<div class="alert alert-warning text-xs-center text-sm-center col-xs-12 col-sm-6 alternate_time">
				<strong>{l s='Devido ao horário, o limite para pagamentos via pix pode ser reduzido, verifique junto ao seu banco.' d='Modules.PagBank.Shop'}</strong>
			</div>
		{/if}
		<div class="alert alert-info text-xs-center text-sm-center col-xs-12 col-sm-6 float-sm-right">
			<strong>
				{l s='Após a geração do QR Code do Pix, você terá' d='Modules.PagBank.Shop'} {$pix_timeout}
				{l s=' minutos para realizar o pagamento.' d='Modules.PagBank.Shop'}
				<br />{l s='Com o Pix a aprovação do pagamento é imediata.' d='Modules.PagBank.Shop'}
				<br />{l s='O Pix possui limite diário de transferência, consulte o seu banco para mais informações.'}
			</strong>
		</div>
		<div class="col-xs-12 col-sm-12 clearfix">
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ps_pix_address">
					{l s='Confirmar endereço de cobrança' d='Modules.PagBank.Shop'}
				</button>
			</div>
		</div>
		<fieldset class="col-xs-12 col-sm-12 collapse clearfix address" id="ps_pix_address">
			<div class="panel panel-info">
				<div class="panel-body">
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_postcode_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='CEP:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_postcode_invoice"
									onblur="checkField(this.id);"
									id="ps_pix_postcode_invoice" autocomplete="off" maxlength="9"
									value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_address_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Endereço:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_address_invoice" id="ps_pix_address_invoice" autocomplete="off"
									maxlength="80"
									value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_number_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Número:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_number_invoice"
									onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
									onblur="checkField(this.id);"
									id="ps_pix_number_invoice" autocomplete="off" maxlength="10"
									value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_other_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Complemento:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control" type="text" name="ps_other_invoice"
									id="ps_pix_other_invoice" autocomplete="off" maxlength="40"
									value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left nopadding">
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_address2_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Bairro:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_address2_invoice" id="ps_pix_address2_invoice" autocomplete="off"
									maxlength="60"
									value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_city_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Cidade:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<input class="form-control text-xs-left text-sm-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="ps_city_invoice" id="ps_pix_city_invoice" autocomplete="off"
									maxlength="60"
									value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-xs-left text-sm-left">
							<label for="ps_pix_state_invoice"
								class="text-xs-left text-sm-left col-xs-12 col-sm-12">{l s='Estado:' d='Modules.PagBank.Shop'}</label>
							<div class="input-group col-xs-12 col-sm-12">
								<select id="ps_pix_state_invoice" name="ps_state_invoice"
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