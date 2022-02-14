{*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<div id="pagseguro">
	{if isset($pagseguro_msg) && $pagseguro_msg}
		<div class="alert alert-info">
			<p>{$pagseguro_msg}</p>
		</div>
	{/if}
	{if $info['refund'] > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com estorno Parcial ou Total no PagSeguro. Valor do último estorno: R$' d='Modules.PagSeguroPro.Admin'} {$info['refund']}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagSeguro, no menu, "Extratos e Relatórios > Extrato de Transações".' d='Modules.PagSeguroPro.Admin'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' d='Modules.PagSeguroPro.Admin'}</p>
			<p>{l s='Link de acesso:' d='Modules.PagSeguroPro.Admin'} <a href="https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes" target="_blank">https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes</a></p>
		</div>
	{/if}
	<div class="panel panel-info card">
	    <div class="card-header">
            <h3 class="card-header-title">{l s='Dados do Pedido (PagSeguro):' d='Modules.PagSeguroPro.Admin'}</h3>
	    </div>
        <div class="panel-body card-body">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<pre class="hidden d-none">{$transaction|print_r}</pre>
					<ul class="list list-unstyled">
						<li><b>{l s='Data do pedido:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->date|date_format:'%d/%m/%Y %H:%M'}</span></li>
						<li><b>{l s='Código no PagSeguro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->code}</span></li>
						<li><b>{l s='Referência:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->reference}</span></li>
						<li><b>{l s='Status:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->status} - {$status_pagseguro}</span></li>
						<li><b>{l s='Última atualização:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->lastEventDate|date_format:'%d/%m/%Y %H:%M'}</span></li>
						<li><b>{l s='Forma de Pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$formaPagamento} ({$tipoPagamento|replace:"Cartão de crédito ":""})</span></li>
						<li><b>{l s='Link para pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{if $transaction->paymentLink}<u><a href="{$transaction->paymentLink}" target="_blank">{if $formaPagamento|strstr:'Boleto'}{l s='Link do Boleto' d='Modules.PagSeguroPro.Admin'}{else}{l s='Link da Cobrança' d='Modules.PagSeguroPro.Admin'}{/if}</a></u>{else}--{/if}</span></li>
						<li><b>{l s='Quantidade de parcelas:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->installmentCount}</span></li>
					</ul>
					<br />
					<ul class="list list-unstyled">
						<li><b>{l s='Cliente:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->sender->name}</span></li>
						<li><b>{l s='E-mail:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->sender->email}</span></li>
						<li><b>{l s='Telefone:' d='Modules.PagSeguroPro.Admin'}</b> <span>({$transaction->sender->phone->areaCode}) {$transaction->sender->phone->number}</span></li>
						{foreach from=$transaction->sender->documents->document item="document" name="docs"}
							<li><b>{$document->type}</b> <span>{$document->value}</span></li>
						{/foreach}
					</ul>
					<br />
					<ul class="list list-unstyled">
						<li><b>{l s='Endereço:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->street}</span></li>
						<li><b>{l s='Número' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->number}</span></li>
						<li><b>{l s='Complemento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->complement}</span></li>
						<li><b>{l s='Bairro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->district}</span></li>
						<li><b>{l s='Cidade:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->city}</span></li>
						<li><b>{l s='UF:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->state}</span></li>
						<li><b>{l s='País:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->country}</span></li>
						<li><b>{l s='CEP:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transaction->shipping->address->postalCode}</span></li>
					</ul>
					<br />
				</div>
				<div class="col-xs-12 col-sm-6">
					<h4>{l s='Resumo do pedido' d='Modules.PagSeguroPro.Admin'} ({$transaction->itemCount} {l s='produtos' d='Modules.PagSeguroPro.Admin'})</h4>
					<table class="table table-responsive table-striped d-table">
						<thead>
							<th colspan="2">{l s='Produto' d='Modules.PagSeguroPro.Admin'}</th>
							<th>{l s='Qtd' d='Modules.PagSeguroPro.Admin'}</th>
							<th class="text-right">{l s='Total' d='Modules.PagSeguroPro.Admin'}</th>
						</thead>
						<tbody>
						{foreach from=$transaction->items->item item="product" name="prod"}
							<tr>
								<td>{$product->id}</td>
								<td>{$product->description}</td>
								<td>{$product->quantity}</td>
								<td class="text-right">{displayPrice price=$product->amount currency=$pedido->id_currency}</td>
							</tr>
						{/foreach}
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3"><b>{l s='Descontos:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transaction->discountAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr>
								<td colspan="3"><b>{l s='Frete:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transaction->shipping->cost currency=$pedido->id_currency}</span></td>
							</tr>
							<tr>
								<td colspan="3"><b>{l s='Total do pedido:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transaction->grossAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr class="taxa_pagseguro">
								<td colspan="3"><b>{l s='Taxa de intermediação:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transaction->feeAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr class="total">
								<td colspan="3"><b>{l s='Valor líquido:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transaction->netAmount currency=$pedido->id_currency}</span></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="card-footer">
				{if ($transaction->status < 3)}
					<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post" class="form-horizontal card-body pull-left">
						<button name="cancelarPedidoPagSeguro" type="submit" class="btn btn-danger btn-lg" onclick="return confirm('{l s='Tem certeza que deseja cancelar a transação no PagSeguro?' mod='pagseguropro' js=1}');">
							{l s='Cancelar Transação no PagSeguro' d='Modules.PagSeguroPro.Admin'}
						</button>
					</form>
				{elseif ($transaction->status == 3 || $transaction->status == 4 || $transaction->status == 5)}
					<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post" class="form-horizontal form-inline card-body pull-left">
						<div class="alert alert-danger">
							<p> {l s='O valor total deste pedido no PagSeguro é:' d='Modules.PagSeguroPro.Admin'} <b>R${$transaction->grossAmount}</b></p>
							<p> {l s='Se por algum motivo for necessário estornar um valor parcial ou total, informe o valor no campo abaixo:' d='Modules.PagSeguroPro.Admin'} </p>
						</div>
						<div class="form-group">
							<label class="control-label col-xs-6 col-sm-6">{l s='Valor a ser estornado:' d='Modules.PagSeguroPro.Admin'}</label>
							<div class="input-group col-xs-6 col-sm-6">
								<input type="text" name="refundValue" class="input form-control" value="" placeholder="{$transaction->grossAmount}" />
							</div>
						</div>
						<div class="input-group submit pull-right">
							<button name="estornarPedidoPagSeguro" type="submit" class="btn btn-danger btn-lg" onclick="return confirm('{l s='Tem certeza que deseja estornar a transação no PagSeguro e devolver o valor ao comprador?' mod='pagseguropro' js=1}');">
								{l s='Estornar Transação no PagSeguro' d='Modules.PagSeguroPro.Admin'}
							</button>
						</div>
					</form>
				{/if}
			</div>
			<pre class="hidden d-none">{$info|print_r}</pre>
        </div>
    </div>
</div>
