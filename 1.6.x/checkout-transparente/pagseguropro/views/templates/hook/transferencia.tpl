{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 *}

<div class="container-debito clearfix">
	<br />
	<form id="debito_pagseguropro" name="checkout" action="{$link->getModuleLink('pagseguropro', 'validation', [], true)|escape:'html'}" method="post" target="_top" onsubmit="showLoading();" class="clearfix">
		<input type="hidden" name="ps_tipo" id="ps_tipo" value="transf" />
		<input type="hidden" name="ps_transf_hash" id="ps_transf_hash" />
		<fieldset class="pagamento col-xs-12">
			<input type="hidden" id="payment_type" name="payment_type" value="Debito">
			<div class="clearfix row" align="center">
				<div class="col-xs-12 payments" style="float:none;">
					<img title="Transferência Bancária" src="{$this_path}img/transferencia.png" alt="{l s='Transferência Bancária' mod='pagseguropro'}" ondrag="return false" onselec="return false" oncontextmenu="return false" />
				</div>
				<div class="pagseguroprocontrolaErro text-center" style="display:none;float:none; text-align:center; margin:0 auto; width:200px;"></div>
				<br>
				<div class="form-group clearfix text-center nofloat" align="center">
					<label class="text-center col-xs-12 nofloat" for="transf_doc">{l s='Por favor, informe o CPF/CNPJ:' mod='pagseguropro'}</label>
					<div class="input-group col-xs-11 col-sm-9 col-md-8 col-lg-7 nofloat">
						<input id="transf_doc" class="form-control" name="transf_doc" type="text" maxlength="19" onblur="verifica('transf_doc');$('#boleto_doc, #card_doc').val(this.value);" value="{if (isset($cpf) && $cpf)}{$cpf}{/if}" size="30" placeholder="{l s='Somente números' mod='pagseguropro'}" />
					</div>
				</div>
			    <br>
				<div class="form-group clearfix text-center nofloat" align="center">
					<label class="text-center col-xs-12 nofloat" for="transf_phone">{l s='Telefone de contato:' mod='pagseguropro'}</label>
					<div class="input-group col-xs-11 col-sm-9 col-md-8 col-lg-7 nofloat">
						<input id="transf_phone" class="form-control" name="transf_phone" type="text" maxlength="15" onkeypress="mascara(this,telefone)" onblur="validarTel('transf_phone');mascara(this,telefone);$('#card_phone, #boleto_phone').val(this.value);" value="{if (isset($phone) && $phone)}{$phone}{/if}" placeholder="(99) 99999-9999" />
					</div>
				</div>
				<div class="pagseguropro_transf col-xs-12 clearfix text-center" align="center">
				    <br>
					<div class="form-group col-xs-4 col-sm-2">
						<input type="radio" id="transf_bb" name="ps_transf" value="bancodobrasil">
					    <label for="transf_bb">
    						<img alt="Banco do Brasil" src="{$url_img}bancodobrasil_32.png">
    						<span>{l s='Banco do Brasil' mod='pagseguropro'}</span>
						</label>
					</div>
					<div class="form-group col-xs-4 col-sm-2">
						<input type="radio" id="transf_bradesco" name="ps_transf" value="bradesco">
					    <label for="transf_bradesco">
    						<img alt="Bradesco" src="{$url_img}bradesco_32.png">
    						<span>{l s='Bradesco' mod='pagseguropro'}</span>
    					</label>
					</div>
					<div class="form-group col-xs-4 col-sm-2">
						<input type="radio" id="transf_hsbc" name="ps_transf" value="hsbc">
					    <label for="transf_hsbc">
    						<img alt="HSBC" src="{$url_img}hsbc_32.png">
						    <span>{l s='HSBC' mod='pagseguropro'}</span>
						</label>
					</div>
					<div class="form-group col-xs-4 col-sm-2">
						<input type="radio" id="transf_itau" name="ps_transf" value="itau">
					    <label for="transf_itau">
    						<img alt="Itaú" src="{$url_img}itau_32.png">
						    <span>{l s='Itaú' mod='pagseguropro'}</span>
						</label>
					</div>
					<div class="form-group col-xs-4 col-sm-2">
						<input type="radio" name="ps_transf" id="transf_banrisul" value="banrisul">
					    <label for="transf_banrisul">
    						<img alt="Banrisul" src="{$url_img}banrisul_32.png">
    						<span>{l s='Banrisul' mod='pagseguropro'}</span>
    					</label>
					</div>
				</div>
			</div>
			<p class="cart_navigation clearfix">
				<button id="submitDebito" type="button" name="submitDebito" class="btn btn-success btn-lg hideOnSubmit">
					{l s='Pagar com débito' mod='pagseguropro'}
					<i class="icon icon-check fa fa-check"></i>
				</button>
			</p>
		</fieldset>
	</form>
</div>
