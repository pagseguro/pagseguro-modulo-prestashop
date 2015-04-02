{*
* 2007-2014 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<h2 class="title" title="{$pageTitle}">{$pageTitle}</h2>

<input type="hidden" id="adminToken" value="{$adminToken|escape}" />
<input type="hidden" id="urlAdminOrder" value="{$urlAdminOrder|escape}" />


{if isset($hasCredentials)}
    
    {if $recoveryActive}

        {if count($errors)}
            <div class="pagseguro-msg pagseguro-msg-error pagseguro-msg-small">
                <ul>
                    {foreach from=$errorMsg key=errorKey item=errorMessage}
                        <li>{$errorMessage|escape:'none'}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        <div class="pagseguro-search-tools">
            
            <button class="pagseguro-button" id="search-abandoned-button">
                {l s='Pesquisar' mod='pagseguro'}
            </button>
            <select class="pagseguro-field" id="pagseguro-daystorecovery-input">
                {html_options values=$daysToRecoveryKeys output=$daysToRecoveryValues selected=1}
            </select>
            <span>&nbsp;{l s='últimos dias' mod='pagseguro'}</span>

            <div class="right-tools">
                {l s='Recuperação de carrinho' mod='pagseguro'}:&nbsp;
                <button disabled="disabled" class="pagseguro-button" id="send-email-button">
                    {l s='Enviar e-mail' mod='pagseguro'}
                </button>
            </div>

        </div>

        <table id="abandoned-transactions-table" class="pagseguro-table" cellspacing="0">
            <thead>
                <tr>
                    <th class="col-md-0"><input type="checkbox" class="select-all"></th>
                    <th class="col-md-1">{l s='Data do Pedido' mod='pagseguro'}</th>
                    <th class="col-md-2">{l s='ID PrestaShop' mod='pagseguro'}</th>
                    <th class="col-md-3">{l s='Validade do link' mod='pagseguro'}</th>
                    <th class="col-md-4">{l s='E-mails enviados' mod='pagseguro'}</th>
                    <th class="col-md-5">{l s='Pedido' mod='pagseguro'}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        
    {else}
        
        <div class="pagseguro-msg pagseguro-msg-alert pagseguro-msg-small">
            <p>{l s='Ative a opção "listar transações abandonadas"' mod='pagseguro'}.</p>
        </div>

    {/if}

{else}
    
    <div class="pagseguro-msg pagseguro-msg-alert pagseguro-msg-small">
        <p>{l s='Para visualizar as transações abandonadas é necessário configurar suas' mod='pagseguro'} <span class="link pagseguro-goto-configuration">{l s='credenciais do PagSeguro' mod='pagseguro'}</span>.</p>
    </div>

{/if}