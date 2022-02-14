{*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<div class="panel">
	<div class="row pagsegurologs-header">
		<div class="col-xs-3 col-md-3 text-left">
			<a href="https://pagseguro.uol.com.br/" title="PagSeguro" target="_blank">
				<img src="../modules/pagseguropro/views/img/logo_pagseguro.png" class="img-responsive" />
			</a>
		</div>
		<div class="col-xs-6  col-md-6 text-left">
			<h2 style="padding-left:30px;">{l s='Ver Log' d='Modules.PagSeguroPro.Admin'}</h2>
		</div>
	</div>
</div>
<div class="panel">
	<div class="panel-heading">({$log->id_log}) {l s='Data:' d='Modules.PagSeguroPro.Admin'} <b>{$log->datetime|date_format:'%d/%m/%Y %H:%M:%S'}</b> - {l s='Tipo: ' d='Modules.PagSeguroPro.Admin'} <b>{$log->type}</b> - {l s='Método: ' d='Modules.PagSeguroPro.Admin'} <b>{$log->method}</b></div>
	<div class="panel-body">
		{if isset($log->id_cart) && $log->id_cart > 0}
			<div class="box row clearfix">
				<h3>{l s='ID do Carrinho' d='Modules.PagSeguroPro.Admin'}: <a href="{$link->getAdminLink('AdminCarts')}&id_cart={$log->id_cart}&viewcart" id="id_cart" target="_blank">{$log->id_cart}</a></h3>
			</div>
			<br><br>
		{/if}
		{if isset($url) && $url != ''}
			<div class="box row clearfix">
				<h3>{l s='URL Chamada' d='Modules.PagSeguroPro.Admin'}</h3>
				<pre id="url-called">{$url|escape:'htmlall':'UTF-8'}</pre>
			</div>
			<br><br>
		{/if}
		{if $log->method != 'callback'}
			<div class="box row clearfix">
				<h3>{l s='Dados enviados' d='Modules.PagSeguroPro.Admin'}</h3>
				<pre id="sent-data" class="data_format">{$data|escape:'htmlall':'UTF-8'}</pre>
			</div>
			<br> <br>
		{/if}
		<div class="box row clearfix">
			<h3>{l s='Resposta da API' d='Modules.PagSeguroPro.Admin'}</h3>
			<pre id="response-data" class="data_format">{$response|escape:'htmlall':'UTF-8'}</pre>
		</div>
	</div>
	<div class="panel-footer">
		<a class="btn btn-default" href="{$back_link}" title="{l s='Back to logs list' d='Modules.PagSeguroPro.Admin'}">
			<i class="process-icon-back"></i>
			{l s='Voltar para lista de LOGs' d='Modules.PagSeguroPro.Admin'}
		</a>
	</div>
</div>
