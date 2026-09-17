{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2026 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2026 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

<br />
<div class="row" id="pagbank">
	{if isset($pagbank_msg) && $pagbank_msg}
		<div class="alert alert-info">
			<p>{$pagbank_msg}</p>
		</div>
	{/if}
	{if $info['refund'] > 0}
		<div class="alert alert-info">
			<p>{l s='Pedido com estorno parcial ou total no PagBank. Valor do último estorno: R$' mod='pagbank'} {$info['refund']}.</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagBank, no menu, "Extratos e Relatórios".' mod='pagbank'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' mod='pagbank'}</p>
			<p>{l s='Link de acesso:' mod='pagbank'} 
				<a href="https://minhaconta.pagbank.com.br/meu-negocio/vendas-e-recebimentos" target="_blank">
					https://minhaconta.pagbank.com.br/meu-negocio/vendas-e-recebimentos
				</a>
			</p>
		</div>
	{/if}
	{if $info['capture'] != 'null' && $info['capture'] == 0 && isset($transaction->charges[0]->amount->summary->paid) && $transaction->charges[0]->amount->summary->paid > 0 ||
		$info['capture'] != 'null' && $info['capture'] == 0 && isset($transaction->charges[1]->amount->summary->paid) && $transaction->charges[1]->amount->summary->paid > 0}
		<div class="alert alert-info">
			<p>
				{l s='Pedido com captura parcial ou total no PagBank. Valor da captura:' mod='pagbank'} 
				{displayPrice price=($transaction->charges[0]->amount->summary->paid/100) currency=$order->id_currency}
				({$transaction->charges[0]->payment_method->card->last_digits})
				{if isset($transaction->charges[1]->amount->summary->paid) && $transaction->charges[1]->amount->summary->paid > 0}
					/ {displayPrice price=($transaction->charges[1]->amount->summary->paid/100) currency=$order->id_currency}
					({$transaction->charges[1]->payment_method->card->last_digits})
				{/if}
			</p>
			<p>{l s='Para mais detalhes acesse a sua conta no PagBank, no menu, "Extratos e Relatórios".' mod='pagbank'}</p>
			<p>{l s='Ao acessar a transação role a página para localizar "Extrato de movimentações da transação".' mod='pagbank'}</p>
			<p>{l s='Link de acesso:' mod='pagbank'} 
				<a href="https://minhaconta.pagbank.com.br/meu-negocio/vendas-e-recebimentos" target="_blank">
					https://minhaconta.pagbank.com.br/meu-negocio/vendas-e-recebimentos
				</a>
			</p>
		</div>
	{/if}
	{if $info['transaction_code']|strstr:"GPAY"}
		<div class="panel panel-info">
			<div class="panel-heading">
				{l s='Dados do Pedido - PagBank:' mod='pagbank'}
			</div>
			<div class="panel-body nopadding">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="alert alert-info">
								<p>{l s='Pedido Demo Google Pay.' mod='pagbank'}</p>
						</div>
					</div>
				</div>
			</div>
		</div>	
	{else}
		<div class="panel panel-info">
			<div class="panel-heading">
				{l s='Dados do Pedido - PagBank:' mod='pagbank'}
			</div>
			<div class="panel-body nopadding">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<ul class="list list-unstyled">
							{if isset($transaction->charges) && count($transaction->charges) > 1}
								<li>
									<b>{l s='Data do pedido:' mod='pagbank'}</b>
									<span>{$transaction->created_at|date_format:'%d/%m/%Y %H:%M'}</span>
								</li>
								<li>
									<b>{l s='Forma de Pagamento:' mod='pagbank'}</b> <span>{$payment_description}</span>
								</li>
								<li>
									<b>{l s='Status:' mod='pagbank'}</b> <span>{$desc_status}</span>
								</li>
								<br />
								<li>
									<b>{l s='Cartão 1 Final:' mod='pagbank'} {$transaction->charges[0]->payment_method->card->last_digits}</b>
								</li>
								<li>
									<b>{l s='Código no PagBank:' mod='pagbank'}</b> <span>{$transaction->charges[0]->id|replace:"CHAR_":""}</span>
								</li>
								<li>
									<b>{l s='Referência:' mod='pagbank'}</b> <span>{$transaction->charges[0]->reference_id}</span>
								</li>
								<li>
									<b>{l s='Quantidade de parcelas:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_method->installments}</span>
								</li>
								<li>
									<b>{l s='NSU:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_response->raw_data->nsu}</span>
								</li>
								<li>
									{if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}
										<b>{l s='Total c/ juros:' mod='pagbank'}</b>
									{else}
										<b>{l s='Total s/ juros:' mod='pagbank'}</b>
									{/if}
									<span>{displayPrice price=($transaction->charges[0]->amount->value/100) currency=$order->id_currency}</span>
									{if $info['capture'] != 'null' && $info['capture'] == 0 && isset($transaction->charges[0]->amount->summary->paid) && $transaction->charges[0]->amount->summary->paid > 0}
										<br /><b>{l s='Total Capturado:' mod='pagbank'}</b> 
										<span>{displayPrice price=($transaction->charges[0]->amount->summary->paid/100) currency=$order->id_currency}</span>
									{/if}
								</li>
								<br />
								<li>
									<b>{l s='Cartão 2 Final:' mod='pagbank'} {$transaction->charges[1]->payment_method->card->last_digits}</b>
								</li>
								<li>
									<b>{l s='Código no PagBank:' mod='pagbank'}</b> <span>{$transaction->charges[1]->id|replace:"CHAR_":""}</span>
								</li>
								<li>
									<b>{l s='Referência:' mod='pagbank'}</b> <span>{$transaction->charges[1]->reference_id}</span>
								</li>
								<li>
									<b>{l s='Quantidade de parcelas:' mod='pagbank'}</b> <span>{$transaction->charges[1]->payment_method->installments}</span>
								</li>
								<li>
									<b>{l s='NSU:' mod='pagbank'}</b> <span>{$transaction->charges[1]->payment_response->raw_data->nsu}</span>
								</li>
								<li>
									{if isset($transaction->charges[1]->amount->fees) && $transaction->charges[1]->amount->fees}
										<b>{l s='Total c/ juros:' mod='pagbank'}</b>
									{else}
										<b>{l s='Total s/ juros:' mod='pagbank'}</b>
									{/if}
									<span>{displayPrice price=($transaction->charges[1]->amount->value/100) currency=$order->id_currency}</span>
									{if $info['capture'] != 'null' && $info['capture'] == 0 && isset($transaction->charges[1]->amount->summary->paid) && $transaction->charges[1]->amount->summary->paid > 0}
										<br /><b>{l s='Total Capturado:' mod='pagbank'}</b> 
										<span>{displayPrice price=($transaction->charges[1]->amount->summary->paid/100) currency=$order->id_currency}</span>
									{/if}
								</li>
							{else}
								<li>
									<b>{l s='Data do pedido:' mod='pagbank'}</b>
									<span>{$transaction->created_at|date_format:'%d/%m/%Y %H:%M'}</span>
								</li>
								<li>
									<b>{l s='Código no PagBank:' mod='pagbank'}</b>
									{if isset($transaction->charges)}
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
										<b>{l s='Quantidade de parcelas:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_method->installments}</span>
									</li>
									<li>
										<b>{l s='NSU:' mod='pagbank'}</b> <span>{$transaction->charges[0]->payment_response->raw_data->nsu}</span>
									</li>
									<li>
										{if isset($transaction->charges[0]->amount->fees) && $transaction->charges[0]->amount->fees}
											<b>{l s='Total c/ juros:' mod='pagbank'}</b>
										{else}
											<b>{l s='Total s/ juros:' mod='pagbank'}</b>
										{/if}
										<span>{displayPrice price=($transaction->charges[0]->amount->value/100) currency=$order->id_currency}</span>
										{if $info['capture'] != 'null' && $info['capture'] == 0 && isset($transaction->charges[0]->amount->summary->paid) && $transaction->charges[0]->amount->summary->paid > 0}
											<br /><b>{l s='Total Capturado:' mod='pagbank'}</b>
											<span>{displayPrice price=($transaction->charges[0]->amount->summary->paid/100) currency=$order->id_currency}</span>
										{/if}
									</li>
								{/if}
								{if isset($transaction->qr_codes[0]) && $transaction->qr_codes[0]->arrangements[0] === 'PIX'}
									<li><b>{l s='Link do PIX:' mod='pagbank'}</b> <span>{$transaction->qr_codes[0]->text}</span></li>
								{/if}
								{if (isset($transaction->charges) && $transaction->charges[0]->payment_method->type === 'BOLETO')}
									<li>
										<b>{l s='Link do Boleto:' mod='pagbank'}</b>
										{foreach from=$transaction->charges[0]->links item="link" name="link"}
											{if ($link->media === 'application/pdf')}
												<span>
													<a href="{$link->href}" title="{l s='Link do Boleto' mod='pagbank'}"
														target="_blank">
														{$link->href}
													</a>
												</span>
											{/if}
										{/foreach}
									</li>
								{/if}
							{/if}
						</ul>
						<ul class="list list-unstyled">
							<li><b>{l s='Cliente:' mod='pagbank'}</b> <span>{$transaction->customer->name}</span></li>
							<li><b>{l s='E-mail:' mod='pagbank'}</b> <span>{$transaction->customer->email}</span></li>
							{if isset($transaction->customer->phones[0]->area) && $transaction->customer->phones[0]->area}
							<li><b>{l s='Telefone:' mod='pagbank'}</b> <span>({$transaction->customer->phones[0]->area})
									{$transaction->customer->phones[0]->number}</span></li>
							{/if}
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
							{l s='produto(s)' mod='pagbank'})</h4>
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
						<div class="col-xs-12 col-sm-12 col-lg-8 alert alert-danger">
							<p> 
								{l s='Se por algum motivo for necessário estornar um valor parcial ou total, informe o valor no campo abaixo:' mod='pagbank'}
							</p>
						</div>
						<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post" class="form-inline">
							<div class="form-group col-xs-12 col-sm-12 col-lg-12">
								<div class="col-xs-12 col-sm-12 col-lg-12">
									{if isset($transaction->charges) && $transaction->charges[0]->payment_method->type === 'CREDIT_CARD'}
										<select name="charge_option" class="input form-control">
											<option value="1">{l s='Final:' mod='pagbank'} {$transaction->charges[0]->payment_method->card->last_digits}</option>
											{if isset($transaction->charges) && count($transaction->charges) > 1}
												<option value="2">{l s='Final:' mod='pagbank'} {$transaction->charges[1]->payment_method->card->last_digits}</option>
											{/if}
										</select>
									{else}
										<input type="hidden" name="charge_option" value="1" /> 
									{/if}
									<input type="text" name="refund_value" class="input form-control" value=""
										max="{$transaction->charges[0]->amount->value/100}" onkeypress="mascara(this,valormask)"
										placeholder="{l s='Informe o valor' mod='pagbank'}" />
									<button name="refundOrderPagBank" type="submit" class="btn btn-danger"
										onclick="return confirm('{l s='Tem certeza que deseja estornar a transação no PagBank e devolver o valor ao comprador?' mod='pagbank' js=1}');">
										{l s='Estornar Transação no PagBank' mod='pagbank'}
									</button>
								</div>
							</div>
						</form>
					{/if}
					<br />
					{if $status === 'AUTHORIZED'}
						<div class="col-xs-12 col-sm-12 col-lg-8 alert alert-info clearfix capture">
							<p> 
								{l s='Para capturar o pagamento informe um valor parcial ou total no campo abaixo:' mod='pagbank'}
							</p>
						</div>
						<form action="{$this_page|escape:'htmlall':'UTF-8'}" method="post" class="form-inline">
							<div class="form-group col-xs-12 col-sm-12 col-lg-12">
								<div class="col-xs-12 col-sm-12 col-lg-12">
									{if isset($transaction->charges) && $transaction->charges[0]->payment_method->type === 'CREDIT_CARD'}
										<select name="charge_option" class="input form-control">
											<option value="1">{l s='Final:' d='Modules.PagBank.Admin'} {$transaction->charges[0]->payment_method->card->last_digits}</option>
											{if isset($transaction->charges) && count($transaction->charges) > 1}
												<option value="2">{l s='Final:' d='Modules.PagBank.Admin'} {$transaction->charges[1]->payment_method->card->last_digits}</option>
											{/if}
										</select>
									{else}
										<input type="hidden" name="charge_option" value="1" /> 
									{/if}
									<input type="text" name="capture_value" class="input form-control" value="" onkeypress="mascara(this,valormask)"
										placeholder="{l s='Informe o valor' mod='pagbank'}" />
									<button name="captureOrderPagBank" type="submit" class="btn btn-success"
										onclick="return confirm('{l s='Tem certeza que deseja capturar o pagamento no PagBank?' mod='pagbank' js=1}');">
										{l s='Capturar Pagamento no PagBank' mod='pagbank'}
									</button>
								</div>
							</div>
						</form>
					{/if}
				</div>
			</div>
		</div>
	{/if}
</div>
{if isset($reload) && $reload == 1}
<script>
	{literal}
		window.location.replace('{/literal}{$this_page}{literal}');
	{/literal}
</script>
{/if}