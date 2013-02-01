{*
 * 2012-2013 S2IT Solutions Consultoria LTDA.
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
 *  @author Wellington Camargo <wellington.camargo@s2it.com.br>
 *  @copyright  2012-2013 S2IT Solutions Consultoria LTDA
 *  @version  Release: $Revision: 1 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}

{capture name=path}{l s='Pagamento via PagSeguro' mod='pagseguro'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Resumo da compra' mod='pagseguro'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Pagamento via PagSeguro' mod='pagseguro'}</h3>
<form action="{$link->getModuleLink('pagseguro', 'validation', [], true)}" method="post">
	<p>
		<img src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/pagamentos/209x48-comprar-assina.gif" alt="{l s='pagseguro' mod='pagseguro'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
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
	<p>
		{l s='Aceitamos a seguinte moeda para efetuar seu pagamento via PagSeguro: ' mod='pagseguro'}&nbsp;<b>{$currencies.0.name}</b>
                <input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	</p>
	<p>
		<br /><br />
		<b>{l s='Por favor, confirme sua compra clicando no botão \'Confirmo minha compra\'' mod='pagseguro'}.</b>
	</p>
	<p class="cart_navigation">
		<input type="submit" name="submit" value="{l s='Confirmo minha compra' mod='pagseguro'}" class="exclusive_large" />
		<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outros formas de pagamento' mod='pagseguro'}</a>
	</p>
</form>
{/if}
