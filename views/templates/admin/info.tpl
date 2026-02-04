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

{if (isset($check_cron) && $check_cron) || (isset($check_carrier) && empty($check_carrier)) || (isset($check_rewriting) && $check_rewriting) || (isset($check_update) && $check_update)}
<div class="alert alert-warning">
    {if $check_cron}
        <p><b>Módulo PagBank - Tarefa Cron: </b></p> <p>As URLs de Tarefa Cron mudaram. Por favor, verifique e atualize em seu servidor de hospedagem. <br /> Para remover este alerta acesse as configurações do módulo em "Debug & Logs" e marque a opção "Desativar aviso Cron?" como SIM.</p>
    {/if}
    {if empty($check_carrier)}
        <p><b>Módulo PagBank - Restrições de transportadora: </b></p> <p>Verifique se as transportadoras estão vinculadas à forma de pagamento. Isso vai garantir que o módulo seja exibido e esteja disponível para processar pagamentos na tela de checkout. Para ver as configurações de Restrições de transportadora <a href="{$url_carrier}">Clique aqui</a> (role até o final da página)</p>{if empty($check_rewriting)}<br />{/if}
    {/if}
    {if $check_rewriting}
        <p><b>Módulo PagBank - URL Amigável: </b></p> <p>Ative a opção de reescrita de url para que o módulo possa funcionar corretamente. Para ativar a URL Amigável <a href="{$url_rewriting}">Clique aqui</a></p>{if empty($check_update)}<br />{/if}
    {/if}
    {if $check_update}
        <p><b>Módulo PagBank - Atualização disponível! Nova Versão: v.{$file_version} <br /> Acesse: <a href="https://github.com/pagseguro/pagseguro-modulo-prestashop" target="_blank">https://github.com/pagseguro/pagseguro-modulo-prestashop</a> </b></p>
    {/if}
</div>
{/if}