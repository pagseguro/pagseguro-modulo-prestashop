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

<link type="text/css" rel="stylesheet" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/pagseguro-module.css" />
<link type="text/css" rel="stylesheet" href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/pagseguro-modal.css" />
<link type="text/css" rel="stylesheet" href="{$cssFileVersion|escape:'htmlall':'UTF-8'}" />

<div id="pagseguro-module">
	
    <div id="pagseguro-module-header">
		<div class="wrapper">
			
			<div id="pagseguro-logo">
				<img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-180x41.gif" />
				<div id="pagseguro-module-version">Versão {$moduleVersion|escape:'htmlall':'UTF-8'}</div>
			</div>
		    
		    <a id="pagseguro-registration-button" class="pagseguro-button gray-theme" href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank">{l s='Faça seu cadastro' mod='pagseguro'}</a>

		</div>
	</div>

	<div id="pagseguro-module-content">

		<div class="wrapper">
		    
		    <div id="pagseguro-module-menu">

                        <ul>
                            {foreach from=$pages item=page}
                                
                                {if !$page.hasChild}
                                <li 
                                    id="menu-item-{$page.id|escape:'htmlall':'UTF-8'}" 
                                    class="menu-item {if $page.selected}selected{/if}" 
                                    data-page-id="{$page.id|escape:'htmlall':'UTF-8'}"
                                    {if $page.hasForm} data-has-form="true" {/if} >
                                    <a class="link menu-item-link" href="#pagseguro-module-content-{$page.id|escape:'htmlall':'UTF-8'}">
                                            {$page.title|escape:'htmlall':'UTF-8'}
                                    </a>
                                {else}
                                    <li 
                                    id="menu-item-{$page.id|escape:'htmlall':'UTF-8'}" 
                                    data-page-id="{$page.id|escape:'htmlall':'UTF-8'}"
                                    {if $page.hasForm} data-has-form="true" {/if} >
                                        <span class="children"><i class="icon"></i>{$page.title|escape:'htmlall':'UTF-8'}</span>
                                    
                                {/if}
                                    {if $page.hasChild}
                                        <ul>
                                            {foreach from=$page.content item=nav}
                                                <li 
                                                    id="menu-item-{$nav.id|escape:'htmlall':'UTF-8'}" 
                                                    class="menu-item {if $nav.selected}selected{/if}" 
                                                    data-page-id="{$nav.id|escape:'htmlall':'UTF-8'}"
                                                    {if $nav.hasForm} data-has-form="true" {/if} >
                                                    <a class="link menu-item-link" href="#pagseguro-module-content-{$nav.id|escape:'htmlall':'UTF-8'}">
                                                            {$nav.title|escape:'htmlall':'UTF-8'}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    {/if}
                                </li>
                            {/foreach}
                        </ul>

		    	<div id="pagseguro-save-wrapper">
		    		<p>Clique no botão abaixo para salvar suas configurações.</p>
		    		<button id="pagseguro-save-button" class="pagseguro-button gray-theme" type="button">Salvar Configuração</button>
		    	</div>
                </div>

                <div id="pagseguro-module-contents">

                    {foreach from=$pages item=page}

                            {if is_array($page.content)}
                                {foreach from=$page.content item=subpage}

                                    <div id="pagseguro-module-content-{$subpage.id|escape:'htmlall':'UTF-8'}" 
                                         class="pagseguro-module-content {if $subpage.selected}selected{/if}">

                                        {if isset($success)}
                                            <div class="pagseguro-msg pagseguro-msg-success pagseguro-msg-small">
                                                    <p>Dados atualizados com sucesso.</p>
                                            </div>
                                        {/if}

                                        {foreach from=$errors item=error}
                                            <div class="pagseguro-msg pagseguro-msg-error pagseguro-msg-small">
                                                <p>{$error|escape:'htmlall':'UTF-8'}</p>
                                            </div>
                                        {/foreach}

                                        {$subpage.content|escape:'quotes':'UTF-8'}   
                                    </div>                                           
                                {/foreach}
                            {else}       
                                <div id="pagseguro-module-content-{$page.id|escape:'htmlall':'UTF-8'}" 
                                     class="pagseguro-module-content {if $page.selected}selected{/if}">

                                    {if isset($success)}
                                        <div class="pagseguro-msg pagseguro-msg-success pagseguro-msg-small">
                                                <p>Dados atualizados com sucesso.</p>
                                        </div>
                                    {/if}

                                    {foreach from=$errors item=error}
                                        <div class="pagseguro-msg pagseguro-msg-error pagseguro-msg-small">
                                            <p>{$error|escape:'htmlall':'UTF-8'}</p>
                                        </div>
                                    {/foreach}

                                    {$page.content|escape:'quotes':'UTF-8'}   

                                </div> 
                            {/if}
                    {/foreach}
                </div>
            </div>
	</div>

</div>

<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jquery.min.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jquery-1102.min.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/jquery.inputmask.bundle.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/pagseguro-module.js"></script>