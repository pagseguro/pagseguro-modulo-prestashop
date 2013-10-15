{if $status == 'ok'}
	<p>{l s='Sua compra está finalizada. Obrigado por comprar conosco!' sprintf=$shop_name mod='pagseguro'}
		<br /><br />{l s='Sua compra ficou num total de:' mod='pagseguro'} <span class="price"><strong>{$total_to_pay}</strong></span>
		<br /><br />{l s='Não se esqueça de guardar o número da compra' mod='pagseguro'} <span><strong>{$id_order|sprintf:"#%d"}</strong></span> {l s='para consultar depois.' mod='pagseguro'} 
		<br /><br />{l s='Foi enviado um e-mail para você com as informações dessa compra.' mod='pagseguro'}
		<br /><br /><strong>{l s='Sua compra será enviada assim que recebermos a confirmação de pagamento.' mod='pagseguro'}</strong>
		<br /><br />{l s='Quaisquer dúvidas, por favor entre em contato conosco através do ' mod='pagseguro'} <a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='Encontramos um problema com sua compra. Caso julgue ser um erro, por favor contate-nos' mod='pagseguro'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
{/if}
