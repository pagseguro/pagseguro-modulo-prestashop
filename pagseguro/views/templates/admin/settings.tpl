{*
* 2007-2014 PrestaShop 
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
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
	
<h2 class="title" title="{$pageTitle}">{$pageTitle}</h2>

<p>{l s='Aqui você pode configurar o módulo PagSeguro no PrestaShop.' mod='pagseguro'}</p>


<form id="pagseguro-config-form" action="{$action_post|escape:'none'}" method="POST">

	<input type="hidden" name="pagseguroModuleSubmit">

	<div class="config-area">
		
		<h3 title="Credenciais" class="title-text title-3 title">{l s='Credenciais' mod='pagseguro'}</h3>

		<!--
			##################################
		 	##### E-mail  ####################
		-->
		<div class="config-sub-area">	
			<label for="pagseguro-email-input">{l s='E-mail' mod='pagseguro'}</label>
			<p>
				{l s='Para oferecer o PagSeguro em sua loja é preciso ter uma conta do tipo vendedor ou empresarial.' mod='pagseguro'}
				{l s='Informe neste campo o e-mail associado à sua conta PagSeguro.' mod='pagseguro'}
			 	<a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank">{l s='Clique aqui para criar uma conta.' mod='pagseguro'}</a>.
			</p>
			<div class="config-field">
				<input type="text" class="pagseguro-field" name="pagseguroEmail" id="pagseguro-email-input" value="{$email|escape}" maxlength="60">
			</div>
		</div>


		<!--
			##################################
		 	##### Token  #####################
		-->
		<div class="config-sub-area">
			<label for="pagseguro-token-input">{l s='Token' mod='pagseguro'}</label>
			<p>
				{l s='Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro.' mod='pagseguro'}
				<a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">{l s='Clique aqui para gerar o token' mod='pagseguro'}</a>.
			</p>
			<div class="config-field">
				<input type="text" class="pagseguro-field" name="pagseguroToken" id="pagseguro-token-input" value="{$token|escape}" maxlength="32">
			</div>
		</div>

	</div>


	<!--
		##################################
	 	##### Charset  ###################
	-->
	<div class="config-area">
		<h3 title="Charset" class="title-text title-3 title">{l s='Charset' mod='pagseguro'}</h3>
		<p>
			{l s='Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.' mod='pagseguro'}
		</p>
		<div class="config-field">
			<select class="pagseguro-field" name="pagseguroCharset" id="pagseguro-charset-input">
	    		{html_options values=$charsetKeys output=$charsetValues selected=$charsetSelected|escape:'none'}
			</select>
		</div>
	</div>


	<!--
		##################################
	 	##### Checkout  ##################
	-->	
	<div class="config-area">
		<h3 title="Checkout" class="title-text title-3 title">{l s='Checkout' mod='pagseguro'}</h3>
		
		<div class="config-field">
			<select class="pagseguro-field pagseguro-select-hint" name="pagseguroCheckout" id="pagseguro-checkout-input">
			    {html_options values=$checkoutKeys output=$checkoutValues selected=$checkoutSelected|escape:'none'}
			</select>
		</div>

		<p class="pagseguro-option-hint" data-hint="0">
			{l s='No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.' mod='pagseguro'}
		</p>

		<p class="pagseguro-option-hint" data-hint="1">
			{l s='No checkout lightbox o comprador, após escolher os produtos e/ou serviços, fará o pagamento em uma janela que se sobrepõe a sua loja.' mod='pagseguro'}
		</p>

	</div>


	<!--
		##################################
	 	##### URL de notificação  ########
	-->
	<div class="config-area">
		<h3 title="URL de notificação" class="title-text title-3 title">{l s='URL de Notificação' mod='pagseguro'}</h3>
		<p>
			{l s='Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.' mod='pagseguro'}
		</p>
		<div class="config-field">
   			<input type="text" class="pagseguro-field" name="pagseguroNotificationUrl" id="pagseguro-notificationurl-input" value="{$notificationUrl|escape:'none'}" maxlength="255">
		</div>
	</div>


	<!--
		##################################
	 	##### URL de redirecionamento  ###
	-->	
	<div class="config-area">
		<h3 title="URL de redirecionamento" class="title-text title-3 title">{l s='URL de Redirecionamento' mod='pagseguro'}</h3>
		<p>
			{l s='Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API.' mod='pagseguro'}
			<a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank">
			{l s='Clique aqui para ativar este serviço.' mod='pagseguro'}</a>
		</p>
		<div class="config-field">
   			<input type="text" class="pagseguro-field" name="pagseguroRedirectUrl" id="pagseguro-redirecturl-input" value="{$redirectUrl|escape:'none'}" maxlength="255">
		</div>
	</div>


	<div class="config-area">
		
		<h3 title="Geração de log" class="title-text title-3 title">{l s='Geração de log' mod='pagseguro'}</h3>
		
		<!--
			##################################
		 	##### Habilitar Log  #############
		-->
		<div class="config-sub-area">
			<div class="config-field">
				<label for="pagseguro-logactive-input">{l s='Habilitar a geração de log?' mod='pagseguro'}</label>
       			<select class="pagseguro-field" name="pagseguroLogActive" id="pagseguro-logactive-input">
            		{html_options values=$logActiveKeys output=$logActiveValues selected=$logActiveSelected|escape:'none'}
        		</select>
			</div>
		</div>

		<!--
			##################################
		 	##### Diretótrio de log  #########
		-->
		<div class="config-sub-area" id="logfilelocation-area">
			<label for="pagseguro-logfilelocation-input">{l s='Definir diretótrio de log' mod='pagseguro'}</label>
			<p>
				{l s='Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log' mod='pagseguro'}
	    	</p>		
			<div class="config-field">
       			<input type="text" class="pagseguro-field" name="pagseguroLogFileLocation" id="pagseguro-logfilelocation-input" value="{$logFileLocation|escape:'none'}"/>
			</div>
		</div>

	</div>


	<div class="config-area">
		
		<div class="title-wrapper title-wrapper-3">
			<h3 title="{l s='Transações abandonadas' mod='pagseguro'}" class="title-text title-3 title">{l s='Transações abandonadas' mod='pagseguro'}</h3>
		</div>
		
		<!--
			##################################
		 	##### Transações abandonadas  ####
		-->		
		<div class="config-sub-area">
			<label for="pagseguro-recoveryactive-input">{l s='Listar transações abandonadas?' mod='pagseguro'}</label>
			<p>
				{l s='Ao ativar esta funcionalidade, você poderá listar as transações abandonadas e disparar, manualmente, um e-mail para seu comprador. Este e-mail conterá um link que o redirecionará para o fluxo de pagamento, exatamente no ponto onde ele parou.' mod='pagseguro'}
	   		</p>	
			<div class="config-field">
       			<select class="pagseguro-field" name="pagseguroRecoveryActive" id="pagseguro-recoveryactive-input">
            		{html_options values=$recoveryActiveKeys output=$recoveryActiveValues selected=$recoveryActiveSelected|escape:'none'}
        		</select>
			</div>
		</div>

	</div>

</form>