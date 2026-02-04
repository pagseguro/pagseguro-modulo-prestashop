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

<div class="container-pix clearfix mb-2">
	<div id="pagbank_pix_error" class="col-xs-10 col-sm-9 text-xs-center text-sm-center nofloat-block" style="display:none;"></div>
	<form id="pix_pagbank" name="checkout" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" method="post" class="pagbank_form clearfix"
	onsubmit="return ps_pixCheckout(event);">
		<input type="hidden" name="pagbank_type" id="pagbank_type" value="pix"/>
		<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left float-left float-start">
			<div class="mb-1">
				<label class="form-label" for="pix_name">{l s='Nome/Razão Social' d='Modules.PagBank.Shop'}</label>
				<input id="pix_name" class="form-control" name="pix_name" type="text" data-validate="isName"
					onblur="checkField(this.id)" size="30"
					value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}"
					placeholder="Nome/Razão Social" required />
			</div>
			<div class="mb-1">
				<label class="form-label" for="pix_doc">{l s='CPF/CNPJ:' d='Modules.PagBank.Shop'}</label>
				<input id="pix_doc" class="form-control" name="cpf_cnpj" type="text" maxlength="18"
					onkeyup="this.value.length == 14 || this.value.length == 18 ? checkField(this.id) : ''; this.value = this.value.toUpperCase();"
					onkeydown="this.value.length > 14 ? mascara(this,cnpjmask): mascara(this,cpfmask)"
					onblur="checkField(this.id);" value="" size="18" required />
			</div>
			<div class="mb-1">
				<label class="form-label" for="pix_phone">{l s='Telefone de contato:' d='Modules.PagBank.Shop'}</label>
				<input id="pix_phone" class="form-control" name="telephone" {if $device == 'm'}type="tel"{else}type="text"{/if} 
					maxlength="15" onkeypress="mascara(this,telefone)"
					onblur="validatePhoneNumber(this.id);mascara(this,telefone);"
					value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999"
					required />
			</div>
		</div>
		<div class="right-side infos col-xs-12 col-sm-6 float-xs-right float-sm-right float-right float-end">
			<div class="logo-pix" align="center">
				<img title="Pix" src="{$img_path}pix.png" alt="{l s='Pix' d='Modules.PagBank.Shop'}"
					ondrag="return false" onselec="return false" oncontextmenu="return false" />
			</div>
			{if ($active_discounts.discount_type > 0 && $active_discounts.discount_value > 0) && $active_discounts.pix}
				<div class="info-discount alert alert-success text-xs-center text-sm-center">
					<b>{l s='Pague com PIX e ganhe um desconto de' d='Modules.PagBank.Shop'}</b>
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
					<br />
					<b>{l s='Total:' d='Modules.PagBank.Shop'}</b>
					<span class="total_discount">
						{if $ps_version >= '9.0.0'}
							{Context::getContext()->currentLocale->formatPrice($active_discounts.pix_value, $currency->iso_code)}
						{else}
							{Tools::displayPrice($active_discounts.pix_value|escape:'htmlall':'UTF-8')}
						{/if}
					</span>
				</div>
			{/if}
			{if ($alternate_time != false)}
				<div class="alert alert-warning text-xs-center text-sm-center alternate_time">
					<strong>{l s='Devido ao horário, o limite para pagamentos via pix pode ser reduzido, verifique junto ao seu banco.' d='Modules.PagBank.Shop'}</strong>
				</div>
			{/if}
			<div class="alert alert-info text-xs-center text-sm-center">
				<strong>
					{l s='Após a geração do QR Code do Pix, você terá' d='Modules.PagBank.Shop'}
					{if {$pix_timeout.hours} > 0}
						{$pix_timeout.hours} {l s='horas' d='Modules.PagBank.Shop'}
					{elseif {$pix_timeout.minutes} > 0}
						{$pix_timeout.minutes} {l s='minutos' d='Modules.PagBank.Shop'}
					{/if}
					{l s='  para realizar o pagamento.' d='Modules.PagBank.Shop'}
					<br />{l s='Com o Pix a aprovação do pagamento é imediata.' d='Modules.PagBank.Shop'}
					<br />{l s='O Pix possui limite diário de transferência, consulte o seu banco para mais informações.' d='Modules.PagBank.Shop'}
				</strong>
			</div>
		</div>
		<div class="mb-1 clearfix col-xs-12 col-sm-12">
			<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#pix_address" data-bs-toggle="collapse" data-bs-target="#pix_address">
				{l s='Confirmar endereço de cobrança' d='Modules.PagBank.Shop'}
			</button>
		</div>
		<div class="mb-1 clearfix col-xs-12 col-sm-12 collapse" id="pix_address">
			<div class="row">	
				<div class="col-xs-12 col-sm-6 float-xs-left float-sm-left float-left float-start">
					<div class="mb-1">
						<label class="form-label" for="pix_postcode_invoice">{l s='CEP:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_postcode_invoice" class="form-control" name="postcode_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="9"
							value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="pix_address_invoice">{l s='Endereço:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_address_invoice" class="form-control" name="address_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="80"
							value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="pix_number_invoice">{l s='Número:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_number_invoice" class="form-control" name="number_invoice" type="text"
							onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
							onblur="checkField(this.id);" autocomplete="off" maxlength="10"
							value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="pix_other_invoice">{l s='Complemento:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_other_invoice" class="form-control" name="other_invoice" type="text"
							autocomplete="off" maxlength="40"
							value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 float-xs-right float-sm-right float-right float-end">
					<div class="mb-1">
						<label class="form-label" for="pix_address2_invoice">{l s='Bairro:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_address2_invoice" class="form-control" name="address2_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
							required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="pix_city_invoice">{l s='Cidade:' d='Modules.PagBank.Shop'}</label>
						<input id="pix_city_invoice" class="form-control" name="city_invoice" type="text"
							onblur="checkField(this.id);" autocomplete="off" maxlength="60"
							value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
					</div>
					<div class="mb-1">
						<label class="form-label" for="pix_state_invoice">{l s='Estado:' d='Modules.PagBank.Shop'}</label>
						<select id="pix_state_invoice" class="form-select form-control" name="state_invoice"
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
	</form>
</div>