{*
* 2007-2011 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript" src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js"></script>
<script type="text/javascript">
function checkout()
{
    var query = $.ajax({
        type: 'POST',
        url: "{$action_url}",
        success: function(response) {
		var json = $.parseJSON(response);
            PagSeguroLightbox(
            json.code,{
                success: function(token){
                    window.location.href = json.redirect;
                },
                abort: function(){
                    redirecToPageError();
                }
            });
        },
        error: function() {
            redirecToPageError();
        }
    });
}
function redirecToPageError(){
    window.location.href = baseDir + 'modules/pagseguro/controllers/front/error.php';
}
</script>
   

{if $version >= '1.5.0.2'}
    <style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>
{else}
    <style type="text/css" media="all">{literal}div#center_column{ width: 535px; }{/literal}</style>
{/if}

{capture name=path}{l s='Pagamento via PagSeguro' mod='pagseguro'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Resumo da compra' mod='pagseguro'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Pagamento via PagSeguro' mod='pagseguro'}</h3>
<form action="{$action_url}" method="post">
	<p>
		<img src="{$image_payment}" alt="{l s='pagseguro' mod='pagseguro'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
		{l s='Você escolheu efetuar o pagamento via PagSeguro' mod='pagseguro'}
		<br/><br />
		{l s='Breve resumo da sua compra:' mod='pagseguro'}
	</p>
	<p style="margin-top:20px;">
		- {l s='O valor total de sua compra é ' mod='pagseguro'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
			{l s='(tax incl.)' mod='pagseguro'}
		{/if}
	</p>
    {if $current_currency_name != "Real"}
        <p>
		{l s='Moeda atual: ' mod='pagseguro'}&nbsp;<b>{$current_currency_name}</b>
                <input type="hidden" name="currency_payment" value="{$current_currency_id}" />
	</p>
        {/if}
	<p style="margin-top:20px;">
            {l s='Aceitamos a seguinte moeda para efetuar seu pagamento via PagSeguro: ' mod='pagseguro'}&nbsp;<b>Real</b>
                <input type="hidden" name="currency_payment" />
	</p>
        {if $current_currency_name != "Real" && $total_real > 0.00}
	<p>
		- {l s='O valor total de sua compra convertido é ' mod='pagseguro'}
                <span id="amount" class="price">{displayPrice price=$total_real currency=$currency_real}</span>
		{if $use_taxes == 1}
			{l s='(tax incl.)' mod='pagseguro'}
		{/if}
	</p>
        {/if}
	<p>
		<br /><br />
		<b>{l s='Por favor, confirme sua compra clicando no botão \'Confirmo minha compra\'' mod='pagseguro'}</b>
	</p>
	<p class="cart_navigation">
    	{if ($checkout)}
            <input type="button " value="{l s='Confirmo minha compra' mod='pagseguro'}" class="exclusive_large" onclick="checkout()" />
        {else}
		    <input type="submit" name="submit" value="{l s='Confirmo minha compra' mod='pagseguro'}" class="exclusive_large" />
        {/if}
		<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outros formas de pagamento' mod='pagseguro'}</a>
	</p>
</form>
{/if}
