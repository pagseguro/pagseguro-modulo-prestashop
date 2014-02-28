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

<link type="text/css" rel="stylesheet" href="{$css_version}" />
<script type="text/javascript" src="{$module_dir}assets/js/jquery.min.js"></script>

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
    <p class="center"><button id="update" class="pagseguro-button green-theme normal" name="btnSubmit" />Salvar</button></p>
</form>
<br>
<script type="text/javascript">
    {literal}
        var url = location.href;  
        var baseURL = url.substring(0, url.indexOf('/', 18));

        $('.menuTabButton').live('click',
            function () {
                $('.menuTabButton.selected').removeClass('selected');
                $(this).addClass('selected');
                $('.tabItem.selected').removeClass('selected');
                $('#' + this.id + 'Sheet').addClass('selected');

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

        $.fn.pageMe = function(opts){
            var $this = this,
                defaults = {
                    perPage: 10,
                    paginationPerPage: 10,
                    showPrevNext: false,
                    hidePageNumbers: false
                },
                settings = $.extend(defaults, opts);

            var listElement = $('#resultTable');
            var perPage = settings.perPage;
            var paginationPerPage = parseInt(settings.paginationPerPage) - 1;
            var children = listElement.children();
            var pager = $('.pager');

            if (typeof settings.childSelector!="undefined") {
                children = listElement.find(settings.childSelector);
            }

            if (typeof settings.pagerSelector!="undefined") {
                pager = $(settings.pagerSelector);
            }

            var numItems = children.size();
            var numPages = Math.ceil(numItems/perPage);

            pager.data("curr",0);

            if (settings.showPrevNext){
                $('<li><a href="#" class="prev_link">«</a></li>').appendTo(pager);
            }

            var curr = 0;
            var i = 0;
            while(numPages > curr && (settings.hidePageNumbers==false)){
                if(i > paginationPerPage) {
                    $('<li class="li'+i+'"><a href="#" id='+i+' class="page_link">'+(curr+1)+'</a></li>').appendTo(pager);
                    $('li.li'+i).hide();
                } else {
                    $('<li class="li'+i+' ativo"><a href="#" id='+i+' class="page_link">'+(curr+1)+'</a></li>').appendTo(pager);
                }
                i++;
                curr++;
            }

          	$('.page_link').click(
                function(){
                    click(this.id);
                }
            );

            function click(id){
                var atual = parseInt(id);
                if($('li.li'+(atual-(paginationPerPage - 1))).hasClass("ativo") && atual < (numPages-1)) {
                    $('li.li'+(atual-paginationPerPage)).hide();
                    $('li.li'+(atual+1)).show();
                    $('li.li'+(atual-paginationPerPage)).removeClass("ativo");
                    $('li.li'+(atual+1)).addClass("ativo");
                } else if($('li.li'+(atual+(paginationPerPage - 1))).hasClass("ativo") && atual >= 1) {
                    $('li.li'+(atual+paginationPerPage)).hide();
                    $('li.li'+(atual-1)).show();
                    $('li.li'+(atual+paginationPerPage)).removeClass("ativo");
                    $('li.li'+(atual-1)).addClass("ativo");
                }
            }

            if (settings.showPrevNext){
                $('<li><a href="#" class="next_link">»</a></li>').appendTo(pager);
            }

            pager.find('.page_link:first').addClass('active');
            pager.find('.prev_link').hide();
            if (numPages<=1) {
                pager.find('.next_link').hide();
            }
          	pager.children().eq(1).addClass("active");

            children.hide();
            children.slice(0, perPage).show();

            pager.find('li .page_link').click(function(){
                var clickedPage = $(this).html().valueOf()-1;
                goTo(clickedPage,perPage);
                return false;
            });
            pager.find('li .prev_link').click(function(){
                previous();
                return false;
            });
            pager.find('li .next_link').click(function(){
                next();
                return false;
            });

            function previous(){
                click(parseInt(pager.data("curr")) - 1);
                var goToPage = parseInt(pager.data("curr")) - 1;
                goTo(goToPage);
            }

            function next(){
                click(parseInt(pager.data("curr")) + 1);
                goToPage = parseInt(pager.data("curr")) + 1;
                goTo(goToPage);
            }
            
            function goTo(page){
                var startAt = page * perPage,
                    endOn = startAt + perPage;

                children.css('display','none').slice(startAt, endOn).show();

                if (page>=1) {
                    pager.find('.prev_link').show();
                }
                else {
                    pager.find('.prev_link').hide();
                }
                
                if (page<(numPages-1)) {
                    pager.find('.next_link').show();
                }
                else {
                    pager.find('.next_link').hide();
                }

                pager.data("curr",page);
                pager.children().removeClass("active");
                pager.children().eq(page+1).addClass("active");

            }
        };
        
        window.onload = function() {
            $('table.gridConciliacao').pageMe({pagerSelector:'#myPager',showPrevNext:true});
        };	
        
        function editRedirect(rowId){
            var token = adminToken.value;
            window.location.href = baseURL + '/admin-loja/index.php?tab=AdminOrders&id_order='+rowId+'&vieworder&token='+token;
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
