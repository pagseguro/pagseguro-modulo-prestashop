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

{if $status == 'ok'}
	<p>{l s='Sua compra está finalizada. Obrigado por comprar conosco!' sprintf=$shop_name mod='pagseguro'}
		<br /><br />{l s='Sua compra ficou num total de: ' mod='pagseguro'} <span class="price"><strong>{$total_to_pay}</strong></span>
		{if !isset($reference)}
			<br /><br />{l s='Não se esqueça de guardar o número da compra #%d para consultar depois.' sprintf=$id_order mod='pagseguro'}
		{else}
			<br /><br />{l s='Não se esqueça de guardar o número da compra %s para consultar depois.' sprintf=$reference mod='pagseguro'}
		{/if}
		<br /><br />{l s='Foi enviado um e-mail para você com as informações dessa compra.' mod='pagseguro'}
		<br /><br /><strong>{l s='Sua compra será enviada assim que recebermos a confirmação de pagamento.' mod='pagseguro'}</strong>
		<br /><br />{l s='Quaisquer dúvidas, por favor entre em contato conosco através do ' mod='pagseguro'} <a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
        <p class="cart_navigation">
            <a href="{$pagseguro_return_url}" class="exclusive_large" target="_blank">{l s='Ir ao PagSeguro' mod='pagseguro'}</a>
	</p>
{else}
	<p class="warning">
		{l s='Encontramos um problema com sua compra. Caso julgue ser um erro, por favor contate-nos' mod='pagseguro'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
{/if}
