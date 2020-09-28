{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 *}

<div id="ps_confirmacao" class="container">
	{if isset($ps_link_boleto) && $ps_link_boleto}
		<h3 class="boleto"><span> {l s='Pagamento pendente' mod='pagseguropro'}</span></h3>
	{elseif isset($ps_link_transf) && $ps_link_transf}
		<h3 class="transfer"><span> {l s='Pagamento pendente' mod='pagseguropro'}</span></h3>
	{else}
		{if $info.status == 1}
			{assign var="status_class" value="status_aguardando"}
			<h3 class="{$status_class}"><span>{l s='Pagamento em processamento' mod='pagseguropro'}</span></h3>
		{elseif $info.status == 2}
			{assign var="status_class" value="status_analise"}
			<h3 class="{$status_class}"><span>{l s='Pagamento em análise' mod='pagseguropro'}</span></h3>
		{elseif $info.status == 3}
			{assign var="status_class" value="status_confirmado"}
			<h3 class="{$status_class}"><span>{l s='Pagamento efetuado com sucesso' mod='pagseguropro'}</span></h3>
		{elseif $info.status == 7}
			{assign var="status_class" value="status_cancelado"}
			<h3 class="{$status_class}"><span>{l s='Pedido cancelado!' mod='pagseguropro'}</span></h3>
		{else}
			{assign var="status_class" value="status_aguardando"}
			<h3 class="{$status_class}"><span>{l s='Pedido realizado' mod='pagseguropro'}</span></h3>
		{/if}
	{/if}
	<div class="content clearfix {$status_class}">
		<p>{l s='Você receberá um e-mail com todos os detalhes do seu pedido.' mod='pagseguropro'}</p>
		{if isset($ps_link_boleto) && $ps_link_boleto}
			<p>
				<a class="btn btn-lg btn-success" href="{$ps_link_boleto}" id="btnBoleto" title="{l s='Imprimir o boleto' mod='pagseguropro'}" target="_blank">
					<i class="icon icon-barcode"></i>
					{l s='Clique para imprimir o boleto' mod='pagseguropro'}
				</a>
			</p>
			<p class="alert alert-warning">
				{l s='Não esqueça de pagar seu boleto o mais rápido possível.' mod='pagseguropro'}<br>
				{l s='Seu pedido só será processado após a confirmação do pagamento.' mod='pagseguropro'}
			</p>
		{elseif isset($ps_link_transf) && $ps_link_transf}
			<p>
				<a class="btn btn-lg btn-success" type="button" name="btnTransf" id="btnTransf" href="{$ps_link_transf}" title="{l s='Efetuar Transferência' mod='pagseguropro'}">
					<i class="icon icon-bank"></i>
					{l s='Clique para efetuar a transferência bancária' mod='pagseguropro'}
				</a>
			</p>
			<p class="alert alert-warning">
				{l s='Não esqueça de efetivar a transferência o mais rápido possível.' mod='pagseguropro'}<br>
				{l s='Seu pedido só será processado após a confirmação do pagamento.' mod='pagseguropro'}
			</p>
        {/if}
		<br />
	
		<h4>{l s='Abaixo os dados referente ao seu pagamento:' mod='pagseguropro'}</h4>
		<ul class="list clearfix {$status_class}">
			<li><b>Código da transação:</b> {$ps_cod_transacao}</li>
			<li><b>Número do pedido:</b> {$ps_pedido}</li>
			<li><b>Referência do pedido:</b> {$ps_referencia}</li>
			<li><b>Valor do pedido:</b> {$ps_valor}</li>
			<li class="{$status_class}"><b>Status:</b> {$info.status} - {$info.desc_status}</li>
		</ul>
		{if $info.status != 7}
			<div class="table_container clearfix">
				<pre class="hidden">{$pedido|print_r}</pre>
				<h4>{l s='Resumo do pedido' mod='pagseguropro'}</h4>
				<table class="table">
					<thead>
						<th>{l s='ID' mod='pagseguropro'}</th>
						<th>{l s='Nome' mod='pagseguropro'}</th>
						<th>{l s='Preço' mod='pagseguropro'}</th>
						<th>{l s='Qtd' mod='pagseguropro'}</th>
						<th class="text-right">{l s='Total' mod='pagseguropro'}</th>
					</thead>
					<tbody>
					{foreach from=$produtos item='produto' name='prods'}
						<tr>
							<td>{$produto.product_id}</td>
							<td>{$produto.product_name}</td>
							<td>{displayPrice price=$produto.product_price}</td>
							<td>{$produto.product_quantity}</td>
							<td class="price text-right">{displayPrice price=$produto.total_price_tax_incl}</td>
						</tr>
					{/foreach}
					</tbody>
					<tfoot>
						<tr class="total_prods">
							<td colspan="3">{l s='Total de Produtos' mod='pagseguropro'}</td>
							<td colspan="2" class="price text-right">{displayPrice price=$pedido->total_products}</td>
						</tr>
						{if ($pedido->total_discounts) > 0}
							<tr class="extra">
								<td colspan="3">{l s='Descontos' mod='pagseguropro'}</td>
								<td colspan="2" class="price text-right">{displayPrice price=$pedido->total_discounts}</td>
							</tr>
						{/if}
						{if ($pedido->total_wrapping) > 0}
							<tr class="extra">
								<td colspan="3">{l s='Embalagem de presente' mod='pagseguropro'}</td>
								<td colspan="2" class="price text-right">{displayPrice price=$pedido->total_wrapping}</td>
							</tr>
						{/if}
						<tr class="frete">
							<td colspan="3">{l s='Frete' mod='pagseguropro'}</td>
							<td colspan="2" class="price text-right">{displayPrice price=$pedido->total_shipping}</td>
						</tr>
						<tr class="total">
							<td colspan="3">{l s='Total do Pedido' mod='pagseguropro'}</td>
							<td colspan="2" class="price text-right">{displayPrice price=$pedido->total_paid}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		{else}
			<div class="alert alert-warning">
				<p>{l s='Houve um problema com o seu pedido.' mod='pagseguropro'}</p>
				<p>{l s='Recomendamos que confira os dados informados e tente novamente, clicando no botão abaixo' mod='pagseguropro'}</p> 
				<p align="center"><a class="btn btn-lg btn-info" href="{$link->getPageLink('order')}?submitReorder=1&id_order={$ps_pedido}" title="{l s='Refazer pedido' mod='pagseguropro'}">{l s='Refazer pedido' mod='pagseguropro'}</a></p>
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
