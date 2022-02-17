{*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<div id="ps_confirmacao" class="container">
	{if isset($ps_link_boleto) && $ps_link_boleto}
		<h3 class="boleto"><span> {l s='Pagamento pendente' d='Modules.PagSeguroPro.Shop'}</span></h3>
	{elseif isset($ps_link_transf) && $ps_link_transf}
		<h3 class="transfer"><span> {l s='Pagamento pendente' d='Modules.PagSeguroPro.Shop'}</span></h3>
	{else}
		{if $info.status == 1}
			<h3 class="status_{$info.status}"><span>{l s='Pagamento em processamento' d='Modules.PagSeguroPro.Shop'}</span></h3>
		{elseif $info.status == 2}
			<h3 class="status_{$info.status}"><span>{l s='Pagamento em análise' d='Modules.PagSeguroPro.Shop'}</span></h3>
		{elseif $info.status == 3}
			<h3 class="status_{$info.status}"><span>{l s='Pagamento efetuado com sucesso' d='Modules.PagSeguroPro.Shop'}</span></h3>
		{elseif $info.status == 7}
			<h3 class="status_{$info.status}"><span>{l s='Pedido cancelado!' d='Modules.PagSeguroPro.Shop'}</span></h3>
		{else}
			<h3 class="status_{$info.status}"><span>{l s='Pedido realizado' d='Modules.PagSeguroPro.Shop'}</span></h3>
		{/if}
	{/if}
	<div class="content clearfix status_{$info.status}">
		<p>{l s='Você receberá um e-mail com todos os detalhes do seu pedido.' d='Modules.PagSeguroPro.Shop'}</p>
		{if isset($ps_link_boleto) && $ps_link_boleto}
			<p>
				<a class="btn btn-lg btn-success" href="{$ps_link_boleto}" id="btnBoleto" title="{l s='Imprimir o boleto' d='Modules.PagSeguroPro.Shop'}" target="_blank">
					<i class="icon icon-barcode"></i>
					{l s='Clique para imprimir o boleto' d='Modules.PagSeguroPro.Shop'}
				</a>
			</p>
			<p class="alert alert-warning">
				{l s='Não esqueça de pagar seu boleto o mais rápido possível.' d='Modules.PagSeguroPro.Shop'}<br>
				{l s='Seu pedido só será processado após a confirmação do pagamento.' d='Modules.PagSeguroPro.Shop'}
			</p>
		{elseif isset($ps_link_transf) && $ps_link_transf}
			<p>
				<a class="btn btn-lg btn-success" type="button" name="btnTransf" id="btnTransf" href="{$ps_link_transf}" title="{l s='Efetuar Transferência' d='Modules.PagSeguroPro.Shop'}">
					<i class="icon icon-bank"></i>
					{l s='Clique para efetuar a transferência bancária' d='Modules.PagSeguroPro.Shop'}
				</a>
			</p>
			<p class="alert alert-warning">
				{l s='Não esqueça de efetivar a transferência o mais rápido possível.' d='Modules.PagSeguroPro.Shop'}<br>
				{l s='Seu pedido só será processado após a confirmação do pagamento.' d='Modules.PagSeguroPro.Shop'}
			</p>
        {/if}
		<br />
	
		<div class="nopadding col-xs-8 col-sm-9 pull-left">
			<h4 class="pull-left">{l s='Abaixo os dados referente ao seu pagamento:' d='Modules.PagSeguroPro.Shop'}</h4>
		</div>
		<div class="nopadding col-xs-3 col-sm-2 pull-right">
			<img class="pagseguro-logo pull-right " src="{$url_img}logo_pagseguro_mini.png" />
		</div>
		<div class="clearfix"><br></div>
		<ul class="list clearfix status_{$info.status}">
			<li><b>Código da transação:</b> {$ps_cod_transacao}</li>
			<li><b>Número do pedido:</b> {$ps_pedido}</li>
			<li><b>Referência do pedido:</b> {$ps_referencia}</li>
			<li><b>Valor do pedido:</b> {$ps_valor}</li>
			<li class="status_{$info.status}"><b>Status:</b> {$info.status} - {$info.desc_status}</li>
		</ul>
		{if $info.status == 7}
			<div class="alert alert-warning">
				<p>{l s='Houve um problema com o seu pedido.' d='Modules.PagSeguroPro.Shop'}</p>
				<p>{l s='Recomendamos que confira os dados informados e tente novamente, clicando no botão abaixo' d='Modules.PagSeguroPro.Shop'}</p> 
				<p align="center"><a class="btn btn-lg btn-info" href="{$link->getPageLink('order')}?submitReorder=1&id_order={$ps_pedido}" title="{l s='Refazer pedido' d='Modules.PagSeguroPro.Shop'}">{l s='Refazer pedido' d='Modules.PagSeguroPro.Shop'}</a></p>
			</div>
			<script type="text/javascript">
				window.onload = function(){
					setTimeout(function() {
						location.reload(true);
					},10000);
				}
			</script>
		{/if}
	</div>
	{if isset($ps_link_boleto) && $ps_link_boleto}
	{literal}
	<script type="text/javascript">
	    	window.onload = function(){
		     var PaymentWindow = window.open("{/literal}{$ps_link_boleto}{literal}", "Boleto para Pagamento", "toolbar=no,scrollbars=yes,resizable=yes,top=100,left=700,width=700,height=500");
		     PaymentWindow.focus();
		}
    	</script>
	{/literal}
	{elseif isset($ps_link_transf) && $ps_link_transf}
	{literal}
	<script type="text/javascript">
	    	window.onload = function(){
		     var PaymentWindow = window.open("{/literal}{$ps_link_transf}{literal}", "Boleto para Pagamento", "toolbar=no,scrollbars=yes,resizable=yes,top=100,left=700,width=700,height=500");
		     PaymentWindow.focus();
		}
    	</script>
	{/literal}
	{/if}
    </div>
