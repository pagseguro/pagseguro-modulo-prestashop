{*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

<div class="pagseguropro">
	<div class="row">
		<div class="panel col-xs-12 col-sm-6">
			<div class="panel-heading">
				{l s='Dados Cadastrais' d='Modules.PagSeguroPro.Admin'}
			</div>
			<div class="panel-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='Id:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$cliente->id}</span></li>
					<li><b>{l s='Nome:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$cliente->firstname} {$cliente->lastname}</span></li>
					<li><b>{l s='CPF/CNPJ:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$info['cpf_cnpj']}</span></li>
					<li><b>{l s='E-mail:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$cliente->email}</span></li>
					<li><b>{l s='Data de nascimento:' d='Modules.PagSeguroPro.Admin'} </b>
						<span>
						{if $cliente->birthday != '0000-00-00'}
							{$cliente->birthday|date_format:'%d/%m/%Y'}
						{/if}</span>
					</li>
					<li><b>{l s='Data cadastro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$cliente->date_add|date_format:'%d/%m/%Y %H:%M'}</span></li>
					<li><b>{l s='Newsletter:' d='Modules.PagSeguroPro.Admin'}</b> <span>
						{if $cliente->newsletter == 0}
							Não
						{else}
							Sim (desde: {$cliente->newsletter_date_add|date_format:'%d/%m/%Y'})
						{/if}</span>
					</li>
				</ul>
			</div>
		</div>
		<div class="panel col-xs-12 col-sm-6">
			<div class="panel-heading">
				{l s='Endereço de entrega:' d='Modules.PagSeguroPro.Admin'} {$endereco->alias}
			</div>
			<div class="panel-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='CEP:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->postcode}</span></li>
					<li><b>{l s='Endereço:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->address1}</span></li>
					<li><b>{l s='Número:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->{$number_field}}</span></li>
					<li><b>{l s='Complemento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->{$compl_field}}</span></li>
					<li><b>{l s='Bairro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->address2}</span></li>
					<li><b>{l s='Cidade:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->city}</span></li>
					<li><b>{l s='Estado:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->uf}</span></li>
					<li><b>{l s='Pais:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->pais}</span></li>
					<li><b>{l s='Telefone:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->phone}</span></li>
					<li><b>{l s='Celular:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$endereco->phone_mobile}</span></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="panel">
			<div class="panel-heading">
				{l s='Dados do Pedido (Loja):' d='Modules.PagSeguroPro.Admin'} {$pedido->id} ({$pedido->reference})
			</div>
			<div class="panel-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='Data do pedido:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$pedido->date_add|date_format:'%d/%m/%Y %H:%M'}</span></li>
					<li><b>{l s='Data do pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>
						{if $pedido->invoice_date == '0000-00-00 00:00:00'}
							Pendente
						{else}
							{$pedido->invoice_date|date_format:'%d/%m/%Y %H:%M'}
						{/if}</span>
					</li>
					<li><b>{l s='Forma de pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$pedido->payment}</span></li>
					<li><b>{l s='Transportadora:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$pedido->carrier_name}</span></li>
					<li><b>{l s='Total produtos:' d='Modules.PagSeguroPro.Admin'}</b> <span>{displayPrice price=$pedido->total_products_wt currency=$pedido->id_currency}</span></li>
					<li><b>{l s='Total frete:' d='Modules.PagSeguroPro.Admin'}</b> <span>{displayPrice price=$pedido->total_shipping currency=$pedido->id_currency}</span></li>
					<li><b>{l s='Total embalagem:' d='Modules.PagSeguroPro.Admin'}</b> <span>{displayPrice price=$pedido->total_wrapping currency=$pedido->id_currency}</span></li>
					<li><b>{l s='Total descontos:' d='Modules.PagSeguroPro.Admin'}</b> <span>{displayPrice price=$pedido->total_discounts currency=$pedido->id_currency}</span></li>
					<li><b>{l s='Total pedido:' d='Modules.PagSeguroPro.Admin'}</b> <span>{displayPrice price=$pedido->total_paid currency=$pedido->id_currency}</span></li>
					<li class="hidden"><pre>{$pedido|print_r}</pre></li>
				</ul>
				<div class="panel">
					<div class="panel-heading">
						{l s='Produtos' d='Modules.PagSeguroPro.Admin'}
					</div>
					<div class="panel-body nopadding">
						<table class="table table-striped table-hover">
							<thead>
								<th>{l s='Id' d='Modules.PagSeguroPro.Admin'}</th>
								<th>{l s='Nome' d='Modules.PagSeguroPro.Admin'}</th>
								<th class='text-center'>{l s='Quantidade' d='Modules.PagSeguroPro.Admin'}</th>
								<th class='text-right'>{l s='Valor unitário' d='Modules.PagSeguroPro.Admin'}</th>
								<th class='text-right'>{l s='Valor total' d='Modules.PagSeguroPro.Admin'}</th>
							</thead>
							<tbody>
							{foreach $pedido->getProducts() as $produto}
								<tr>
									<td>{$produto.product_id}</td>
									<td>{$produto.product_name}</td>
									<td class='text-center'>{$produto.product_quantity}</td>
									<td class='text-right'>{displayPrice price=$produto.unit_price_tax_incl currency=$pedido->id_currency}</td>
									<td class='text-right'>{displayPrice price=$produto.total_price_tax_incl currency=$pedido->id_currency}</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	{if $info['refund'] > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com estorno Parcial ou Total no PagSeguro. Valor do último estorno: R$' d='Modules.PagSeguroPro.Admin'} {$info['refund']}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagSeguro, no menu, "Extratos e Relatórios > Extrato de Transações".' d='Modules.PagSeguroPro.Admin'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' d='Modules.PagSeguroPro.Admin'}</p>
			<p>{l s='Link de acesso:' d='Modules.PagSeguroPro.Admin'} <a href="https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes" target="_blank">https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes</a></p>
		</div>
	{/if}
	<div class="panel panel-info">
	    <div class="panel-heading">
            {l s='Dados do Pedido (PagSeguro):' d='Modules.PagSeguroPro.Admin'}
	    </div>
        <div class="panel-body nopadding">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<ul class="list list-unstyled">
						<li><b>{l s='Data do pedido:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->date|date_format:'%d/%m/%Y %H:%M'}</span></li>
						<li><b>{l s='Código no PagSeguro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->code}</span></li>
						<li><b>{l s='Referência:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->reference}</span></li>
						<li><b>{l s='Status:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->status} - {$status}</span></li>
						<li><b>{l s='Última atualização:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->lastEventDate|date_format:'%d/%m/%Y %H:%M'}</span></li>
						<li><b>{l s='Forma de Pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$formaPagamento} ({$tipoPagamento|replace:"Cartão de crédito ":""})</span></li>
						<li><b>{l s='Link para pagamento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{if $transacao->paymentLink}<u><a href="{$transacao->paymentLink}" target="_blank">{if $formaPagamento|strstr:'Boleto'}{l s='Link do Boleto' mod='pagseguropro'}{else}{l s='Link da Cobrança' mod='pagseguropro'}{/if}</a></u>{else}--{/if}</span></li>
						<li><b>{l s='Quantidade de parcelas:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->installmentCount}</span></li>
					</ul>
					<br />
					<ul class="list list-unstyled">
						<li><b>{l s='Cliente:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->sender->name}</span></li>
						<li><b>{l s='E-mail:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->sender->email}</span></li>
						<li><b>{l s='Telefone:' d='Modules.PagSeguroPro.Admin'}</b> <span>({$transacao->sender->phone->areaCode}) {$transacao->sender->phone->number}</span></li>
						{foreach from=$transacao->sender->documents->document item="document" name="docs"}
							<li><b>{$document->type}</b> <span>{$document->value}</span></li>
						{/foreach}
					</ul>
					<br />
					<ul class="list list-unstyled">
						<li><b>{l s='Endereço:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->street}</span></li>
						<li><b>{l s='Número' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->number}</span></li>
						<li><b>{l s='Complemento:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->complement}</span></li>
						<li><b>{l s='Bairro:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->district}</span></li>
						<li><b>{l s='Cidade:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->city}</span></li>
						<li><b>{l s='UF:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->state}</span></li>
						<li><b>{l s='País:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->country}</span></li>
						<li><b>{l s='CEP:' d='Modules.PagSeguroPro.Admin'}</b> <span>{$transacao->shipping->address->postalCode}</span></li>
					</ul>
					<br />
				</div>
				<div class="col-xs-12 col-sm-6">
					<h4>{l s='Resumo do pedido' d='Modules.PagSeguroPro.Admin'} ({$transacao->itemCount} {l s='produtos' d='Modules.PagSeguroPro.Admin'})</h4>
					<table class="table table-responsive table-striped">
						<thead>
							<th colspan="2">{l s='Produto' d='Modules.PagSeguroPro.Admin'}</th>
							<th>{l s='Qtd' d='Modules.PagSeguroPro.Admin'}</th>
							<th class="text-right">{l s='Total' d='Modules.PagSeguroPro.Admin'}</th>
						</thead>
						<tbody>
						{foreach from=$transacao->items->item item="product" name="prod"}
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
								<td class="price text-right"><span>{displayPrice price=$transacao->discountAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr>
								<td colspan="3"><b>{l s='Frete:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transacao->shipping->cost currency=$pedido->id_currency}</span></td>
							</tr>
							<tr>
								<td colspan="3"><b>{l s='Total do pedido:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transacao->grossAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr class="taxa_pagseguro">
								<td colspan="3"><b>{l s='Taxa de intermediação:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transacao->feeAmount currency=$pedido->id_currency}</span></td>
							</tr>
							<tr class="total">
								<td colspan="3"><b>{l s='Valor líquido:' d='Modules.PagSeguroPro.Admin'}</b> </td>
								<td class="price text-right"><span>{displayPrice price=$transacao->netAmount currency=$pedido->id_currency}</span></td>
							</tr>
						</tfoot>
					</table>
					<br />
				</div>
			</div>
			<pre class="hidden">{$transacao|print_r}</pre>
        </div>
    </div>
	<div class="clearfix">
		<a class="btn btn-default" href="{$back_link}" title="{l s='Back to logs list' mod='stelopro'}">
			<i class="process-icon-back"></i>
			{l s='Voltar para lista de Transações' d='Modules.PagSeguroPro.Admin'}
		</a>
	</div>
</div>
