<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

{capture name=path}{l s='Pagamento via PagSeguro' mod='pagseguro'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>Ocorreu um erro, durante a compra.</h3>
<p>
    Desculpe, infelizmente ocorreu um erro durante a finaliza&ccedil;&atilde;o da compra.
    Por favor entre em contato com o administrador da loja se o problema persistir.
</p>
<p>
    <img src="{$image}" alt="{l s='pagseguro' mod='pagseguro'}" width="86" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
</p>

<a href="{$base_dir}" class="button_small" title="{l s='Voltar' mod='pagseguro'}">&laquo; {l s='Voltar' mod='pagseguro'}</a></p>