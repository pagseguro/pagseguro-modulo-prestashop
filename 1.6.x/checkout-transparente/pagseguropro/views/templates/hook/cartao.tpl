{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 *}

<form id="checkout_pagseguropro" name="checkout" action="{$link->getModuleLink('pagseguropro', 'validation', [], true)|escape:'html'}" method="post" class="clearfix">
	<!-- Campos hidden -->
	<input type="hidden" name="ps_tipo" id="ps_tipo" value="cartao"/>
	<input type="hidden" name="ps_cartao_bandeira" id="ps_cartao_bandeira" value="visa" />
	<input type="hidden" name="ps_cartao_token" id="ps_cartao_token"/>
	<input type="hidden" name="ps_cartao_hash" id="ps_cartao_hash"/>
	<input type="hidden" name="ps_cartao_valor_parcela" id="ps_cartao_valor_parcela"/>
	<input type="hidden" name="ps_cartao_parcelas" id="ps_cartao_parcelas" />
	<input type="hidden" name="max_parcelas" id="max_parcelas" value="{$max_parcelas}" />
	<input type="hidden" name="parcelas_sem_juros" id="parcelas_sem_juros" value="{$parcelas_sem_juros}" />
	<input type="hidden" name="parcela_minima" id="parcela_minima" value="{$parcela_minima}" />
	<div id="card_show" class="col-xs-12 col-sm-6 pull-right nopadding-left" align="center">
		<div id="card_wrapper" class="nofloat">
			<div id="card_container">
				<div id="number_card" class="card-number anonymous">&bull;&bull;&bull;&bull;&nbsp; &bull;&bull;&bull;&bull;&nbsp; &bull;&bull;&bull;&bull;&nbsp; &bull;&bull;&bull;&bull;</div>
				<div class="card-name">{l s='TITULAR DO CARTÃO' mod='pagseguropro'}</div>
				<div class="card-expiry"><span class="card-expiry-month">&bull; &bull;</span> / <span class="card-expiry-year">&bull; &bull;</span></div>
				<div class="card-brand"></div>
				<span class="card-cvv">&bull;&bull;&bull;</span>
			</div>
		</div>
	</div>
	<fieldset class="pagamento col-xs-12 col-sm-6 pull-left">
		<div class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_name">{l s='Titular do cartão:' mod='pagseguropro'}</label>
			<div class="input-group col-xs-12">
				<input name="card_name" data-validate="isName" class="form-control" type="text" id="card_name" value="{if (isset($senderName) && $senderName)}{$senderName}{/if}" size="30" onBlur="sendToCard(this.value, 'card-name');" />
			</div>
		</div>
		<div class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_doc">{l s='CPF:' mod='pagseguropro'}</label>
			<div class="input-group col-xs-12">
				<input id="card_doc" class="form-control" name="card_doc" {if $device == 'm'}type="tel"{else}type="text"{/if} maxlength="19" onFocus="sendToCard($('#card_name').val(), 'card-name');" onblur="verifica('card_doc');$('#boleto_doc, #transf_doc').val(this.value);" value="{if (isset($cpf) && $cpf)}{$cpf}{/if}" size="30" placeholder="{l s='Somente números' mod='pagseguropro'}" />
			</div>
		</div>
		<div class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_doc">{l s='Data de Nascimento:' mod='pagseguropro'}</label>
			<div class="input-group col-xs-12">
				<input id="card_birth" class="form-control" name="card_birth" {if $device == 'm'}type="tel"{else}type="text"{/if} onkeypress="mascara(this,data)" maxlength="10" placeholder="{l s='DD/MM/AAAA' mod='pagseguropro'}" value="{if (isset($birthday) && $birthday)}{$birthday}{/if}" />
			</div>
		</div>
		<div class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_phone">{l s='Telefone de contato:' mod='pagseguropro'}</label>
			<div class="input-group col-xs-12">
				<input id="card_phone" class="form-control" name="card_phone" {if $device == 'm'}type="tel"{else}type="text"{/if} maxlength="15" onkeypress="mascara(this,telefone)" onblur="validarTel('card_phone');mascara(this,telefone);$('#boleto_phone, #transf_phone').val(this.value);" value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
			</div>
		</div>
		<div class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_number">{l s='Número do cartão:' mod='pagseguropro'}</label>
			<div class="input-group col-xs-12">
				<input id="card_number" class="form-control" name="card_number" {if $device == 'm'}type="tel"{else}type="text"{/if} maxlength="16" min="0" onKeyDown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" onFocus="sendToCard($('#card_name').val(), 'card-name');" autocomplete="off" onpaste="return false" value="" onBlur="valCartao();if(this.value.length > 14) sendToCard(this.value, 'card-number');" size="16" />
				<div id="credit-icon" class="input-group-addon">
					<i class="icon icon-credit-card fa fa-credit-card"></i>
				</div>
			</div>
		</div>
		<div class="form-group clearfix row text-left">
			<div class="col-xs-8 nopadding">
				<label class="col-xs-12 text-left">{l s='Validade:' mod='pagseguropro'}</label>
				<div class="text-right col-xs-5 nopadding-right">
					{assign var=exp_months value=array('01','02','03','04','05','06','07','08','09','10','11','12')}
					<select id="card_month" name="card_month" class="number form-control" onChange="sendToCard(this.value, 'card-expiry-month');">
						<option value=""> -- </option>
						{foreach from=$exp_months key=k item=month}
							<option value="{$month}">{$month}&nbsp;</option>
						{/foreach}
					</select>
				</div>
				<div class="text-center col-xs-1 nopadding">/</div>
				<div class="text-right col-xs-5 nopadding-left">
					<select id="card_year" name="card_year" class="number form-control" onFocus="sendToCard($('#card_month').val(), 'card-expiry-month');" onChange="sendToCard(this.value.slice(-2), 'card-expiry-year');" onBlur="sendToCard(this.value.slice(-2), 'card-expiry-year');">
						<option value=""> -- </option>
						{assign var=this_year value={$smarty.now|date_format:"%Y"}}
						{for $ano=$this_year to $this_year+15}
							<option value="{$ano}">{$ano|substr:-2}</option>
						{/for}
					</select>
				</div>
			</div>
			<div class="form-group col-xs-4 nopadding text-left">
				<label class="text-left col-xs-12" for="card_cvv">{l s='CVV:' mod='pagseguropro'}</label>
				<div class="input-group col-xs-12">
					<input id="card_cvv" class="form-control" name="card_cvv" maxlength="4" {if $device == 'm'}type="tel"{else}type="text"{/if} min="0" onKeyDown="javascript:if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" onpaste="return false" autocomplete="off" value="" onFocus="toggleVerso('add');" onblur="checkCVV();toggleVerso('remove');" onKeyUp="sendToCard(this.value, 'card-cvv');" size="20" />
				</div>
			</div>
		</div>
		<div id="parcelamento" class="form-group clearfix row text-left" align="center">
			<label class="text-left col-xs-12" for="card_inst">{l s='Parcelas:' mod='pagseguropro'}</label>
			<div class="col-xs-12 col-sm-10 col-md-8">
				<select id="card_inst" name="card_inst" class="number form-control" onchange="ps_informarParcela(this.id);">
					<option value="">--</option>
				</select>
			</div>
		</div>
	</fieldset>
	<div class="col-xs-12 clearfix">
		<br />
		<button type="button" class="btn btn-info" data-toggle="collapse" data-target="#ps_endereco">
			{l s='Confirmar endereço do titular' mod='pagseguropro'}
		</button>
	</div>
	<fieldset class="col-xs-12 collapse clearfix" id="ps_endereco">
		<div class="panel panel-info">
			{*<div class="panel-heading">{l s='Endereço de cobrança' mod='pagseguropro'}</div>*}
			<div class="panel-body">
				<div class="col-xs-12 col-sm-6 pull-left nopadding">
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_cep_cobranca" class="text-left col-xs-12">{l s='CEP:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control" type="text" name="ps_cartao_cep_cobranca" id="ps_cartao_cep_cobranca" autocomplete="off" maxlength="9" value="{if isset($address_invoice->postcode)}{$address_invoice->postcode}{/if}">
						</div>
					</div>
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_endereco_cobranca" class="text-left col-xs-12">{l s='Endereço:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control text-left col-xs-12" type="text" name="ps_cartao_endereco_cobranca" id="ps_cartao_endereco_cobranca" autocomplete="off" maxlength="80" value="{if isset($address_invoice->address1)}{$address_invoice->address1}{/if}">
						</div>
					</div>
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_numero_cobranca" class="text-left col-xs-12">{l s='Número:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control" type="text" name="ps_cartao_numero_cobranca" id="ps_cartao_numero_cobranca" autocomplete="off" maxlength="10" value="{if isset($number_invoice)}{$number_invoice}{/if}">
						</div>
					</div>
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_complemento_cobranca" class="text-left col-xs-12">{l s='Complemento:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control" type="text" name="ps_cartao_complemento_cobranca" id="ps_cartao_complemento_cobranca" autocomplete="off" maxlength="40" value="{if isset($compl_invoice)}{$compl_invoice}{/if}">
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 pull-left nopadding">
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_bairro_cobranca" class="text-left col-xs-12">{l s='Bairro:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control text-left col-xs-12" type="text" name="ps_cartao_bairro_cobranca" id="ps_cartao_bairro_cobranca" autocomplete="off" maxlength="60" value="{if isset($address_invoice->address2)}{$address_invoice->address2}{/if}">
						</div>
					</div>
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_cidade_cobranca" class="text-left col-xs-12">{l s='Cidade:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<input class="form-control text-left col-xs-12" type="text" name="ps_cartao_cidade_cobranca" id="ps_cartao_cidade_cobranca" autocomplete="off" maxlength="60" value="{if isset($address_invoice->city)}{$address_invoice->city}{/if}">
						</div>
					</div>
					<div class="form-group clearfix text-left">
						<label for="ps_cartao_uf_cobranca" class="text-left col-xs-12">{l s='Estado:' mod='pagseguropro'}</label>
						<div class="input-group col-xs-12">
							<select id="ps_cartao_uf_cobranca" name="ps_cartao_uf_cobranca" class="form-control">
								<option value=""> -- </option>
								{foreach from=$states item=state name=uf}
									<option value="{$state.iso_code}" {if (isset($address_invoice->id_state) && $address_invoice->id_state == $state.id_state)}selected="selected"{/if}>{$state.iso_code}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</fieldset>
	<div class="clear clearfix"></div>
	<p class="cart_navigation clearfix col-xs-12">
		<button id="submitCard" type="button" name="submitCard" class="btn btn-success btn-lg hideOnSubmit">
			{l s='Pagar' mod='pagseguropro'} &nbsp;<i class="icon icon-check fa fa-check"></i>
		</button>
	</p>
</form>
