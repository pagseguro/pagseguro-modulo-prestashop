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

<div id="pagbank-order">
	{if isset($pagbank_msg) && $pagbank_msg}
		<div class="alert alert-info">
			<p>{$pagbank_msg}</p>
		</div>
	{/if}
	{if $info['refund'] > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com estorno parcial ou total no PagBank. Valor do último estorno: R$' d='Modules.PagBank.Admin'} {$info['refund']}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagBank, no menu, "Extratos e Relatórios > Extrato de Transações".' d='Modules.PagBank.Admin'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' d='Modules.PagBank.Admin'}</p>
			<p>{l s='Link de acesso:' d='Modules.PagBank.Admin'} <a
					href="https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes"
					target="_blank">https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes</a>
			</p>
		</div>
	{/if}
	{if $info['capture'] == 0 && isset($transaction->charges[0]->amount->summary->paid) && $transaction->charges[0]->amount->summary->paid > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com captura parcial ou total no PagBank. Valor da captura:' d='Modules.PagBank.Admin'} {displayPrice price=($transaction->charges[0]->amount->summary->paid/100) currency=$order->id_currency}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagBank, no menu, "Extratos e Relatórios > Extrato de Transações".' d='Modules.PagBank.Admin'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' d='Modules.PagBank.Admin'}</p>
			<p>{l s='Link de acesso:' d='Modules.PagBank.Admin'} <a
					href="https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes"
					target="_blank">https://minhaconta.pagseguro.uol.com.br/meu-negocio/extrato-de-transacoes</a>
			</p>
		</div>
	{/if}
	<div class="panel card panel-info">
		<div class="panel-heading card-header">
			<h3 class="card-header-title">{l s='Dados do Pedido - PagBank:' d='Modules.PagBank.Admin'}</h3>
		</div>
		<div class="panel-body card-body nopadding">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<ul class="list list-unstyled">
						<li><b>{l s='Data do pedido:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->created_at|date_format:'%d/%m/%Y %H:%M'}</span></li>
						<li>
							<b>{l s='Código no PagBank:' d='Modules.PagBank.Admin'}</b>
							{if (isset($transaction->charges))}
								<span>{$transaction->charges[0]->id|replace:"CHAR_":""}</span>
							{else}
								<span>{$transaction->id}</span>
							{/if}
						</li>
						{if (isset($transaction->reference_id))}
							<li><b>{l s='Referência:' d='Modules.PagBank.Admin'}</b> <span>{$transaction->reference_id}</span></li>
						{/if}
						<li><b>{l s='Status:' d='Modules.PagBank.Admin'}</b> <span>{$desc_status}</span></li>
						<li><b>{l s='Forma de Pagamento:' d='Modules.PagBank.Admin'}</b> <span>{$payment_description}</span></li>
						{if isset($transaction->charges) && isset($transaction->charges[0]->payment_method->installments)}
							<li>
								<b>{l s='Quantidade de parcelas:' d='Modules.PagBank.Admin'}</b> <span>{$transaction->charges[0]->payment_method->installments}</span><br />
								<b>{l s='NSU:' d='Modules.PagBank.Admin'}</b> <span>{$transaction->charges[0]->payment_response->raw_data->nsu}</span><br />
								<b>{if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}{l s='Total c/ juros:' mod='pagbank'}{else}{l s='Total s/ juros:' mod='pagbank'}{/if}</b> <span>{displayPrice price=($transaction->charges[0]->amount->value/100) currency=$order->id_currency}</span>
								{if $info['capture'] == 0 && isset($transaction->charges[0]->amount->summary->paid) && $transaction->charges[0]->amount->summary->paid > 0}<br /><b>{l s='Total Capturado:' d='Modules.PagBank.Admin'}</b> <span>{displayPrice price=($transaction->charges[0]->amount->summary->paid/100) currency=$order->id_currency}</span>{/if}
							</li>
						{/if}
						{if isset($transaction->qr_codes[0]) && $transaction->qr_codes[0]->arrangements[0] == 'PIX'}
							<li><b>{l s='Link do PIX:' d='Modules.PagBank.Admin'}</b> <span>{$transaction->qr_codes[0]->text}</span></li>
						{/if}
						{if (isset($transaction->charges) && $transaction->charges[0]->payment_method->type == 'BOLETO')}
							<li>
								<b>{l s='Link do Boleto:' d='Modules.PagBank.Admin'}</b>
								{foreach from=$transaction->charges[0]->links item="link" name="link"}
									{if ($link->media == 'application/pdf')}
										<span>
											<a href="{$link->href}" title="{l s='Link do Boleto' d='Modules.PagBank.Admin'}"
												target="_blank">
												{$link->href}
											</a>
										</span>
									{/if}
								{/foreach}
							</li>
						{/if}
					</ul>
					<ul class="list list-unstyled">
						<li><b>{l s='Cliente:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->customer->name}</span></li>
						<li><b>{l s='E-mail:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->customer->email}</span></li>
						<li><b>{l s='Telefone:' d='Modules.PagBank.Admin'}</b>
							<span>({$transaction->customer->phones[0]->area})
								{$transaction->customer->phones[0]->number}</span></li>
						<li><b>{l s='CPF/CNPJ:'}</b> <span>{$transaction->customer->tax_id}</span></li>
					</ul>
					<ul class="list list-unstyled">
						<li><b>{l s='Endereço:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->street}</span></li>
						<li><b>{l s='Número' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->number}</span></li>
						<li><b>{l s='Complemento:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->complement}</span></li>
						<li><b>{l s='Bairro:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->locality}</span></li>
						<li><b>{l s='Cidade:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->city}</span></li>
						<li><b>{l s='UF:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->region_code}</span></li>
						<li><b>{l s='País:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->country}</span></li>
						<li><b>{l s='CEP:' d='Modules.PagBank.Admin'}</b>
							<span>{$transaction->shipping->address->postal_code	}</span></li>
					</ul>
					<br />
				</div>
				<div class="col-xs-12 col-sm-6">
					<h4>{l s='Resumo do pedido' d='Modules.PagBank.Admin'} ({$transaction->items|count}
						{l s='produto(s)' d='Modules.PagBank.Admin'})</h4>
					<table class="table table-responsive table-striped d-table">
						<thead>
							<th colspan="2">{l s='Produto' d='Modules.PagBank.Admin'}</th>
							<th>{l s='Qtd' d='Modules.PagBank.Admin'}</th>
							<th class="text-right">{l s='Total' d='Modules.PagBank.Admin'}</th>
						</thead>
						<tbody>
							{foreach from=$transaction->items item="product" name="prod"}
								<tr>
									<td>{$product->reference_id}</td>
									<td>{$product->name}</td>
									<td>{$product->quantity}</td>
									<td class="text-right">
										{Tools::displayPrice(($product->unit_amount/100)|escape:'htmlall':'UTF-8')}</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					<br />
				</div>
			</div>
			<div class="panel-footer card-footer clearfix">
				{if (in_array($status, ['AUTHORIZED', 'PAID', 'AVAILABLE', 'DISPUTE']))}
					<div class="alert alert-danger clearfix text-xs-center mt-2 mb-2">
						<p> 
							{l s='Se por algum motivo for necessário estornar um valor parcial ou total, informe o valor no campo abaixo:' d='Modules.PagBank.Admin'}
						</p>
					</div>
					<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post"
						class="form-horizontal form-inline well container">
						<div class="form-group">
							<label class="control-label col-xs-6">{l s='Valor a ser estornado:' d='Modules.PagBank.Admin'}
							</label>
							<div class="input-group col-xs-6 ml-3">
								<input type="text" name="refundValue" id="refundValue" class="input form-control" value=""
									max="{$transaction->charges[0]->amount->value/100}" onkeypress="mascara(this,valormask)"
									placeholder="{displayPrice price=($transaction->charges[0]->amount->value/100)} {if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}(c/ juros){else}(s/ juros){/if}" />
							</div>
						</div>
						<div class="input-group submit ml-3">
							<button name="refundOrderPagBank" type="submit" class="btn btn-danger btn-lg"
								onclick="return confirm('{l s='Tem certeza que deseja estornar a transação no PagBank e devolver o valor ao comprador?' d='Modules.PagBank.Admin' js=1}');">
								{l s='Estornar Transação no PagBank' d='Modules.PagBank.Admin'}
							</button>
						</div>
					</form>
				{/if}
				<br />
				{if $status == 'AUTHORIZED'}
					<div class="alert alert-info clearfix text-xs-center mt-2 mb-2">
						<p> 
							{l s='Para capturar o pagamento informe um valor parcial ou total no campo abaixo:' d='Modules.PagBank.Admin'}
						</p>
					</div>
					<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post"
						class="form-horizontal form-inline well container">
						<div class="form-group">
							<label class="control-label col-xs-6">{l s='Valor a ser capturado:' d='Modules.PagBank.Admin'}
							</label>
							<div class="input-group col-xs-6 ml-3">
								<input type="text" name="captureValue" id="captureValue" class="input form-control" value=""
									max="{$transaction->charges[0]->amount->value/100}" onkeypress="mascara(this,valormask)"
									placeholder="{displayPrice price=($transaction->charges[0]->amount->value/100)} {if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}(c/ juros){else}(s/ juros){/if}" />
							</div>
						</div>
						<div class="input-group submit ml-3">
							<button name="captureOrderPagBank" type="submit" class="btn btn-success btn-lg"
								onclick="return confirm('{l s='Tem certeza que deseja capturar o pagamento no PagBank?' d='Modules.PagBank.Admin' js=1}');">
								{l s='Capturar Pagamento no PagBank' d='Modules.PagBank.Admin'}
							</button>
						</div>
					</form>
				{/if}
			</div>
		</div>
	</div>
</div>
{if isset($reload) && $reload == 1}
<script>
	{literal}
		window.location.replace('{/literal}{$this_page}{literal}');
	{/literal}
</script>
{/if}