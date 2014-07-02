{*
* 2007-2011 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link type="text/css" rel="stylesheet" href="{$module_dir}assets/css/jquery.dataTables.min.css" />
<link type="text/css" rel="stylesheet" href="{$css_version}" />
<script type="text/javascript" src="{$module_dir}assets/js/jquery.min.js"></script>
<script type="text/javascript" src="{$module_dir}assets/js/jquery.blockUI.js"></script>
<script type="text/javascript" src="{$module_dir}assets/js/jquery-1102.min.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir}assets/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="{$module_dir}assets/js/dataTables.refresh.js"></script>
<form class="psplugin" id="psplugin" action="{$action_post}" method="POST">
    <h1>
        <img src="{$module_dir}assets/images/logops_228x56.png" />
        <span style="right : 0px">
            <a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank" class="pagseguro-button green-theme normal">
                {l s='Faça seu cadastro' mod='pagseguro'}
            </a>
        </span>
    </h1>    
    <ul id="menuTab">
    {foreach from=$tab item=li}
        <li id="menuTab{$li.tab|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $li.selected}selected{/if}">{if $li.icon != ''}<img src="{$li.icon|escape:'htmlall':'UTF-8'}" alt="{$li.title|escape:'htmlall':'UTF-8'}"/>{/if} {$li.title|escape:'htmlall':'UTF-8'}</li>
    {/foreach}
    </ul>
    <div id="tabList">
    {foreach from=$tab item=div}
        <div id="menuTab{$div.tab|escape:'htmlall':'UTF-8'}Sheet" class="tabItem {if $div.selected}selected{/if}">
            {$div.content}
        </div>
    {/foreach}
    </div>

    <div id="divSalvar">
        <p class="center" id="pSalvar">
        	<input type="submit" id='update' class='pagseguro-button green-theme normal' name='btnSubmit' value="Salvar" />
        </p>
    </div>

	<input type='hidden' id='hiddenMenuTab' name='menuTab' value='{$menu_tab}' />

</form>
<br>
<script type="text/javascript">
    {literal}

        var tip = 'O botão salvar só será habilitado quando os campos E-mail e Token forem preenchidos.';

        if(document.getElementById('pagseguro_email').value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

        }

        if(document.getElementById('pagseguro_token').value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

        }

        function validarForm(formInput){ 

            if (formInput == "pagseguro_email")
            {
                var nInput = 'pagseguro_token';
            } else {
                var nInput = 'pagseguro_email';
            }

            if(document.getElementById(formInput).value.length == ""){

                document.getElementById("pSalvar").innerHTML = "<a data-tooltip='"+this.tip+"' id='tooltip'><button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' disabled />Salvar</button></a>";

            } else if( (document.getElementById(formInput).value.length != "") & (document.getElementById(nInput).value.length != "")) {
                 document.getElementById('update').disabled=false;
                 document.getElementById("pSalvar").innerHTML = "<button id='update' class='pagseguro-button green-theme normal' name='btnSubmit' />Salvar</button>";
            }
        }

        var url = location.href;  
        var baseURL = url.substring(0, url.indexOf('/', 18));
        var paginaAtual = 1;
        var menuTab = 'menuTab1';

        
        $('.menuTabButton').live('click',
            function () {
                $('.menuTabButton.selected').removeClass('selected');
                $(this).addClass('selected');
                $('.tabItem.selected').removeClass('selected');
                $('#' + this.id + 'Sheet').addClass('selected');
                menuTab = this.id;
                document.getElementById('menuTab').value = menuTab;
                $("input[name=menuTab]").val(menuTab);
                hideInput(this.id);
        });

        
        function hideInput(menuTab) {

            if (menuTab == 'menuTab2') {
                if ($('select#pagseguro_log').val() == '0') {
                    if($('#directory-log').is(':visible')) {
                         $('#directory-log').hide();
                     }
                }
                if ($('select#pagseguro_recovery').val() == '0') {
                    if($('#directory-val-link').is(':visible')) {
                        $('#directory-val-link').hide();
                    }
                }
            }
        }

        $('#pagseguro_log').live('change',
            function(e) {
                $('#directory-log').toggle(300);
            }
        );

        $('#pagseguro_recovery').live('change',
            function(e) {
                $('#directory-val-link').toggle(300);
            }
        );
        
        function blockModal(block) {
            if(block == 1) {
                $.blockUI({
                    message: '<h1>Carregando...</h1>',
                    css: {
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#4f7743',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        opacity: 0.7,
                        color: '#90e874'
                    },
                    overlayCSS: { backgroundColor: 'gray' }
                });
            } else {
                setTimeout($.unblockUI, 1000);
            }
        }
        
        $("input[name = 'search']").live('click',
            function() {
                blockModal(1);
                paginaAtual = 0;
                reloadTable();
        });

        function reloadTable() {
            jQuery.ajax({
                    type: 'POST',
                    url: '../modules/pagseguro/features/conciliation/conciliation.php',
                    dataType : "json",
                    data: {dias: jQuery('#pagseguro_dias_btn').val()},
                    success: function(result) {
                        if (result != "") {
                            jQuery('#htmlgrid').dataTable().fnClearTable(true);           
                            jQuery('#htmlgrid').dataTable().fnAddData(result);
                            //jQuery('#htmlgrid').dataTable().fnStandingRedraw();
                        }
                        
                        blockModal(0);
                    },
                    error: function() {
                        blockModal(0);
                    }
                });
        }
        
        function editRedirect(rowId){
            var token = jQuery('#adminToken').val();
            var url = jQuery('#urlAdminOrder').val();

            window.open(url + '&id_order='+rowId+'&vieworder&token='+token);
            
        }
        
        function duplicateStatus(rowId,rowIdStatusPagSeg,rowIdStatusPreShop){

            if(rowIdStatusPagSeg != rowIdStatusPreShop && rowIdStatusPagSeg != ""){
                blockModal(1);
                jQuery.ajax({
                    type: 'POST',
                    url: '../modules/pagseguro/features/conciliation/conciliation.php',
                    data: {idOrder: rowId, newIdStatus: rowIdStatusPagSeg },
                    success: function(result) {
                        reloadTable();
                    },
                    error: function() {
                        blockModal(0);
                        alert('Não foi possível corrigir o Status.\nTente novamente');
                    }
                });
            }
        }

        $('#pagseguro_checkout').live('change',
            function(e) {
                if($('option:selected', this).attr('value') == 0) {
                    $('#pagseguro_checkout').attr('hint','No checkout padrão o comprador, após escolher os produtos e/ou serviços, é redirecionado para fazer o pagamento no PagSeguro.');
                } else {          
                    $('#pagseguro_checkout').attr('hint','No checkout lightbox o comprador, após escolher os produtos e/ou serviços, fará o pagamento em uma janela que se sobrepõe a sua loja.');
                }
                $('#pagseguro_checkout').focus();
            }
        );

        $('input, select').live('focus',
            function(e) {
                _$this = $(this);
                $(this).addClass('focus');
                $(this).parent().parent().find('.hintps').fadeOut(210, function() {
                    $(this).html(_$this.attr('hint')).fadeIn(210);
                });
            }
        );

        $('input, select').live('blur',
            function(e) {
                $(this).removeClass('focus');
            }
        );
        
        $(".tab").live('click',
            function(e){
                $(this).parent().parent().find('.hintps').fadeOut(5);
        });

        $('.alert, .conf').insertBefore('#mainps');

        $('.alert, .conf').live('click',
            function() {
                    $(this).fadeOut(450);
            }
        );

        setTimeout(function() {
            $('.conf').fadeOut(450);
        }, 3000);

    {/literal}
</script>
