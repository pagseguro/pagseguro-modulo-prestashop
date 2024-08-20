{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Pix, Boleto e Cartão de Crédito
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2024 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2024 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

<div class="pagbank">
	<div class="row">
		<div class="panel card col-xs-12 col-sm-6">
			<div class="panel-heading card-header">
				{l s='Dados Cadastrais' mod='pagbank'}
			</div>
			<div class="panel-body card-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='Id:' mod='pagbank'}</b> <span>{$customer->id}</span></li>
					<li><b>{l s='Nome:' mod='pagbank'}</b> <span>{$customer->firstname} {$customer->lastname}</span>
					</li>
					<li><b>{l s='CPF/CNPJ:' mod='pagbank'}</b> <span>{$info.cpf_cnpj}</span></li>
					<li><b>{l s='E-mail:' mod='pagbank'}</b> <span>{$customer->email}</span></li>
					<li><b>{l s='Data de nascimento:' mod='pagbank'} </b>
						<span>
							{if $customer->birthday != '0000-00-00'}
								{$customer->birthday|date_format:'%d/%m/%Y'}
							{/if}</span>
					</li>
					<li><b>{l s='Data cadastro:' mod='pagbank'}</b>
						<span>{$customer->date_add|date_format:'%d/%m/%Y %H:%M'}</span></li>
					<li><b>{l s='Newsletter:' mod='pagbank'}</b> <span>
							{if $customer->newsletter == 0}
								Não
							{else}
								Sim (desde: {$customer->newsletter_date_add|date_format:'%d/%m/%Y'})
							{/if}</span>
					</li>
				</ul>
			</div>
		</div>
		<div class="panel card col-xs-12 col-sm-6">
			<div class="panel-heading card-header">
				{l s='Endereço de entrega:' mod='pagbank'} {$customer_address->alias}
			</div>
			<div class="panel-body card-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='CEP:' mod='pagbank'}</b> <span>{$customer_address->postcode}</span></li>
					<li><b>{l s='Endereço:' mod='pagbank'}</b> <span>{$customer_address->address1}</span></li>
					<li><b>{l s='Número:' mod='pagbank'}</b> <span>{$customer_address->{$number_field}}</span></li>
					<li><b>{l s='Complemento:' mod='pagbank'}</b> <span>{$customer_address->{$compl_field}}</span></li>
					<li><b>{l s='Bairro:' mod='pagbank'}</b> <span>{$customer_address->address2}</span></li>
					<li><b>{l s='Cidade:' mod='pagbank'}</b> <span>{$customer_address->city}</span></li>
					<li><b>{l s='Estado:' mod='pagbank'}</b> <span>{$customer_address->uf}</span></li>
					<li><b>{l s='Pais:' mod='pagbank'}</b> <span>{$customer_address->country}</span></li>
					<li><b>{l s='Telefone:' mod='pagbank'}</b> <span>{$customer_address->phone}</span></li>
					<li><b>{l s='Celular:' mod='pagbank'}</b> <span>{$customer_address->phone_mobile}</span></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="panel">
			<div class="panel-heading card-header">
				{l s='Dados do Pedido - Loja:' mod='pagbank'} {$order->id} ({$order->reference})
			</div>
			<div class="panel-body card-body nopadding">
				<ul class="list list-unstyled">
					<li><b>{l s='Data do pedido:' mod='pagbank'}</b>
						<span>{$order->date_add|date_format:'%d/%m/%Y %H:%M'}</span></li>
					<li><b>{l s='Data do pagamento:' mod='pagbank'}</b> <span>
							{if $order->invoice_date == '0000-00-00 00:00:00'}
								Pendente
							{else}
								{$order->invoice_date|date_format:'%d/%m/%Y %H:%M'}
							{/if}</span>
					</li>
					<li><b>{l s='Forma de pagamento:' mod='pagbank'}</b> <span>{$order->payment}</span></li>
					<li><b>{l s='Transportadora:' mod='pagbank'}</b> <span>{$order->carrier_name}</span></li>
					<li><b>{l s='Total produtos:' mod='pagbank'}</b>
						<span>{displayPrice price=$order->total_products_wt currency=$order->id_currency}</span></li>
					<li><b>{l s='Total frete:' mod='pagbank'}</b>
						<span>{displayPrice price=$order->total_shipping currency=$order->id_currency}</span></li>
					<li><b>{l s='Total embalagem:' mod='pagbank'}</b>
						<span>{displayPrice price=$order->total_wrapping currency=$order->id_currency}</span></li>
					<li><b>{l s='Total descontos:' mod='pagbank'}</b>
						<span>{displayPrice price=$order->total_discounts currency=$order->id_currency}</span></li>
					<li><b>{l s='Total pedido:' mod='pagbank'}</b>
						<span>{displayPrice price=$order->total_paid currency=$order->id_currency}</span></li>
				</ul>
				<div class="panel">
					<div class="panel-heading card-header">
						{l s='Produtos' mod='pagbank'}
					</div>
					<div class="panel-body card-body nopadding">
						<table class="table table-striped table-hover">
							<thead>
								<th>{l s='Id' mod='pagbank'}</th>
								<th>{l s='Nome' mod='pagbank'}</th>
								<th class="text-center">{l s='Quantidade' mod='pagbank'}</th>
								<th class="text-right">{l s='Valor unitário' mod='pagbank'}</th>
								<th class="text-right">{l s='Valor total' mod='pagbank'}</th>
							</thead>
							<tbody>
								{foreach $order->getProducts() as $produto}
									<tr>
										<td>{$produto.product_id}</td>
										<td>{$produto.product_name}</td>
										<td class="text-center">{$produto.product_quantity}</td>
										<td class="text-right">
											{displayPrice price=$produto.unit_price_tax_incl currency=$order->id_currency}
										</td>
										<td class="text-right">
											{displayPrice price=$produto.total_price_tax_incl currency=$order->id_currency}
										</td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	{if $info.refund > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com estorno Parcial ou Total no PagBank. Valor do último estorno: R$' mod='pagbank'}
				{$info.refund}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagBank, no menu, "Extratos e Relatórios > Extrato de Transações".' mod='pagbank'}
			</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' mod='pagbank'}
			</p>
			<p>{l s='Link de acesso:' mod='pagbank'} <a
					href="https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes"
					target="_blank">https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes</a></p>
		</div>
	{/if}
	<div class="panel card panel-info">
		<div class="panel-heading card-header">
			{l s='Dados do Pedido - PagBank:' mod='pagbank'}
		</div>
		<div class="panel-body card-body nopadding">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<ul class="list list-unstyled">
						<li><b>{l s='Data do pedido:' mod='pagbank'}</b> <span>{$transaction->created_at}</span></li>
						<li>
							<b>{l s='Código no PagBank:' mod='pagbank'}</b>
							{if (isset($transaction->charges))}
								<span>{$transaction->charges[0]->id|replace:"CHAR_":""}</span>
							{else}
								<span>{$transaction->id}</span>
							{/if}
						</li>
						{if (isset($transaction->reference_id))}
							<li><b>{l s='Referência:' mod='pagbank'}</b> <span>{$transaction->reference_id}</span></li>
						{/if}
						<li><b>{l s='Status:' mod='pagbank'}</b> <span>{$desc_status}</span></li>
						<li><b>{l s='Forma de Pagamento:' mod='pagbank'}</b> <span>{$payment_description}</span></li>
						{if isset($transaction->charges) && isset($transaction->charges[0]->payment_method->installments)}
							<li>
								<b>{l s='Quantidade de parcelas:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_method->installments}</span><br />
								<b>{l s='NSU:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_response->raw_data->nsu}</span><br />
								<b>{if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}{l s='Total c/ juros:' mod='pagbank'}{else}{l s='Total s/ juros:' mod='pagbank'}{/if}</b> <span>{displayPrice price=($transaction->charges[0]->amount->value/100) currency=$order->id_currency}</span>
							</li>
						{/if}
						{if isset($transaction->qr_codes[0]) && $transaction->qr_codes[0]->arrangements[0] == 'PIX'}
							<li><b>{l s='Link do PIX:' mod='pagbank'}</b> <span>{$transaction->qr_codes[0]->text}</span></li>
						{/if}
						{if (isset($transaction->charges) && $transaction->charges[0]->payment_method->type == 'BOLETO')}
							<li>
								<b>{l s='Link do Boleto:' mod='pagbank'}</b>
								{foreach from=$transaction->charges[0]->links item="link" name="link"}
									{if ($link->media == 'application/pdf')}
										<span>
											<a href="{$link->href}" title="{l s='Link do Boleto' mod='pagbank'}" target="_blank">
												{$link->href}
											</a>
										</span>
									{/if}
								{/foreach}
							</li>
						{/if}
					</ul>
					<ul class="list list-unstyled">
						<li><b>{l s='Cliente:' mod='pagbank'}</b> <span>{$transaction->customer->name}</span></li>
						<li><b>{l s='E-mail:' mod='pagbank'}</b> <span>{$transaction->customer->email}</span></li>
						<li><b>{l s='Telefone:' mod='pagbank'}</b> <span>({$transaction->customer->phones[0]->area})
								{$transaction->customer->phones[0]->number}</span></li>
						<li><b>{l s='CPF/CNPJ:'}</b> <span>{$transaction->customer->tax_id}</span></li>
					</ul>
					<ul class="list list-unstyled">
						<li><b>{l s='Endereço:' mod='pagbank'}</b>
							<span>{$transaction->shipping->address->street}</span></li>
						<li><b>{l s='Número' mod='pagbank'}</b> <span>{$transaction->shipping->address->number}</span>
						</li>
						<li><b>{l s='Complemento:' mod='pagbank'}</b>
							<span>{$transaction->shipping->address->complement}</span></li>
						<li><b>{l s='Bairro:' mod='pagbank'}</b>
							<span>{$transaction->shipping->address->locality}</span></li>
						<li><b>{l s='Cidade:' mod='pagbank'}</b> <span>{$transaction->shipping->address->city}</span>
						</li>
						<li><b>{l s='UF:' mod='pagbank'}</b> <span>{$transaction->shipping->address->region_code}</span>
						</li>
						<li><b>{l s='País:' mod='pagbank'}</b> <span>{$transaction->shipping->address->country}</span>
						</li>
						<li><b>{l s='CEP:' mod='pagbank'}</b>
							<span>{$transaction->shipping->address->postal_code	}</span></li>
					</ul>
					<br />
				</div>
				<div class="col-xs-12 col-sm-6">
					<h4>{l s='Resumo do pedido' mod='pagbank'} ({$transaction->items|count}
						{l s='produtos' mod='pagbank'})</h4>
					<table class="table table-responsive table-striped">
						<thead>
							<th colspan="2">{l s='Produto' mod='pagbank'}</th>
							<th>{l s='Qtd' mod='pagbank'}</th>
							<th class="text-right">{l s='Total' mod='pagbank'}</th>
						</thead>
						<tbody>
							{foreach from=$transaction->items item="product" name="prod"}
								<tr>
									<td>{$product->reference_id}</td>
									<td>{$product->name}</td>
									<td>{$product->quantity}</td>
									<td class="text-right">
										{displayPrice price=($product->unit_amount/100) currency=$order->id_currency}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					<br />
				</div>
			</div>
			<div class="panel-footer">
				{if (in_array($status, ['AUTHORIZED', 'PAID', 'AVAILABLE', 'DISPUTE']))}
					<div class="alert alert-danger clearfix text-xs-center mt-2 mb-2">
						<p> 
							{l s='Se por algum motivo for necessário estornar um valor parcial ou total, informe o valor no campo abaixo:' mod='pagbank'}
						</p>
					</div>
					<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post"
						class="form-horizontal form-inline well container" onsubmit="return checkRefundValue()">
						<div class="form-group">
							<label class="control-label col-xs-6">{l s='Valor a ser estornado:' mod='pagbank'}
							</label>
							<div class="input-group col-xs-6 ml-3">
								<input type="text" name="refundValue" id="refundValue" class="input form-control" value=""
									max="{$transaction->charges[0]->amount->value/100}" onkeypress="mascara(this,valormask)"
									placeholder="{displayPrice price=($transaction->charges[0]->amount->value/100)} {if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}(c/ juros){else}(s/ juros){/if}" />
							</div>
						</div>
						<div class="input-group submit ml-3">
							<button name="refundOrderPagBank" type="submit" class="btn btn-danger btn-lg"
								onclick="return confirm('{l s='Tem certeza que deseja estornar a transação no PagBank e devolver o valor ao comprador?' mod='pagbank' js=1}');">
								{l s='Estornar Transação no PagBank' mod='pagbank'}
							</button>
						</div>
					</form>
				{/if}
			</div>
		</div>
	</div>
	<div class="clearfix">
		<a class="btn btn-default" href="{$back_link}" title="{l s='Back to logs list' mod='pagbank'}">
			<i class="process-icon-back"></i>
			{l s='Voltar para lista de Transações' mod='pagbank'}
		</a>
	</div>
</div>