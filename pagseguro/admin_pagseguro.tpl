{* * 2007-2011 PrestaShop * * NOTICE OF LICENSE * * This source file is
subject to the Academic Free License (AFL 3.0) * that is bundled with
this package in the file LICENSE.txt. * It is also available through the
world-wide-web at this URL: * http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to *
obtain it through the world-wide-web, please send an email * to
license@prestashop.com so we can send you a copy immediately. * *
DISCLAIMER * * Do not edit or add to this file if you wish to upgrade
PrestaShop to newer * versions in the future. If you wish to customize
PrestaShop for your * needs please refer to http://www.prestashop.com
for more information. * * @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2011 PrestaShop SA * @version Release: $Revision: 6594
$ * @license http://opensource.org/licenses/afl-3.0.php Academic Free
License (AFL 3.0) * International Registered Trademark & Property of
PrestaShop SA *} <link type="text/css" rel="stylesheet"
href="{$module_dir}assets/css/styles.css" /> <script
type="text/javascript"
src="{$module_dir}assets/js/jquery.min.js"></script> <script
type="text/javascript"
src="{$module_dir}assets/js/behaviors.js"></script> <form
class="psplugin" id="psplugin" action="{$action_post}" method="POST">
<h1> <img src="{$module_dir}assets/images/logops_228x56.png" /> <span>
{l s='Mais de 23 milhões de brasileiros já utilizam o PagSeguro.'
mod='pagseguro'} <br /> {l s='Faça parte você também!'
mod='pagseguro'} </span> </h1> <div id="mainps"> <ol> <li
class="ps-slide1"> <h2> <span>{l s='Como funciona'
mod='pagseguro'}</span> </h2> <div> <h2> {l s='Sem convênios. Sem taxa
mínima, adesão ou mensalidade.' mod='pagseguro'} </h2> <br /> <p> {l
s='PagSeguro é a solução completa para pagamentos online, que garante
a segurança de quem compra e de quem vende na web. Quem compra com
PagSeguro tem a garantia de produto ou serviço entregue ou seu dinheiro
de volta. Quem vende utilizando o serviço do PagSeguro tem o
gerenciamento de risco de suas transações*. Quem integra lojas ao
PagSeguro tem ferramentas, comissão e publicidade gratuita.'
mod='pagseguro'} </p> <p> {l s='Não é necessário fazer convênios com
operadoras. O PagSeguro é a única empresa no Brasil a oferecer todas
as opções em um só pacote. O PagSeguro não cobra nenhuma taxa para
você abrir sua conta, não cobra taxas mensais, não cobra multa caso
você queira parar de usar os serviços.' mod='pagseguro'} </p> <p> {l
s='Use PagSeguro para receber pagamentos de modo fácil e seguro. Comece
a aceitar em alguns minutos, pagamentos por cartões de crédito,
boletos e transferências bancárias online e alcance milhares de
compradores. Mesmo que você já ofereça outros meios de pagamento,
adicione o PagSeguro e ofereça a opção Carteira Eletrônica
PagSeguro. Milhões de usuários já usam o Saldo PagSeguro para compras
online, e compram com segurança, rapidez e comodidade.'
mod='pagseguro'} </p> <p class="small">{l s='* Gerenciamento de risco de
acordo com nossas' mod='pagseguro'} <a
href="https://pagseguro.uol.com.br/regras-de-uso.jhtml"
target="_blank">{l s='Regras de uso.' mod='pagseguro'}</a> </p> </div>
</li> <li class="ps-slide2"> <h2> <span>{l s='Crie sua conta'
mod='pagseguro'}</span> </h2> <div> <h2> {l s='A forma mais fácil de
vender' mod='pagseguro'} </h2> <br /> <ul> <li>{l s='Comece hoje a
vender pela internet' mod='pagseguro'}</li> <li>{l s='Venda pela
internet sem pagar mensalidade' mod='pagseguro'}</li> <li>{l s='Ofereça
parcelamento com ou sem acréscimo' mod='pagseguro'}</li> <li>{l
s='Venda parcelado e receba de uma única vez' mod='pagseguro'}</li>
<li>{l s='Proteção total contra fraudes' mod='pagseguro'}</li> </ul>
<br /> <a
href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor"
target="_blank" class="pagseguro-button green-theme normal"> {l s='Faça
seu cadastro' mod='pagseguro'} </a> </div> </li> <li class="ps-slide3">
<h2> <span>{l s='Configurações' mod='pagseguro'}</span> </h2> <div>
<label>{l s='E-MAIL' mod='pagseguro'}*</label> <br /> <input type="text"
name="pagseguro_email" id="pagseguro_email" value="{$email_user}"
maxlength="60" hint="{l s='Para oferecer o PagSeguro em sua loja é
preciso ter uma conta do tipo vendedor ou empresarial. Se você ainda
não tem uma conta PagSeguro <a
href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor"
target="_blank"> clique aqui </a>, caso contrário informe neste campo o
e-mail associado à sua conta PagSeguro.' mod='pagseguro'}" />
<br />
<label>{l s='TOKEN' mod='pagseguro'}*</label>
<br />
<input type="text" name="pagseguro_token" id="pagseguro_token"
	value="{$token_user}" maxlength="32"
	hint="{l s='Para utilizar qualquer serviço de integração do PagSeguro, é necessário ter um token de segurança. O token é um código único, gerado pelo PagSeguro. Caso não tenha um token <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank"> clique aqui </a>, para gerar.' mod='pagseguro'}" />
<br />
<label>{l s='URL DE REDIRECIONAMENTO' mod='pagseguro'}</label>
<br />
<input type="text" name="pagseguro_url_redirect"
	id="pagseguro_url_redirect" value="{$redirect_url}" maxlength="255"
	hint="{l s='Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar neste campo. Para utilizar essa funcionalidade você deve configurar sua conta para aceitar somente requisições de pagamentos gerados via API. <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank"> Clique aqui </a> para ativar este serviço.' mod='pagseguro'}" />
<br />
<label>{l s='URL DE NOTIFICAÇÃO' mod='pagseguro'}</label>
<br />
<input type="text" name="pagseguro_notification_url"
	id="pagseguro_notification_url" value="{$notification_url}"
	maxlength="255"
	hint="{l s='Sempre que uma transação mudar de status, o PagSeguro envia uma notificação para sua loja ou para a URL que você informar neste campo.' mod='pagseguro'}" />
<br />
<br />
<p class="small">* {l s='Campos obrigatórios' mod='pagseguro'}</p>
<div class="hintps _config"></div>
</div>
</li>
<li class="ps-slide4">
	<h2>
		<span>{l s='Extras' mod='pagseguro'}</span>
	</h2>
	<div>
		<label>{l s='CHARSET' mod='pagseguro'}</label><br /> <select
			id="pagseguro_charset" name="pagseguro_charset" class="select"
			hint="{l s='Informe a codificação utilizada pelo seu sistema. Isso irá prevenir que as transações gerem possíveis erros ou quebras ou ainda que caracteres especiais possam ser apresentados de maneira diferente do habitual.' mod='pagseguro'}">
			{html_options options=$charset_options selected=$charset_selected}
		</select> <label>{l s='LOG' mod='pagseguro'}</label><br /> <select
			id="pagseguro_log" name="pagseguro_log" class="select"
			hint="{l s='Deseja habilitar a geração de log?' mod='pagseguro'}">
			{html_options options=$active_log selected=$log_selected}
		</select> <br /> <span id="directory-log"> <label>{l s='DIRETÓRIO'
				mod='pagseguro'}</label><br /> <input type="text"
			id="pagseguro_log_dir" name="pagseguro_log_dir"
			value="{$diretorio_log}"
			hint="{l s='Diretório a partir da raíz de instalação do PrestaShop onde se deseja criar o arquivo de log. Ex.: /logs/log_ps.log' mod='pagseguro'}" />
		</span>
		<div class="hintps _extras"></div>
	</div>
</li>
</ol>
<noscript>
	<p>{l s='Please enable JavaScript to get the full experience.'
		mod='pagseguro'}</p>
</noscript>
</div>
<br />
<input type="hidden" name="activeslide" id="activeslide"
	value="{$checkActiveSlide}" />
<button id="update" class="pagseguro-button green-theme normal"
	name="btnSubmit">{l s='Atualizar' mod='pagseguro'}</button>
</form>
<br>
<script type="text/javascript">
    {literal}
        $('#mainps').liteAccordion({
            theme : 'ps',
            rounded : true,
            firstSlide : parseInt($('#activeslide').val()),
            containerHeight : 400,
            onTriggerSlide : function() {
                $('.hintps').fadeOut(400);
            }
        });

        $('li[class*=ps-slide] h2').on('click',
            function(e) {
                var active = /ps-slide(d)/;
                $('#activeslide').val( active.exec($(this).parent().attr('class'))[1] );
            }
        );

        $('#pagseguro_log').on('change',
            function(e) {
                $('#directory-log').toggle(300);
            }
        );

        $('input, select').on('focus',
            function(e) {
                _$this = $(this);
                $(this).addClass('focus');
                $(this).parent().parent().find('.hintps').fadeOut(210, function() {
                    $(this).html(_$this.attr('hint')).fadeIn(210);
                });
            }
        );

        $('input, select').on('blur',
            function(e) {
                $(this).removeClass('focus');
            }
        );

        $('#psplugin').on('submit',
            function(e) {
                //$('#mainps ol li:nth-child(3) h2').trigger('click');
            }
        );

        if ($('select#pagseguro_log').val() == '0'){
            $('#directory-log').hide();
        }

        $('.alert, .conf').insertBefore('#mainps');

        $('.alert, .conf').on('click',
            function() {
                    $(this).fadeOut(450);
            }
        );

        setTimeout(function() {
            $('.conf').fadeOut(450);
        }, 3000);
    {/literal}
</script>
