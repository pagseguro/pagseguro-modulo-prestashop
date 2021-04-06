{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 *}

<div class="panel">
	<div class="row pagsegurologs-header">
		<div class="col-xs-3 col-md-3 text-left">
			<a href="https://pagseguro.uol.com.br//" title="PagSeguro" target="_blank">
				<img src="../modules/pagseguropro/img/logo_pagseguro.png" class="img-responsive" />
			</a>
		</div>
		<div class="col-xs-6  col-md-6 text-left">
			<h2 style="padding-left:30px;">{l s='Ver Log' mod='pagseguropro'}</h2>
		</div>
	</div>
</div>
<div class="panel">
	<div class="panel-heading">({$log->id_log}) {l s='Data:' mod='pagseguropro'} <b>{$log->datetime|date_format:'%d/%m/%Y %H:%M:%S'}</b> - {l s='Tipo: ' mod='pagseguropro'} <b>{$log->type}</b> - {l s='Método: ' mod='pagseguropro'} <b>{$log->method}</b></div>
	<div class="panel-body">
		{if isset($log->id_cart) && $log->id_cart > 0}
			<div class="box row clearfix">
				<h3>{l s='ID do Carrinho' mod='pagseguropro'}: <a href="{$link->getAdminLink('AdminCarts')}&id_cart={$log->id_cart}&viewcart" id="id_cart" target="_blank">{$log->id_cart}</a></h3>
			</div>
			<br><br>
		{/if}
		{if isset($url) && $url != ''}
			<div class="box row clearfix">
				<h3>{l s='URL Chamada' mod='pagseguropro'}</h3>
				<pre id="url-called">{$url|escape:'htmlall':'UTF-8'}</pre>
			</div>
			<br><br>
		{/if}
		<div class="box row clearfix">
			<h3>{l s='Dados enviados' mod='pagseguropro'}</h3>
			<pre id="sent-data" class="data_format">{$data|escape:'htmlall':'UTF-8'}</pre>
		</div>
		<br> <br>
		<div class="box row clearfix">
			<h3>{l s='Resposta da API' mod='pagseguropro'}</h3>
			<pre id="response-data" class="data_format">{$response|escape:'htmlall':'UTF-8'}</pre>
		</div>
	</div>
	<div class="panel-footer">
		<a class="btn btn-default" href="{$back_link}" title="{l s='Back to logs list' mod='pagseguropro'}">
			<i class="process-icon-back"></i>
			{l s='Voltar para lista de LOGs' mod='pagseguropro'}
		</a>
	</div>
</div>
