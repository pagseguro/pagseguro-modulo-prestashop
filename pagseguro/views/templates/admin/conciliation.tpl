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
<p>
    {l s='Esta consulta permite obter as transações recebidas por você em um intervalo de datas. Ela pode ser usada periodicamente para verificar se o seu sistema recebeu todas as notificações de transações enviadas pelo PagSeguro, de forma a conciliar as transações armazenadas em seu sistema com o PagSeguro.' mod='pagseguro'}
</p>

{if isset($hasCredentials) && $hasCredentials}
    
    <input type="hidden" id="adminToken" value="{$adminToken|escape}" />
    <input type="hidden" id="urlAdminOrder" value="{$urlAdminOrder|escape}" />

    <div class="pagseguro-search-tools">
       <button class="pagseguro-button" type="button" id="conciliation-search-button">{l s='Pesquisar' mod='pagseguro'}</button>
        <select class="pagseguro-field" id="pagseguro-conciliation-days-input" name="pagseguro_dias">
            {html_options values=$conciliationSearchValues output=$conciliationSearchValues}
        </select>
        <span>&nbsp;{l s='últimos dias' mod='pagseguro'}</span>

        <div class="right-tools">
            <button class="pagseguro-button" type="button" id="conciliation-button">{l s='Conciliar' mod='pagseguro'}</button>
        </div>
    </div>
    
    <table id="conciliation-table" class="pagseguro-table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="col-md-0"><input type="checkbox" class="select-all"></th>
                <th class="col-md-1">{l s='Data' mod='pagseguro'}</th>
                <th class="col-md-2">{l s='ID PrestaShop' mod='pagseguro'}</th>
                <th class="col-md-3">{l s='ID PagSeguro' mod='pagseguro'}</th>
                <th class="col-md-2">{l s='Status PrestaShop' mod='pagseguro'}</th>
                <th class="col-md-4">{l s='Status PagSeguro' mod='pagseguro'}</th>
                <th class="col-md-5">{l s='Pedido' mod='pagseguro'}</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="pagseguro-msg pagseguro-msg-info pagseguro-msg-micro">
        <p>{l s='Somente transações geradas a partir da versão 1.8 do módulo serão listadas.' mod='pagseguro'}</p>
    </div>

{else}
    
    <div class="pagseguro-msg pagseguro-msg-alert pagseguro-msg-small">
        <p>{l s='Para conciliar transações é necessário configurar suas' mod='pagseguro'} <span class="link pagseguro-goto-configuration">{l s='credenciais do PagSeguro' mod='pagseguro'}</span>.</p>
    </div>

{/if}