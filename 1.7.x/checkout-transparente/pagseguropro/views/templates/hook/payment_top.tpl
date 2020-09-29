{*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 *}

{if isset($pagseguro_msg) && $pagseguro_msg != ''}
	{literal}
	<script type="text/javascript">
		window.onload = function() {
			$('#pagseguro_msg').modal('show');
		};
	</script>
	{/literal}

	<div id="pagseguro_msg" class="modal fade" style="display:none;" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' d='Modules.PagSeguroPro.Shop'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger">{$pagseguro_msg|nl2br}</p>
				</div>
			</div>
		</div>
	</div>
{/if}

<a href="#fancy_load" id="fancy_btn" style="display:none;"></a>
<div id="fancy_load" class="form-group clearfix row" align="center">
	<div id="pagseguroproproccess" style="height:auto; width:600px; max-width:100%; display:none;" class="container clearfix">
		<div class="row">
			<div class="col-xs-3 col-sm-2 nopadding" align="center">
				<img src="{$this_path}views/img/loading.gif" class="img-fluid" />
			</div>
			<div class="col-xs-6 col-sm-7 text-center nopadding-left" id="pagseguromsg">
				{l s='Por favor aguarde. Processando pagamento...' d='Modules.PagSeguroPro.Shop'}
			</div>
			<div class="d-block d-sm-none col-sm-3 nopadding-left" id="pagseguro_logo" align="center">
				<img src="{$this_path}views/img/logo_pagseguro.png" class="img-fluid" />
			</div>
			<div class="hidden-sm-up hidden-lg-up col-xs-3 nopadding-left" id="pagseguro_logo" align="center">
				<img src="{$this_path}views/img/logo_pagseguro_mini_mobile.png" class="img-fluid" />
			</div>
		</div>
	</div>
</div>

{if $total > 0}
<div id="pagseguroprocontrolaErro" class="text-center nofloat-block col-xs-12" style="display:none;" align="center"></div>
{/if}

<input type="hidden" name="valor_pedido" id="valor_pedido" value="{$total}" />
<input type="hidden" name="msg_console" id="msg_console" value="{$msg_console}" />

{literal}
<script type="text/javascript">
	var urlImg = '{/literal}{$url_img}{literal}';
	var urlFuncoes = '{/literal}{$url_update}{literal}';
	var url_img = '{/literal}{$url_img}{literal}';
	{/literal}{if $msg_console == 1}{literal}
		var msg_console = 1;
	{/literal}{else}{literal}
		var msg_console = 0;
	{/literal}{/if}{literal}
</script>
{/literal}
