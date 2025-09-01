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

<div class="container-pix clearfix">
	<form id="pix_pagbank" name="checkout" method="post" action="{$link->getModuleLink('pagbank', 'validation', [], true)|escape:'html'}" target="_top"
	onsubmit="showLoading();" class="clearfix">
		<input type="hidden" name="pagbank_type" id="pagbank_type" value="pix"/>
		<fieldset class="col-xs-12 col-sm-6 pull-left">
			<div class="col-xs-12 col-sm-12 clearfix nopadding">
				<div class="form-group row clearfix">
					<label class="col-xs-12 col-sm-12" for="pix_name">{l s='Nome / Razão Social' mod='pagbank'}</label>
					<div class="input-group col-xs-12 col-sm-12">
						<input id="pix_name" class="form-control" name="pix_name" onblur="checkField(this.id)"
							value="{if (isset($sender_name) && $sender_name)}{$sender_name}{/if}"
							placeholder="Nome / Razão Social" required />
					</div>
				</div>
				<div class="form-group clearfix row">
					<label class="col-xs-12 col-sm-12" for="pix_doc">{l s='CPF/CNPJ:' mod='pagbank'}</label>
					<div class="input-group col-xs-12 col-sm-12">
						<input id="pix_doc" class="form-control" name="cpf_cnpj" {if $device == 'm'}type="tel"
							{else}type="text" 
							{/if} maxlength="18"
							onkeyup="this.value.length == 14 || this.value.length == 18 ? checkField(this.id) : ''"
							onkeydown="this.value.length > 14 ? mascara(this,cnpjmask): mascara(this,cpfmask)"
							onblur="checkField(this.id);" value="" size="30"
							placeholder="{l s='Somente números' mod='pagbank'}" required />
					</div>
				</div>
				<div class="form-group clearfix row">
					<label class="ccol-xs-12 col-sm-12"
						for="pix_phone">{l s='Telefone de contato:' mod='pagbank'}</label>
					<div class="input-group col-xs-12 col-sm-12">
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
		<div class="logo-pix col-xs-12 col-sm-6 {if $device == 'm'}clearfix{/if}" align="center">
			<img title="Pix" src="{$img_path}pix.png" alt="{l s='Pix' mod='pagbank'}" ondrag="return false"
				onselec="return false" oncontextmenu="return false" />
		</div>
		{if ($discounts.discount_type > 0 && $discounts.discount_value > 0) && $discounts.pix}
			<div class="info-discount alert alert-success text-center col-xs-12 col-sm-6">
				<b>{l s='Pague com PIX e ganhe um desconto de' mod='pagbank'}</b>
				<span class="discount">
					{if ($discounts.discount_type == 1)}
						{$discounts.discount_value}%
					{else}
						{displayPrice price=$discounts.discount_value currency=$currency->id}
					{/if}
				</span>
				<br />
				<b>{l s='Total:' mod='pagbank'}</b>
				<span class="total_discount">
					{displayPrice price=($discounts.pix_value) currency=$currency->id}
				</span>
			</div>
		{/if}
		{if ($alternate_time != false)}
			<div class="alert alert-warning text-center col-xs-12 col-sm-6 alternate_time">
				<strong>{l s='Devido ao horário, o limite para pagamentos via pix pode ser reduzido, verifique junto ao seu banco.' mod='pagbank'}</strong>
			</div>
		{/if}
		<div class="alert alert-info text-center col-xs-12 col-sm-6">
			<strong>
				{l s='Após a geração do QR Code do Pix, você terá' mod='pagbank'}
				{if {$pix_timeout.hours} > 0}
					{$pix_timeout.hours} {l s='horas' mod='pagbank'}
				{elseif {$pix_timeout.minutes} > 0}
					{$pix_timeout.minutes} {l s='minutos' mod='pagbank'}
				{/if}
				{l s=' para realizar o pagamento.' mod='pagbank'}
				<br />{l s='Com o Pix a aprovação do pagamento é imediata.' mod='pagbank'}
				<br />{l s='O Pix possui limite diário de transferência, consulte o seu banco para mais informações.' mod='pagbank'}
			</strong>
		</div>
		<div class="col-xs-12 col-sm-12 clearfix">
			<div class="clearfix">
				<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#pix_address">
					{l s='Confirmar endereço de cobrança' mod='pagbank'}
				</button>
			</div>
		</div>
		<fieldset class="col-xs-12 col-sm-12 collapse clearfix address" id="pix_address">
			<div class="panel panel-info">
				<div class="panel-body">
					<div class="col-xs-12 col-sm-6 pull-left nopadding-left">
						<div class="form-group clearfix text-left">
							<label for="pix_postcode_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='CEP:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control" type="text" name="postcode_invoice"
									onblur="checkField(this.id);"
									id="pix_postcode_invoice" autocomplete="off" maxlength="9"
									value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-left">
							<label for="pix_address_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Endereço:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control text-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="address_invoice" id="pix_address_invoice" autocomplete="off"
									maxlength="80"
									value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-left">
							<label for="pix_number_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Número:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control" type="text" name="number_invoice"
									onkeyup="this.value.length >= 1 ? checkField(this.id) : ''"
									onblur="checkField(this.id);"
									id="pix_number_invoice" autocomplete="off" maxlength="10"
									value="{if isset($number_invoice)}{$number_invoice}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-left">
							<label for="pix_other_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Complemento:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control" type="text" name="other_invoice"
									id="pix_other_invoice" autocomplete="off" maxlength="40"
									value="{if isset($compl_invoice)}{$compl_invoice}{/if}" />
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 pull-left nopadding">
						<div class="form-group clearfix text-left">
							<label for="pix_address2_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Bairro:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control text-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="address2_invoice" id="pix_address2_invoice" autocomplete="off"
									maxlength="60"
									value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}"
									required />
							</div>
						</div>
						<div class="form-group clearfix text-left">
							<label for="pix_city_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Cidade:' mod='pagbank'}</label>
							<div class="input-group">
								<input class="form-control text-left col-xs-12 col-sm-12" type="text"
									onblur="checkField(this.id);"
									name="city_invoice" id="pix_city_invoice" autocomplete="off" maxlength="60"
									value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}" required />
							</div>
						</div>
						<div class="form-group clearfix text-left">
							<label for="pix_state_invoice"
								class="text-left col-xs-12 col-sm-12">{l s='Estado:' mod='pagbank'}</label>
							<div class="input-group col-xs-12 col-sm-12 nopadding">
								<select id="pix_state_invoice" name="state_invoice"
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
		<p class="cart_navigation clearfix col-xs-12 col-sm-12">
			<button id="submitPix" type="button" name="submitPix" class="btn btn-success btn-lg hideOnSubmit">
				{l s='Confirmar pedido' mod='pagbank'}
				<i class="icon icon-check fa fa-check"></i>
			</button>
		</p>
	</form>
</div>