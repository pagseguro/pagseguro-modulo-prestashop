{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author	  2011-2023 PrestaBR - https://prestabr.com.br
 * @copyright 1996-2023 PagBank - https://pagseguro.uol.com.br
 * @license	  Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

<script type="text/javascript" data-keepinline="true">
	var public_key = '{$public_key}';
</script>
{if isset($pagbank_msg) && $pagbank_msg != ''}
	{literal}
		<script type="text/javascript">
			$(document).ready(function() {
				$('#pagbank_msg').modal('show');
				setTimeout(function() {}, 10000);
			});
		</script>
	{/literal}
	<div id="pagbank_msg" class="modal fade" style="display:none;" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">{l s='Detalhes da transação' mod='pagbank'}</h4>
				</div>
				<div class="modal-body">
					<p class="msg-err alert alert-danger">{$pagbank_msg|nl2br}</p>
				</div>
			</div>
		</div>
	</div>
{/if}