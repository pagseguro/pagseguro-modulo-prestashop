{*
 * 2018 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente 
 *
 *}

{if isset($pagseguro_msg) && $pagseguro_msg != ''}
	{literal}
	<script type="text/javascript">
		$(document).ready(function (){
			$('#pagseguro_msg').modal('show');
			setTimeout(function() {
				//$('#pagseguro_msg').modal('hide');
			},10000);
		});
	</script>
	{/literal}
	<div id="pagseguro_msg" class="modal fade" style="display:none;" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' mod='pagseguropro'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger">{$pagseguro_msg|nl2br}</p>
				</div>
			</div>
		</div>
	</div>
{/if}
