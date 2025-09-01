{*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 *}

{if (isset($check_update) && $check_update) || (isset($check_carrier) && empty($check_carrier))}
<div class="alert alert-warning">
        {if $check_update}
            <p><b>Módulo PagBank - Atualização disponível! Nova Versão: v.{$file_version} <br /> Acesse: <a href="https://github.com/pagseguro/pagseguro-modulo-prestashop" target="_blank">https://github.com/pagseguro/pagseguro-modulo-prestashop</a> </b></p>{if empty($check_carrier)}<br />{/if}
        {/if}
        {if empty($check_carrier)}
            <p><b>Módulo PagBank - Restrições de transportadora: </b></p> <p>Verifique se as transportadoras estão vinculadas à forma de pagamento. Isso vai garantir que o módulo seja exibido e esteja disponível para processar pagamentos na tela de checkout. Para ver as configurações de Restrições de transportadora <a href="{$set_url}">Clique aqui</a> (role até o final da página)</p>
        {/if}
</div>
{/if}