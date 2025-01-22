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

<div class="panel">
	<div class="row pagbanklogs-header">
		<div class="col-xs-3 col-md-3 text-left">
			<a href="https://pagseguro.uol.com.br//" title="PagBank" target="_blank">
				<img src="../modules/pagbank/img/logo_pagbank.png" class="img-responsive" />
			</a>
		</div>
		<div class="col-xs-6 col-md-6 text-left">
			<h2 style="padding-left:30px;">{l s='Ver Log' mod='pagbank'}</h2>
		</div>
	</div>
</div>
<div class="panel">
	<div class="panel-heading">
		({$log->id_log}) {l s='Data:' mod='pagbank'} <b>{$log->datetime|date_format:'%d/%m/%Y %H:%M:%S'}</b> -
		{l s='Tipo: ' mod='pagbank'} <b>{$log->type}</b> - {l s='Método: ' mod='pagbank'} <b>{$log->method}</b>
	</div>
	<div class="panel-body">
		{if isset($log->id_cart) && $log->id_cart > 0}
			<div class="box row clearfix">
				<h3>{l s='ID do Carrinho' mod='pagbank'}: <a
						href="{$link->getAdminLink('AdminCarts')}&id_cart={$log->id_cart}&viewcart" id="id_cart"
						target="_blank">{$log->id_cart}</a></h3>
			</div>
			<br />
		{/if}
		{if isset($url) && $url != ''}
			<div class="box row clearfix">
				<h3>{l s='URL Chamada' mod='pagbank'}</h3>
				<pre id="url-called">{$url|escape:'htmlall':'UTF-8'}</pre>
			</div>
			<br />
		{/if}
		{if $log->method != 'callback'}
			<div class="box row clearfix">
				<h3>{l s='Dados enviados' mod='pagbank'}</h3>
				<pre id="sent-data" class="data_format">{$data|escape:'htmlall':'UTF-8'}</pre>
			</div>
			<br /><br />
		{/if}
		<div class="box row clearfix">
			<h3>{l s='Resposta da API' mod='pagbank'}</h3>
			<pre id="response-data" class="data_format">{$response|escape:'htmlall':'UTF-8'}</pre>
		</div>
	</div>
	<div class="panel-footer">
		<a class="btn btn-default" href="{$back_link}" title="{l s='Back to logs list' mod='pagbank'}">
			<i class="process-icon-back"></i>
			{l s='Voltar para lista de LOGs' mod='pagbank'}
		</a>
	</div>
</div>