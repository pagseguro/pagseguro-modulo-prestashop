<h2>{$titulo}</h2>
<br>
<label>E-MAIL*</label>
<br>
    <input type="text" name="pagseguro_email" id="pagseguro_email" value="{$email}" maxlength="60" hint="Para oferecer o PagSeguro em sua loja é preciso ter uma conta do tipo vendedor ou empresarial. Se você ainda não tem uma conta PagSeguro &lt;a href=&quot;https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&amp;tipo=cadastro#!vendedor&quot; target=&quot;_blank&quot;&gt; clique aqui &lt;/a&gt;, caso contrário informe neste campo o e-mail associado à sua conta PagSeguro.">
<br>
<label>TOKEN*</label>
<br>
    <input type="text" name="pagseguro_token" id="pagseguro_token" value="{$token}" maxlength="32" hint="Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro. Caso não tenha um token &lt;a href=&quot;https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml&quot; target=&quot;_blank&quot;&gt; clique aqui &lt;/a&gt;, para gerar.">
<br>
<label>CHARSET</label>
<br>
    <select id="pagseguro_charset" name="pagseguro_charset" class="select" hint="Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.">
        {$optionCharset}
    </select>
<br>
<label>CHECKOUT</label>
<br>
    <select id="pagseguro_checkout" name="pagseguro_checkout" class="select" hint="No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.">
        {$optionCheckout}
    </select>
<br>
<p class="small">* Campos obrigatórios</p>
<div class="hintps _config"></div>