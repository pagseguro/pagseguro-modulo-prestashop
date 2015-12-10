{*
* 2007-2015 PrestaShop 
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
*  @copyright 2007-2015 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*} 

<h2 class="title" title="Conciliação">Conciliação</h2>
<p>
    Com esta funcionalidade você poderá listar as transações realizadas em um determinado intervalo de datas. Ela pode ser usada periodicamente para verificar se o seu sistema recebeu todas as notificações enviadas pelo PagSeguro e, consequentemente, manter o status de suas transações sempre atualizados.
</p>

{if isset($hasCredentials) && $hasCredentials}
    
    <input type="hidden" id="adminToken" value="{$adminToken|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" id="urlAdminOrder" value="{$urlAdminOrder|escape:'htmlall':'UTF-8'}" />

    <div class="pagseguro-search-tools">
       <button class="pagseguro-button" type="button" id="conciliation-search-button">Pesquisar</button>
        <select class="pagseguro-field" id="pagseguro-conciliation-days-input" name="pagseguro_dias">
            {html_options values=$conciliationSearchValues output=$conciliationSearchValues}
        </select>
        <span>&nbsp;últimos dias</span>

        <div class="right-tools">
            <button class="pagseguro-button" type="button" id="conciliation-button">Conciliar</button>
        </div>
    </div>
    
    <table id="conciliation-table" class="pagseguro-table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th class="col-md-0"><input type="checkbox" class="select-all"></th>
                <th class="col-md-1">Data</th>
                <th class="col-md-2">ID PrestaShop</th>
                <th class="col-md-3">ID PagSeguro</th>
                <th class="col-md-2">Status PrestaShop</th>
                <th class="col-md-4">Status PagSeguro</th>
                <th class="col-md-5">Pedido</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="pagseguro-msg pagseguro-msg-info pagseguro-msg-micro">
        <p>Somente transações geradas a partir da versão 1.8 do módulo serão listadas.</p>
    </div>

{else}
    
    <div class="pagseguro-msg pagseguro-msg-alert pagseguro-msg-small">
        <p>Para conciliar transações é necessário configurar suas <span class="link pagseguro-goto-configuration">credenciais do PagSeguro</span>.</p>
    </div>

{/if}