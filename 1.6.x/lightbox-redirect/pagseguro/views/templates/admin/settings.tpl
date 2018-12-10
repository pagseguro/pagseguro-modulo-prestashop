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

<h2 class="title" title="Configuração">Configuração</h2>

<p>Aqui você pode configurar o módulo PagSeguro no PrestaShop.</p>


<form id="pagseguro-config-form" action="{$action_post|escape:'none'}" method="POST">

    <input type="hidden" name="pagseguroModuleSubmit">

    <div class="config-area">

        <h3 title="Credenciais" class="title-text title-3 title">Credenciais</h3>

        <!--
                ##################################
                ##### E-mail  ####################
        -->	
        <div class="config-sub-area">
            <label for="pagseguro-email-input">E-mail</label>
            <p>
                Para oferecer o PagSeguro em sua loja é preciso ter uma conta do tipo vendedor ou empresarial. Se você ainda não tem uma conta PagSeguro <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank">clique aqui</a>, caso contrário informe neste campo o e-mail associado à sua conta PagSeguro.
            </p>
            <div class="config-field">
                <input type="text" class="pagseguro-field" name="pagseguroEmail" id="pagseguro-email-input" value="{$email|escape:'htmlall':'UTF-8'}" maxlength="60">
            </div>
        </div>

        <!--
                ##################################
                ##### Token  #####################
        -->
        <div class="config-sub-area">
            <label for="pagseguro-token-input">Token</label>
            <p>
                Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro. Caso não tenha um token <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank"> clique aqui</a>, para gerar.
            </p>
            <div class="config-field">
                <input type="text" class="pagseguro-field" name="pagseguroToken" id="pagseguro-token-input" value="{$token|escape:'htmlall':'UTF-8'}" maxlength="32">
            </div>
        </div>

    </div>

    <div class="config-area">

        <h3 title="Ambiente" class="title-text title-3 title">Ambiente</h3>

        <!--
                ##################################
                ##### Environment  #####################
        -->
        <div class="config-sub-area">
            <div class="config-field">
                <select class="pagseguro-field" name="pagseguroEnvironment" id="pagseguro-environment-input">
                    <option value="production" {if $environment eq "production"}selected{/if}>Produção</option>
                    <option value="sandbox" {if $environment eq "sandbox"}selected{/if}>Sandbox</option>
                </select>
            </div>
        </div>

    </div>


    <!--
            ##################################
            ##### Charset  ###################
    -->
    <div class="config-area">
        <h3 title="Charset" class="title-text title-3 title">Charset</h3>
        <p>
            Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.
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
        <h3 title="Checkout" class="title-text title-3 title">Checkout</h3>

        <div class="config-field">
            <select class="pagseguro-field pagseguro-select-hint" name="pagseguroCheckout" id="pagseguro-checkout-input">
                {html_options values=$checkoutKeys output=$checkoutValues selected=$checkoutSelected|escape:'none'}
            </select>
        </div>

        <p class="pagseguro-option-hint" data-hint="0">No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.</p>

        <p class="pagseguro-option-hint" data-hint="1">No checkout lightbox o comprador, após escolher os produtos e/ou serviços, fará o pagamento em uma janela que se sobrepõe a sua loja.</p>

    </div>


    <!--
            ##################################
            ##### URL de notificação  ########
    -->
    <div class="config-area">
        <h3 title="URL de notificação" class="title-text title-3 title">URL de Notificação</h3>
        <p>
            Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.
        </p>
        <div class="config-field">
            <input type="text" class="pagseguro-field" name="pagseguroNotificationUrl" id="pagseguro-notificationurl-input" value="{$notificationUrl|escape:'htmlall':'UTF-8'}" maxlength="255">
        </div>
    </div>


    <!--
            ##################################
            ##### URL de redirecionamento  ###
    -->	
    <div class="config-area">
        <h3 title="URL de redirecionamento" class="title-text title-3 title">URL de Redirecionamento</h3>
        <p>
            Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API. <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank">Clique aqui</a> para ativar este serviço.
        </p>
        <div class="config-field">
            <input type="text" class="pagseguro-field" name="pagseguroRedirectUrl" id="pagseguro-redirecturl-input" value="{$redirectUrl|escape:'htmlall':'UTF-8'}" maxlength="255">
        </div>
    </div>


    <div class="config-area">

        <h3 title="Geração de log" class="title-text title-3 title">Geração de log</h3>

        <!--
                ##################################
                ##### Habilitar Log  #############
        -->
        <div class="config-sub-area">
            <div class="config-field">
                <label for="pagseguro-logactive-input">Habilitar a geração de log?</label>
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
            <label for="pagseguro-logfilelocation-input">Definir diretótrio de log</label>
            <p>
                Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log
            </p>		
            <div class="config-field">
                <input type="text" class="pagseguro-field" name="pagseguroLogFileLocation" id="pagseguro-logfilelocation-input" value="{$logFileLocation|escape:'htmlall':'UTF-8'}"/>
            </div>
        </div>

    </div>


    <div class="config-area">

        <div class="title-wrapper title-wrapper-3">
            <h3 title="Transações abandonadas" class="title-text title-3 title">Transações abandonadas</h3>
        </div>

        <!--
                ##################################
                ##### Transações abandonadas  ####
        -->		
        <div class="config-sub-area">
            <label for="pagseguro-recoveryactive-input">Listar transações abandonadas?</label>
            <p>
                Ao ativar esta funcionalidade, você poderá listar as transações abandonadas e disparar, manualmente, um e-mail para seu comprador. Este e-mail conterá um link que o redirecionará para o fluxo de pagamento, exatamente no ponto onde ele parou.
            </p>	
            <div class="config-field">
                <select class="pagseguro-field" name="pagseguroRecoveryActive" id="pagseguro-recoveryactive-input">
                    {html_options values=$recoveryActiveKeys output=$recoveryActiveValues selected=$recoveryActiveSelected|escape:'none'}
                </select>
            </div>
        </div>

    </div>

    <!--
            ##################################
            ##### Descontos ########
    -->
    <div class="config-area">
        <h3 title="Descontos" class="title-text title-3 title">Descontos</h3>
        
        <div class="config-sub-area">
            <div class="config-field">
                <label for="pagseguro-discount-creditcard-input">Oferecer desconto para cartão de crédito?</label>
                <select name="pagseguroDiscountCreditCardInput" class="pagseguro-field" id="pagseguro-discount-creditcard-input">
                    <option value="0" {if $discountCreditCard eq "0"}selected{/if}>Não</option>
                    <option value="1" {if $discountCreditCard eq "1"}selected{/if}>Sim</option>
                </select>
                
                <div class="config-field" id="pagseguro-discount-creditcard-discount-wrapper">
                    <label for="pagseguro-discount-creditcard-discount-input">Percentual de Desconto</label>
                    <input {if $discountCreditCardValue neq "00.00"}value="{$discountCreditCardValue|escape:'htmlall':'UTF-8'}"{/if} text="text" class="pagseguro-field" name="pagseguroDiscountCreditCardDiscountInput" id="pagseguro-discount-creditcard-discount-input" />
                </div>
            </div>
            
            <div class="config-field">
                <label for="pagseguro-discount-boleto-input">Oferecer desconto para boleto?</label>
                <select name="pagseguroDiscountBoletoInput" class="pagseguro-field" id="pagseguro-discount-boleto-input">
                    <option value="0" {if $discountBoleto eq "0"}selected{/if}>Não</option>
                    <option value="1" {if $discountBoleto eq "1"}selected{/if}>Sim</option>
                </select>
                <div class="config-field" id="pagseguro-discount-boleto-discount-wrapper">
                    <label for="pagseguro-discount-boleto-discount-input">Percentual de Desconto</label>
                    <input {if $discountBoletoValue neq "00.00"}value="{$discountBoletoValue|escape:'htmlall':'UTF-8'}"{/if} text="text" class="pagseguro-field" name="pagseguroDiscountBoletoDiscountInput" id="pagseguro-discount-boleto-discount-input" />
                </div>
            </div>
            
            <div class="config-field">
                <label for="pagseguro-discount-eft-input">Oferecer desconto para cartão débito eletrônico?</label>
                <select name="pagseguroDiscountEftInput" class="pagseguro-field" id="pagseguro-discount-eft-input">
                    <option value="0" {if $discountEFT eq "0"}selected{/if}>Não</option>
                    <option value="1" {if $discountEFT eq "1"}selected{/if}>Sim</option>
                </select>
                <div class="config-field" id="pagseguro-discount-eft-discount-wrapper">
                    <label for="pagseguro-discount-eft-input">Percentual de Desconto</label>
                    <input {if $discountEFTValue neq "00.00"}value="{$discountEFTValue|escape:'htmlall':'UTF-8'}"{/if} text="text" class="pagseguro-field" name="pagseguroDiscountEftDiscountInput" id="pagseguro-discount-eft-discount-input" />
                </div>
            </div>
            
            <div class="config-field">
                <label for="pagseguro-discount-deposit-input">Oferecer desconto para deposito em conta?</label>
                <select name="pagseguroDiscountDepositInput" class="pagseguro-field" id="pagseguro-discount-deposit-input">
                    <option value="0" {if $discountDeposit eq "0"}selected{/if}>Não</option>
                    <option value="1" {if $discountDeposit eq "1"}selected{/if}>Sim</option>
                </select>
                <div class="config-field" id="pagseguro-discount-deposit-discount-wrapper">
                    <label for="pagseguro-discount-deposit-discount-input">Percentual de Desconto</label>
                    <input {if $discountBalanceValue neq "00.00"}value="{$discountBalanceValue|escape:'htmlall':'UTF-8'}"{/if} text="text" class="pagseguro-field" name="pagseguroDiscountDepositDiscountInput" id="pagseguro-discount-deposit-discount-input" />
                </div>
            </div>
            
            <div class="config-field">
                <label for="pagseguro-discount-balance-input">Oferecer desconto para saldo PagSeguro?</label>
                <select name="pagseguroDiscountBalanceInput" class="pagseguro-field" id="pagseguro-discount-balance-input">
                    <option value="0" {if $discountBalance eq "0"}selected{/if}>Não</option>
                    <option value="1" {if $discountBalance eq "1"}selected{/if}>Sim</option>
                </select>
                <div class="config-field" id="pagseguro-discount-balance-discount-wrapper">
                    <label for="pagseguro-discount-balance-discount-input">Percentual de Desconto</label>
                    <input {if $discountBalanceValue neq "00.00"}value="{$discountBalanceValue|escape:'htmlall':'UTF-8'}"{/if} text="text" class="pagseguro-field" name="pagseguroDiscountBalanceDiscountInput" id="pagseguro-discount-balance-discount-input" />
                </div>
            </div>
        </div>
    </div>                

</form>