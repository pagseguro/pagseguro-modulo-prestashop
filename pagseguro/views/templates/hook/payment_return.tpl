{*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
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
{else}
	<p class="warning">
		{l s='Encontramos um problema com sua compra. Caso julgue ser um erro, por favor contate-nos' mod='pagseguro'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='suporte ao consumidor' mod='pagseguro'}</a>.
	</p>
{/if}
