{if {$version_module} > '1.5'}
<p class="payment_module">
	<a href="{$action_url}"
		title="{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}">
		<img src="{$image}"
		alt="{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}" />
		{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}
	</a>
</p>
{else}
<p class="payment_module">
	<a href="javascript:void(0)" onclick="$('#payment_form').submit();"
		title="{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}">
		<img src="{$image}"
		alt="{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}" />
		{l s='Pague com PagSeguro e parcele em até 18 vezes' mod='pagseguro'}
	</a>
</p>
<form id="payment_form" action="{$action_url}" accept-charset=""
	data-ajax="false" title="{l s='Pay with PayPal' mod='paypal'}"
	method="post"></form>
{/if}

