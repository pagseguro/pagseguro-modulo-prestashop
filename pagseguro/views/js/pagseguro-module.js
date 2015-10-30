/**
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
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
var PrestaShopPagSeguroModule = new function() {


    var General = new function() {

        this.init = function() {
        }

    };


    /* ************************************* */
    /* *********** MESSAGES **************** */
    /* ************************************* */
    var Messages = new function() {
        
        var wrapper = jQuery("#pagseguro-module-contents");

        var getHtml = function(options) {
            return '<div id="'+ options.id +'" class="pagseguro-msg pagseguro-msg-'+options.type+' pagseguro-msg-'+options.size+'"><'+options.tag+'>' + options.message + '</'+options.tag+'></div>';
        }

        var remove = function() {
            wrapper.find('.pagseguro-msg-error, .pagseguro-msg-success').remove();
        };

        var add = function(message, type) {
            var html = getHtml({
                id: 'pagseguro-main-message',
                message: message,
                type: type,
                size: 'small',
                tag: 'p'
            });
            remove();
            wrapper.prepend(html);
        };

        return {
            addError: function(message) {
                add(message, 'error');
            },
            addSuccess: function(message) {
                add(message, 'success');
            },
            remove: function() {
                remove();
            },
            getHtml: function(options) {
                return getHtml(options);
            }
        };

    };

    
    /* ************************************* */
    /* *********** MODAL **************** */
    /* ************************************* */
    var Modal = new function(){
        
        var opened = false;
        
        var defaults = {
            transition:"none",speed:300,initialWidth:"600",innerWidth:"525",initialHeight:"450",title:!1,opacity:.65,close:"fechar <strong>x</strong>",fixed:true
        };
        
        var _bindEvents = function(elements,o){
            var options = jQuery.extend({},defaults,o || {});
            $(elements).colorbox(options);
        };

        var open = function(o) {
            var options = jQuery.extend({},defaults,o || {});
            if( options.inline && options.avoidDefault ){
                if( !options.width && !options.innerHeight ){
                    options.innerWidth = parseInt($( options.href ).css('width').replace('px','')) + parseInt($( options.href ).css( 'padding-left' ).replace('px','')) + parseInt($( options.href ).css( 'padding-right' ).replace('px',''))
                }
                if( !options.height && !options.innerHeight  ){
                    options.innerHeight = parseInt($( options.href ).css('height').replace('px','')) + parseInt($( options.href ).css( 'padding-top' ).replace('px','')) + parseInt($( options.href ).css( 'padding-bottom' ).replace('px',''));
                }
            }
            jQuery.colorbox(options);
        };

        var showLoading = function() {
            if (jQuery('#pagseguro-loading-message:visible').length > 0) {
                return false;
            }
            var html = Messages.getHtml({
                id: 'pagseguro-loading-message',
                type: 'loading',
                size: 'medium',
                message: 'Aguarde...',
                tag: 'h3'
            });
            Messages.remove();
            open({
                html: html,
                width:  600,
                height: 600,
                overlayClose: false,
                escKey: false,
                close: false
            });
            $('#cboxClose').hide();
            resize();
        };

        var hideLoading = function(callback) {
            close(callback);
        };

        var message = function(type, message) {
            var html = Messages.getHtml({
                type: type,
                size: 'small',
                message: message,
                tag: 'h3'
            });
            open({
                html: html,
                width:  400,
                height: 400
            });
            resize();
        };

        var resize = function() {
            jQuery.colorbox.resize();
        };

        var close = function(callback) {
            jQuery.colorbox.close(callback);
        };

        var remove = function() {
            jQuery.colorbox.remove();
        };

        return {
            close : close,
            remove : remove,
            open : open,
            resize : resize,
            showLoading: showLoading,
            hideLoading: hideLoading,
            message: message
        }

    };



    /* ************************************* */
    /* *********** MENU **************** */
    /* ************************************* */
    var Menu = new function() {

        var wrapper  = jQuery("#pagseguro-module-menu");
        var saveForm = $("#pagseguro-save-wrapper");
        var body = $("html, body");
        var windowSel  = jQuery(window);
        var animating = false;

        var applyMenu = function() {

            var selectedClass = "selected";
            var allItems = wrapper.find(".menu-item");

            allItems.click(function(e){
                
                e.preventDefault();
                e.stopPropagation();
                
                if (!animating) {

                    animating = true;

                    var item = jQuery(this);
                    var id = item.attr("data-page-id");
                    var hasForm = item.attr("data-has-form");

                    allItems.removeClass(selectedClass);
                    item.addClass(selectedClass);

                    var showNewPage = function() {
                        
                        Messages.remove();

                        jQuery(".pagseguro-module-content").removeClass(selectedClass);
                        jQuery("#pagseguro-module-content-" + id).addClass(selectedClass);
                        
                        if (hasForm) {
                            saveForm.show();
                        } else {
                            saveForm.hide();
                        }

                        jQuery("#current-page-id").val(id);
                        animating = false;

                    };

                    if (windowSel.scrollTop() > 100) {
                        
                        body.animate({scrollTop:0}, 800, 'swing', function(){
                            setTimeout(showNewPage, 100);
                        });

                    } else {
                        showNewPage();
                    }

                };

                return false;

            });

        };

        var applySaveForm = function() {
            $("#pagseguro-save-button").click(function(){
                Modal.showLoading();
                $("#pagseguro-config-form").submit();
            });
        };

        var applyFixedPostion = function() {
            
            var initialPos      = wrapper.offset().top;
            var initialLeft     = wrapper.offset().left;
            var initialWidth    = wrapper.width();
            var fixedClass      = 'fixed';

            var resetFixed = function() {
                wrapper.css('width', '');
                wrapper.css('top', '');
                wrapper.removeClass(fixedClass);
            };

            var applyFixed = function(top) {
                if (!wrapper.hasClass('fixed')) {
                    wrapper.addClass(fixedClass);
                }
                wrapper.css('top', parseInt(top - initialPos, 10) + 'px');
                wrapper.width(initialWidth);
            };

            var getWindowTop = function() {
                var aditionalSum = jQuery(".page-head").length > 0 ? 100 : 0;
                return windowSel.scrollTop() + aditionalSum;
            };

            windowSel.scroll(function(e){

                var top = getWindowTop();

                if (top >= initialPos) {
                    applyFixed(top);
                } else {
                    resetFixed();
                }

            });

            windowSel.resize(function(){
                var wasFixed = wrapper.hasClass(fixedClass);
                resetFixed();
                initialWidth = wrapper.width();
                if (wasFixed){
                    applyFixed(getWindowTop());
                }
            });

        };

        var applyGotoConfig = function() {
            jQuery(".pagseguro-goto-configuration").click(function(){
                jQuery("#menu-item-1").trigger('click');
                jQuery("#pagseguro-email-input").focus();
            });
        };

        this.init = function(){
            applyFixedPostion();
            applyMenu();
            applySaveForm();
            applyGotoConfig();
        };

    };


    /* ************************************* */
    /* *********** PAGE SETTINGS *********** */
    /* ************************************* */
    var PageSettings = new function() {

        LogActioveBehavior = new function() {

            var active = function(input, area) {
                var isActiveValue = 1;
                var inputSelector = jQuery(input);
                var areaSelector  = jQuery(area);
                var checkActive = function() {
                    if (inputSelector.val() == isActiveValue){
                        areaSelector.show();
                    } else {
                        areaSelector.hide();
                    }
                };
                inputSelector.change(checkActive);
                checkActive();
            };

            this.init = function() {
                active("#pagseguro-logactive-input", "#logfilelocation-area");
            };

        };


        var OptionHintBehavior = new function() {

            var changeHint = function(selector) {

                selector.each(function(){
                    
                    var select  = jQuery(this);
                    var wrapper = select.parents(".config-area");

                    wrapper.find('.pagseguro-option-hint').hide();

                    var s = wrapper.find('.pagseguro-option-hint[data-hint=' + select.val() + ']');
                    s.show();

                });

            };

            this.init = function() {
                
                var select = jQuery(".pagseguro-select-hint");

                select.change(function(){
                    changeHint(jQuery(this));
                });

                changeHint(select);
                
                jQuery('#pagseguro-environment-input').change(function (event) {
                    var SANDBOX = "sandbox";
                    event.preventDefault();
                    if (jQuery('#pagseguro-environment-input :selected').val() == SANDBOX)
                    {
                        Modal.message('warning', "Suas transações serão feitas em um ambiente de testes. Nenhuma das transações realizadas nesse ambiente tem valor monetário.");
                    }
                });

            };

        };

        this.init = function() {
            OptionHintBehavior.init();
            LogActioveBehavior.init();
        };

    };


    /* ************************************* */
    /* *********** PAGE Conciliation ********** */
    /* ************************************* */
    var PageConciliation = new function() {

        var conciliationTable; // DataTable
        var defaultPage = 0;
        var conciliationButton = jQuery("#conciliation-button");
        
        var AdminData = {
            token: jQuery('#adminToken').val(),
            url: jQuery('#urlAdminOrder').val()
        };
        
        var requestService = function(options) {

            jQuery.ajax({
                type: 'POST',
                url: '../modules/pagseguro/features/conciliation/conciliation.php',
                dataType : "json",
                data: options.params,
                success: options.success,
                error: options.error,
                complete: options.complete
            });            

        };

        var onSearchSuccess = function(data, callback) {

            conciliationTable.fnClearTable(true);

            if (data.length > 0) {
                
                var result = new Array();

                for (var i in data) {

                    var transaction  = data[i];
                    var params       = 'reference='+transaction.orderId+'&amp;status='+transaction.pagSeguroStatusId;
                    var orderUrl     = (AdminData.url + '&amp;id_order=' + transaction.orderId + '&amp;vieworder&amp;token='+AdminData.token);
                    var orderLink    = '<a class="link" target="_blank" href="'+orderUrl+'"><i class="icon-external-link"></i>&nbsp;Ver&nbsp;detalhes</span>';
                    var checkbox     = '<input name="conciliationTransactions[]" type="checkbox" class="conciliation-transaction" value="'+params+'" id="conciliation-transaction-'+i+'">';
                    
                    result[i] = [
                        [checkbox],
                        [transaction.date],
                        [transaction.maskedOrderId],
                        [transaction.transactionCode],
                        [transaction.prestaShopStatus],
                        [transaction.pagSeguroStatus],
                        [orderLink]
                    ];

                }

                conciliationTable.fnAddData(result);

                if (typeof callback == 'function') {
                    callback();
                } else {
                    Modal.hideLoading();
                }

            } else {
                Modal.message('alert', "Não há transações para conciliação no período.");
            }

        };

        var searchService = function(callback) {
            
            Modal.showLoading();

            var searchdays = $("#pagseguro-conciliation-days-input").val();

            requestService({
                params: {
                    days: searchdays
                },
                success: function(response) {
                    onSearchSuccess(response.data, callback);
                },
                error: function() {
                    Modal.message('error', "Não foi possível obter os dados de conciliação.");
                }
            });

        };

        var conciliationService = function(params) {
            
            var onError = function(){
                Modal.message('error', 'Não foi possível realizar a conciliação.');
            };

            Modal.showLoading();

            requestService({
                params: params,
                success: function(response) {
                    if (response.success) {
                        
                        searchService(function() {
                            Modal.message('success', 'Conciliação realizada com sucesso.');
                        });

                    } else {
                        onError();
                    }
                },
                error: onError
            });

        };

        var doConciliation = function() {
            
            var dataSelector = jQuery('input[name="conciliationTransactions[]"]');

            if (dataSelector.filter(":checked").length > 0) {
                conciliationService(dataSelector.serialize());
            } else {
                Messages.addError('Selecione ao menos um item.');
            }

        };

        var prepareTable = function() {
            
            conciliationTable = jQuery("#conciliation-table").dataTable({
                
                bStateSave: true,    
                info: false,
                lengthChange: false,
                searching: false,
                pageLength: 10,

                oLanguage: {
                    sEmptyTable:"Realize uma pesquisa.",
                    oPaginate: {
                        sNext: 'Próximo',
                        sLast: 'Último',
                        sFirst: 'Primeiro',
                        sPrevious: 'Anterior'
                    }
                },

                aoColumnDefs: [
                   { 'bSortable': false, 'aTargets': [ 0, 6 ] }
                ],

                fnDrawCallback: function(data) {

                    var table = this;
                    var checkboxClass   = '.conciliation-transaction';
                    var checkboxes      = table.find(checkboxClass);
                    var selectAll       = table.find('.select-all');

                    var checkedSendButton = function() {
                        conciliationButton.attr('disabled', checkboxes.filter(':checked').length <= 0);
                    };
                    
                    checkedSendButton();
                    selectAll.unbind('click');
                    
                    var hasNotChecked = false;
                    if (checkboxes.length > 0) {
                        checkboxes.each(function(){
                            if (!jQuery(this).is(':checked')) {
                                hasNotChecked = true;
                            }
                        });
                        if ((!hasNotChecked && !selectAll.is(':checked'))  || (hasNotChecked && selectAll.is(':checked'))) {
                            selectAll.trigger('click');
                        }
                    }

                    selectAll.bind('click', function(e){
                        table.find(checkboxClass + ':checked').trigger('click');
                        if (jQuery(this).is(':checked')) {
                            table.find(checkboxClass).trigger('click');
                        }
                    });

                    checkboxes.unbind('click').bind('click', function(e){
                        var row = jQuery(this).parents('tr');
                        if (jQuery(this).is(':checked')) {
                            row.addClass('checked');
                        } else {
                            row.removeClass('checked');
                        }
                        checkedSendButton();
                    });

                },

                fnRowCallback: function(nRow, aData) {
                    
                    jQuery(nRow).find('td').unbind('click').bind('click', function(e){
                        var clickedEl = jQuery(e.target);
                        if (!clickedEl.is('a, input')) {
                            jQuery(nRow).find('.conciliation-transaction').trigger('click');
                        }
                    });

                }

            });

        };

        this.init = function() {
            
            prepareTable();
            
            jQuery('#conciliation-search-button').click(function () {
                searchService();
            });

            conciliationButton.click(function () {
                doConciliation();
            });            

        };

    };


    /* ************************************* */
    /* *********** PAGE ABANDONED ********** */
    /* ************************************* */
    var PageAbandoned = new function() {

        var transactionsTable; // DataTable
        var sendMultipleButton = jQuery("#send-email-button");
        var defaultPage = 0;
        
        var AdminData = {
            token: jQuery('#adminToken').val(),
            url: jQuery('#urlAdminOrder').val()
        };
        
        var onRequestTransactions = function(transactions, callback) {

            transactionsTable.fnClearTable(true);

            if (transactions.length > 0) {
                
                var result = new Array();

                for (var i in transactions) {

                    var transaction = transactions[i];
                    var viewUrl     = (AdminData.url + '&amp;id_order=' + transaction.reference + '&amp;vieworder&amp;token='+AdminData.token);
                    var viewLink    = '<a class="link" target="_blank" href="'+viewUrl+'"><i class="icon-external-link"></i>&nbsp;Ver&nbsp;detalhes</span>';
                    var params      = 'customer='+transaction.customerId+'&amp;reference='+transaction.reference+'&amp;recovery='+transaction.recoveryCode;
                    var checkbox    = '<input name="abandonedTransactions[]" type="checkbox" class="abandoned-transaction" value="'+params+'" id="abandoned-transaction-'+i+'">';
                    
                    result[i] = [
                        [checkbox],
                        [transaction.orderDate],
                        [transaction.maskedReference],
                        [transaction.expirationDate],
                        [transaction.sendRecovery],
                        [viewLink]
                    ];

                }

                transactionsTable.fnAddData(result);

                if (typeof callback == 'function') {
                    callback();
                } else {
                    Modal.hideLoading();
                }

            } else {
                Modal.message('alert', "Não há transações abandonadas no período.");
            }

        };

        var requestTransactions = function(callback) {
            
            Modal.showLoading();

            var recoveryDays = $("#pagseguro-daystorecovery-input").val();

            jQuery.ajax({
                url: '../modules/pagseguro/features/abandoned/abandoned.php',
                type: "GET",
                cache: false,
                dataType : "json",
                data: {'recoveryDays': recoveryDays },
                success: function(response) {
                    onRequestTransactions(response.transactions, callback);
                },
                error: function() {
                    Modal.message('error', "Não foi possível obter os dados de transações abandonadas.");
                }
            });

        };

        var sendMailRequest = function(params) {
            
            var onError = function(){
                Modal.message('error', 'Não foi possível enviar o(s) e-mail(s).');
            };

            Modal.showLoading();

            jQuery.ajax({
                
                type: "GET",
                cache: false,
                url: '../modules/pagseguro/features/abandoned/abandoned.php',
                data: params,
                dataType: 'json',
                
                success: function(response) {
                    if (response.success) {
                        
                        currentPage = (transactionsTable.api().page.info().page);

                        requestTransactions(function(table) {
                            Modal.message('success', 'E-mail(s) enviado(s) com sucesso.');
                            transactionsTable.fnPageChange(currentPage);
                        });

                    } else {
                        onError();
                    }
                },

                error: onError

            });

        };

        var sendMultipleEmails = function() {
            
            var dataSelector = jQuery('input[name="abandonedTransactions[]"]');

            if (dataSelector.filter(":checked").length > 0) {
                sendMailRequest(dataSelector.serialize());
            } else {
                Messages.addError('Selecione ao menos um item.');
            }

        };        

        var prepareTable = function() {
            
            transactionsTable = jQuery("#abandoned-transactions-table").dataTable({
                
                bStateSave: true,    
                info: false,
                lengthChange: false,
                searching: false,
                pageLength: 10,

                oLanguage: {
                    sEmptyTable:"Realize uma pesquisa.",
                    oPaginate: {
                        sNext: 'Próximo',
                        sLast: 'Último',
                        sFirst: 'Primeiro',
                        sPrevious: 'Anterior'
                    }
                },

                aoColumnDefs: [
                   { 'bSortable': false, 'aTargets': [ 0, 5 ] }
                ],

                fnDrawCallback: function(data) {

                    var table = this;
                    var checkboxClass   = '.abandoned-transaction';
                    var checkboxes      = table.find(checkboxClass);
                    var selectAll       = table.find('.select-all');

                    var checkedSendButton = function() {
                        sendMultipleButton.attr('disabled', checkboxes.filter(':checked').length <= 0);
                    };
                    
                    checkedSendButton();
                    selectAll.unbind('click');
                    
                    var hasNotChecked = false;
                    if (checkboxes.length > 0) {
                        checkboxes.each(function(){
                            if (!jQuery(this).is(':checked')) {
                                hasNotChecked = true;
                            }
                        });
                        if ((!hasNotChecked && !selectAll.is(':checked'))  || (hasNotChecked && selectAll.is(':checked'))) {
                            selectAll.trigger('click');
                        }
                    }

                    selectAll.bind('click', function(e){
                        table.find(checkboxClass + ':checked').trigger('click');
                        if (jQuery(this).is(':checked')) {
                            table.find(checkboxClass).trigger('click');
                        }
                    });

                    checkboxes.unbind('click').bind('click', function(e){
                        var row = jQuery(this).parents('tr');
                        if (jQuery(this).is(':checked')) {
                            row.addClass('checked');
                        } else {
                            row.removeClass('checked');
                        }
                        checkedSendButton();
                    });

                },

                fnRowCallback: function(nRow, aData) {
                    
                    if (parseInt(aData[4], 10) <= 0) {
                        jQuery(nRow).addClass('unread');
                    }

                    jQuery(nRow).find('td').unbind('click').bind('click', function(e){
                        var clickedEl = jQuery(e.target);
                        if (!clickedEl.is('a, input')) {
                            jQuery(nRow).find('.abandoned-transaction').trigger('click');
                        }
                    });
                }

            });

        };

        this.init = function() {
            prepareTable();
            jQuery('#search-abandoned-button').click(function () {
                requestTransactions();
            });
            sendMultipleButton.click(function () {
                sendMultipleEmails();
            });
        };

    };

    /* ************************************* */
    /* *********** DOCUMENT READY ********** */
    /* ************************************* */
   jQuery(document).ready(function() {
        General.init();
        Menu.init();
        PageSettings.init();
        PageConciliation.init();
        PageAbandoned.init();
    });


};

