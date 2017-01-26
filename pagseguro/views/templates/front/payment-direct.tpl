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
*  International Registered Trademark & Property of PrestaShop SA
*}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="{$modules_dir}pagseguro/views/css/pagseguro-tabs.css">
<div class="ps-modal-overlay">
    <div class="ps-loading">
        <img src="{$modules_dir}pagseguro/views/img/reload.svg" alt="icon loading page">
    </div>
</div>
<div>
    {capture name=path}
        {l s='Pagamento via PagSeguro' mod='pagseguro'}
    {/capture}
    <section class="ps-wrap">
        <h1 class="page-heading">
            {l s='Pagamento via PagSeguro' mod='pagseguro'}
        </h1>
        {assign var='current_step' value='payment'}
        {include file="$tpl_dir./order-steps.tpl"}

        {if isset($nbProducts) && $nbProducts <= 0}
        <section class="ps-tabs clearfix">
            <p class="warning">
                {l s='Seu carrinho de compras está vazio.' mod='pagseguro'}
            </p>
        </section>
        {else}
            <section class="ps-tabs clearfix">
                <div class="ps-heading">
                    <h2 class="title-payment">Formas de pagamento</h2>
                    <h4 class="method-payment">Escolha o método</h4>
                </div>
                {include file="$tpl_dir./../../modules/pagseguro/views/templates/front/includes/navbar.tpl"}
                <div class="tabs-wrap">
                    {include file="$tpl_dir./../../modules/pagseguro/views/templates/front/includes/credit-card.tpl"}
                    {include file="$tpl_dir./../../modules/pagseguro/views/templates/front/includes/debit.tpl"}
                    {include file="$tpl_dir./../../modules/pagseguro/views/templates/front/includes/billet.tpl"}
                </div><!-- /.tabs-content-->
            </section><!-- /.row -->
        {/if}
    </section>
    <input type="hidden" id="base-url" data-target="{$success_url}">
</div>

<script charset="utf8" src="{$modules_dir}pagseguro/views/js/jquery.mask.min.js"></script>
<script charset="utf8" src="{$modules_dir}pagseguro/views/js/vanilla-masker.min.js"></script>
<script charset="utf8" src="{$pagseguro_direct_js}"></script>
<script type="text/javascript" charset="utf8">
;(function(win, doc, $, undefined) {
    'use strict';

    (function(){
        var psModal = doc.querySelector('.ps-modal-overlay');
        var modalHTML = psModal.outerHTML;

        $('.ps-modal-overlay').remove();
        $('body').prepend(modalHTML);

        var Modal = function() {
            if($('body').hasClass('ps-modal-opened')){
                setTimeout(function(){
                    $('body').removeClass('ps-modal-opened');
                }, 500);
            } else {
                $('body').addClass('ps-modal-opened');
            }
        };
        win.Modal = Modal;
    }());


    ;(function() {
        $('#card_num').on('paste', function (e) {
            e.preventDefault();
            return false;
        });
    }());

    ;(function masksInputs() {
        VMasker(document.querySelector('.credit-card-mask')).maskPattern('9999 9999 9999 9999');
        VMasker(document.querySelector('.code-card-mask')).maskPattern('999');  
        VMasker(document.querySelector('.date-mask')).maskPattern('99/99/9999');

        $('.cpf-cnpj-mask').on('keyup', function() {
            try {
                VMasker($(this)).unMask();
            } catch(e) {
                console.info('Ops, algo deu errado!');
            };
            var isLength = $(this).val().length;
            //9 is number optional, is fake the transtion two types mask
            isLength <= 11 ? VMasker($(this)).maskPattern('999.999.999-999') : VMasker($(this)).maskPattern('99.999.999/9999-99');
        });
    }());

    ;(function tabsPagseguro() {
        var $action = $('.js-tab-action');
        $action.on('click', function(e){
            e.preventDefault();
            var $itemtTab = $(this).parent('.item');
            var isActive = $itemtTab.hasClass('active');
            if(!isActive) {
                var $newTabId = $($(this).attr('href'));
                $('#tabs-payment .item.active').removeClass('active'); //remove class the old tab selected
                $('.item-tab.current').removeClass('current');
                //add new tab selected
                $itemtTab.addClass('active');
                $newTabId.addClass('current');
            } else {
                return false;
            }
        });
    }());

    function unmaskField($el, val = true) {
        try {
            if (val === true) {
                var $el = $el.val();
            }
            $el = $el.replace(/[^0-9]+/g, '').trim();
            return $el;
        } catch(e) {
            console.info('Ops, algo deu errado! Recarregue a página');
        };
    };

    //Event buttons methods buy types
    $('#payment-boleto').on('click', function(e){
        e.preventDefault();
        Modal();

        $(this).attr('disable', 'disable');

        var url = "{$action_url|escape:'htmlall':'UTF-8'}";
        if (location.protocol === 'https:') {
            url = url.replace("http", "https");
        }
        url = url.replace("&amp;","&");
        url = url.replace("&amp;","&");
        var document = unmaskField($('#document-boleto'));
        var hash = PagSeguroDirectPayment.getSenderHash();

        var query = $.ajax({
            type: 'POST',
            url: url,
            data : {
                type : 'boleto',
                document : document,
                hash : hash
            },
            success: function(response) {
                var result = $.parseJSON(response);
                if (result.success) {

                    var form = $('<form>', {
                        'action': $('#base-url').attr('data-target'),
                        'method': 'POST'
                    })
                    .append(
                        $('<input>', {
                            'id': 'payment_url',
                            'name' : 'payment_url',
                            'value': result.payload.data.payment_link,
                            'type': 'hidden'
                        })
                    )
                    .append(
                        $('<input>', {
                            'id': 'payment_type',
                            'name' : 'payment_type',
                            'value': 'boleto',
                            'type': 'hidden'
                        })
                    );
                    form.submit();
                }
            }
        });
    });


    //Event buttons methods buy types
    $('#payment-debit').on('click', function(e){
        e.preventDefault();
        Modal();

        $(this).attr('disable', 'disable');

        var bankId = $("#bankList input[type='radio']:checked");
        if (bankId.length > 0) {
            bankId = bankId.val();
        }

        var url = "{$action_url|escape:'htmlall':'UTF-8'}";
        if (location.protocol === 'https:') {
            url = url.replace("http", "https");
        }
        url = url.replace("&amp;","&");
        url = url.replace("&amp;","&");
        var document = unmaskField($('#document-debit'));
        var hash = PagSeguroDirectPayment.getSenderHash();

        var query = $.ajax({
            type: 'POST',
            url: url,
            data : {
                type : 'debit',
                document : document,
                bankid : bankId,
                hash : hash
            },
            success: function(response) {
                var result = $.parseJSON(response);
                if (result.success) {
                    var form = $('<form>', {
                        'action': $('#base-url').attr('data-target'),
                        'method': 'POST'
                    })
                    .append(
                        $('<input>', {
                            'id': 'payment_url',
                            'name' : 'payment_url',
                            'value': result.payload.data.payment_link,
                            'type': 'hidden'
                        })
                    )
                    .append(
                        $('<input>', {
                            'id': 'payment_type',
                            'name' : 'payment_type',
                            'value': 'debit',
                            'type': 'hidden'
                        })
                    );
                    form.submit();
                }
            }
        });
    });

    $('#payment-credit-card').on('click', function(e){
        e.preventDefault();
        Modal();

        var url = "{$action_url|escape:'htmlall':'UTF-8'}";
        if (win.location.protocol === 'https:') {
            url = url.replace("http", "https");
        }
        url = url.replace("&amp;","&");
        url = url.replace("&amp;","&");
        var document = unmaskField($('#document-credit-card'));
        var hash = PagSeguroDirectPayment.getSenderHash();

        PagSeguroDirectPayment.createCardToken({
            cardNumber: unmaskField($('#card_num')),
            brand: $('#card-brand').attr('data-target'),
            internationalMode: $('#card-international').attr('data-target'),
            cvv: $('#card_cod').val(),
            expirationMonth: $('#card_expiration_month').val(),
            expirationYear: $('#card_expiration_year').val(),
            success: function(response) {
                var international = $('#card-international').attr('data-target');
                var quantity = $("#card_installments option:selected" ).attr('data-quantity');
                var amount = $("#card_installments option:selected" ).attr('data-amount');
                var holderName = $('#card_holder_name').val();
                var holderBirthdate = $('#card_holder_birthdate').val();
                jQuery.ajax({
                    url: url,
                    data: {
                        type : 'credit-card',
                        document : document,
                        card_token: response.card.token,
                        card_international: international,
                        installment_quantity: quantity,
                        installment_amount: amount,
                        holder_name: holderName,
                        holder_birthdate: holderBirthdate,
                        hash : hash
                    },
                    type: 'POST',
                })
                .success(function(response) {
                    win.location.href = $('#base-url').attr('data-target');
                });
            }
        });
    });

    //get and showing brand credit card
    function getBrandCard(cardBinVal) {
        Modal();
        PagSeguroDirectPayment.setSessionId('{$pagseguro_session}');
        PagSeguroDirectPayment.getBrand({
            cardBin: cardBinVal,
            internationalMode: true,
            success: function(response) {
                var query = $.ajax({
                    type: 'POST',
                    url: "{$installment_url}",
                    data: {
                        amount: {$cart->getOrderTotal(true)},
                        brand: response.brand.name,
                        international : response.brand.international
                    },
                    success: function (response) {

                        var result = $.parseJSON(response);

                        //remove if already exists installment options
                        jQuery('#card_installments option').each(function(){
                            if (!jQuery(this).val() === false) {
                                jQuery(this).remove();
                            }
                        });

                        //add installments options
                        jQuery.each(result.payload.data.installments, function (i, item) {
                            jQuery('#card_installments').append(jQuery('<option>', {
                                value: item.totalAmount,
                                text : item.text,
                                'data-amount': item.amount,
                                'data-quantity': item.quantity
                            }));
                        });
                        jQuery('.display-none').show();
                        Modal();
                    }
                });
            }
        });
    };

    ;(function() {
        var kbinValue;
        var klength = 0;
        var klastLength = 0;
        var kunMasked;
        var getbin = false;

        $('#card_num').on('keyup', function () {
            klastLength = klength;
            klength = $(this).val().length;

            //6 number + space of mask
            if (klength == 7 && getbin === false) {
                getbin = true;
                kunMasked = unmaskField($(this).val(), false);
                kbinValue = kunMasked.substring(0,6);
                getBrandCard(kbinValue);
            }

            if(klength < 7) {
                getbin = false;
            }
        });
    }());

    ;(function calcTotal() {
        //Update the total value according with installments
        $('#card_installments').on('change', function() {
            var currency = parseFloat($(this).val()).toFixed(2);
            $('#card_total').text('R$ ' + currency);
        });
    }());

}(window, document, jQuery, undefined));
</script>
