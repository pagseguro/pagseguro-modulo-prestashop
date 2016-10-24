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
<div style="float: left; width: 100%;">
    {capture name=path}{l s='Pagamento via PagSeguro' mod='pagseguro'}{/capture}

    <h1 class="page-heading">{l s='Pagamento via PagSeguro' mod='pagseguro'}</h1>

    {assign var='current_step' value='payment'}
    {include file="$tpl_dir./order-steps.tpl"}

    {if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Seu carrinho de compras está vazio.' mod='pagseguro'}</p>
    {else}

    <section class="row">
        <h2 class="title-payment">Formas de pagamento</h2>
        <h4 class="method-payment">Escolha o método</h4>
        <nav class="tabs-pagseguro clearfix" id="tabs-payment">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a class="action js-tab-action" href="#credit-card" aria-controls="credit-card" role="tab" data-toggle="tab">
                        <i class="fa fa-credit-card fa-4x"></i>
                        <span class="name">Cartão de Crédito</span>
                    </a>
                </li><!-- /.item -->
                <li role="presentation">
                    <a class="action js-tab-action" href="#debit-online" aria-controls="debit-online" role="tab" data-toggle="tab">
                        <i class="fa fa-money fa-4x"></i>
                        <span class="name">Débito Online</span>
                    </a>
                </li><!-- /.item -->
                <li role="presentation">
                    <a class="action js-tab-action" href="#boleto" aria-controls="boleto" role="tab" data-toggle="tab">
                        <i class="fa fa-barcode fa-4x"></i>
                        <span class="name">Boleto</span>
                    </a>
                </li><!-- /.item -->
            </ul><!-- /.items -->
        </nav><!-- /.tabs-payment -->
        <div class="tab-content col-xs-12 col-md-8 col-md-offset-2">
            <div role="tabpanel" class="tab-pane active" id="credit-card">
                <h3 class="title-tab">Cartão de Crédito</h3>
                <form class="form-horizontal clearfix" name="form-credit">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control cpf-cnpj-mask" id="document-credit-card" name="document" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_num">Número do cartão</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control credit-card-mask" id="card_num" name="card_num" type="text" required>
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_holder_name">Nome impresso no cartão</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control" id="card_holder_name" name="card_holder_name" type="text" required>
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_holder_birthdate">Data de nascimento</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control date-mask" id="card_holder_birthdate" name="card_holder_birthdate" type="text" required="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_validate">Validade</label>
                        <div class="col-xs-12 col-sm-10">
                            <div class="row form-inline-childs">
                                <div class="col-xs-12 col-sm-6">
                                    <select class="form-control" id="card_expiration_month" name="card_validate">
                                        <option value="" disabled selected>Mês</option>
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <select id="card_expiration_year" name="card_validate" class="form-control">
                                        <option value="" disabled selected>Ano</option>
                                        {for $years=$cc_years to $cc_max_years}
                                            <option value="{$years}">{$years}</option>
                                        {/for}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_cod">Código de segurança</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control code-card-mask" id="card_cod" name="card_cod" type="text">
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group display-none">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Parcelas</label>
                        <div class="col-xs-12 col-sm-10">
                            <select id="card_installments" name="card_installments" class="form-control">
                                <option value="" disabled selected>Escolha o N° de parcelas</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group credit-total display-none">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Total</label>
                        <div class="col-xs-12 col-sm-10">
                            <span id="card_total">R$ 00,00</span>
                        </div>
                    </div>
                    <button class="btn-pagseguro --align-right" id="payment-credit-card">Concluir</button>
                </form>
            </div><!-- /.item-tab#credit-card -->
            <div role="tabpanel" class="tab-pane" id="debit-online">
                <h3 class="title-tab">Débito On-line</h3>
                <form class="form-horizontal clearfix" name="form-debit">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control cpf-cnpj-mask" id="document-debit" name="document" type="text">
                        </div>
                    </div><!-- /.form-group -->
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-6 control-label">Escolha seu banco abaixo onde deverá fazer o pagamento online.</label>
                        <div id="bankList" class="col-xs-12 col-sm-5 col-sm-offset-1">
                            <label class="radio">
                                <input type="radio" name="bank" id="optionsRadios1" value="1">
                                Itaú
                            </label>
                            <!-- <label class="radio">
                                <input type="radio" name="bank" id="optionsRadios2" value="2">
                                Bradesco
                            </label> -->
                            <label class="radio">
                                <input type="radio" name="bank" id="optionsRadios2" value="3">
                                Banrisul
                            </label>
                            <label class="radio">
                                <input type="radio" name="bank" id="optionsRadios2" value="4">
                                Banco do Brasil
                            </label>
                            <label class="radio">
                                <input type="radio" name="bank" id="optionsRadios2" value="5">
                                HSBC
                            </label>
                        </div>
                    </div><!-- /.form-group -->
                    <button class="btn-pagseguro --align-right" id="payment-debit">Concluir</button>
                </form>
            </div><!-- /.item-tab#debit-online -->
            <div role="tabpanel" class="tab-pane" id="boleto">
                <h3 class="title-tab">Boleto</h3>
                <form class="form-horizontal clearfix" name="form-bilit">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
                        <div class="col-xs-12 col-sm-10">
                            <input class="form-control cpf-cnpj-mask" id="document-boleto" name="document" type="text">
                        </div>
                    </div>
                    <button class="btn-pagseguro cart_navigation clearfix" id="payment-boleto">Concluir</button>
                </form>
                <ul class="list-warning">
                    <li>Imprima o boleto e pague no banco</li>
                    <li>Ou pague pela internet utilizando o código de barras do boleto</li>
                    <li>o prazo de validade do boleto é de 1 dia útil</li>
                </ul>
            </div><!-- /.item-tab#bilet -->
        </div><!-- /.tabs-content-->
    </section><!-- /.wrapper -->

    {/if}

    <input type="hidden" id="base-url" data-target="{$success_url}"/>

    {*<script type="text/javascript" charset="utf8" src="{$modules_dir}pagseguro/views/js/bootstrap.min.js"></script>*}
    <script type="text/javascript" charset="utf8" src="{$modules_dir}pagseguro/views/js/jquery.mask.min.js"></script>
    <script type="text/javascript" charset="utf8" src="{$pagseguro_direct_js}"></script>
    <script type="text/javascript">



                    $('#tabs-payment a').click(function (e) {
                        e.preventDefault()
                        $(this).tab('show')
                    })

                    ;(function()
                    {
                        $('#card_num').on('paste', function (e) {
                            e.preventDefault();
                            return false;
                        });
                    }($));

                    ;(function masksInputs($, undefined) {
                        $('.cpf-mask').mask('000.000.000-00');
                        $('.cnpj-mask').mask('00.000.000/0000-00');
                        $('.credit-card-mask').mask('0000 0000 0000 0000');
                        $('.code-card-mask').mask('000');
                        $('.date-mask').mask('00/00/0000', { placeholder: "__/__/____" });
                        $('.cpf-cnpj-mask').on('keyup', function() {
                            try {
                                $(this).unmask();
                            } catch(e) {
                                alert('Ops, algo deu errado!');
                            };
                            var isLength = $(this).val().length;
                            //9 is number optional, is fake the transtion two types mask
                            isLength <= 11 ? $(this).mask('000.000.000-009') : $(this).mask('00.000.000/0000-00');
                        });
                    }($));
                    ;(function tabsPagseguro($, undefined) {
                        var $action = $('.js-tab-action');
                        $action.on('click', function(e){
                            e.preventDefault();
                            var $itemtTab = $(this).parent('.item');
                            var isActive = $itemtTab.hasClass('--active');
                            if(!isActive) {
                                var $newTabId = $($(this).attr('href'));
                                $('#tabs-payment .item.--active').removeClass('--active'); //remove class the old tab selected
                                $('.item-tab.--current').removeClass('--current');
                                //add new tab selected
                                $itemtTab.addClass('--active');
                                $newTabId.addClass('--current');
                            } else {
                                return false;
                            }
                        });
                    }($));

                    function unmaskField($el, val = true) {
                        try {
                            if (val === true) {
                                var $el = $el.val();
                            }
                            return $el.replace(/[/ -. ]+/g, '').trim();
                        } catch(e) {
                            alert('Ops, algo deu errado! Recarregue a página');
                        };
                    };


                    //Event buttons methods buy types
                    $('#payment-boleto').on('click', function(e){
                        e.preventDefault();

                        $(this).attr('disable', 'disable');


                        var url = "{$action_url|escape:'htmlall':'UTF-8'}";
                        if (location.protocol === 'https:') {
                            url = url.replace("http", "https");
                        }
                        url = url.replace("&amp;","&");
                        url = url.replace("&amp;","&");
                        var document = unmaskField($('#document-boleto'));

                        var query = $.ajax({
                            type: 'POST',
                            url: url,
                            data : {
                                type : 'boleto',
                                document : document
                            },
                            success: function(response) {
                                var result = $.parseJSON(response);

                                if (result.success) {

                                    var form = $('<form>', {
                                        'action': $('#base-url').attr('data-target'),
                                        'method': 'POST'
                                    }).append(
                                            $('<input>', {
                                                'id': 'payment_url',
                                                'name' : 'payment_url',
                                                'value': result.payload.data.payment_link,
                                                'type': 'hidden'
                                            })
                                    ).append(
                                            $('<input>', {
                                                'id': 'payment_type',
                                                'name' : 'payment_type',
                                                'value': 'boleto',
                                                'type': 'hidden'
                                            })
                                    );;
                                    form.submit();

                                }
                            }
                        });
                    });


                    //Event buttons methods buy types
                    $('#payment-debit').on('click', function(e){
                        e.preventDefault();

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

                        var query = $.ajax({
                            type: 'POST',
                            url: url,
                            data : {
                                type : 'debit',
                                document : document,
                                bankid : bankId
                            },
                            success: function(response) {
                                var result = $.parseJSON(response);

                                if (result.success) {

                                    var form = $('<form>', {
                                        'action': $('#base-url').attr('data-target'),
                                        'method': 'POST'
                                    }).append(
                                            $('<input>', {
                                                'id': 'payment_url',
                                                'name' : 'payment_url',
                                                'value': result.payload.data.payment_link,
                                                'type': 'hidden'
                                            })
                                    ).append(
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

                        var url = "{$action_url|escape:'htmlall':'UTF-8'}";
                        if (location.protocol === 'https:') {
                            url = url.replace("http", "https");
                        }
                        url = url.replace("&amp;","&");
                        url = url.replace("&amp;","&");
                        var document = unmaskField($('#document-credit-card'));


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
                                        holder_birthdate: holderBirthdate
                                    },
                                    type: 'POST',
                                }).success(function (response) {

                                    window.location.href = $('#base-url').attr('data-target');
                                });
                            }
                        });
                    });

                    ;(function()
                    {
                        var kbinValue,
                                klength = 0,
                                klastLength = 0,
                                kunMasked;
                        $('#card_num').on('keyup', function () {
                            klastLength = klength;
                            klength = $(this).val().length;
                            //6 number + space of mask
                            if (klength == 7 && klastLength <= 7) {
                                kunMasked = unmaskField($(this).val(), false);
                                kbinValue = kunMasked.substring(0,6);
                                getBrandCard(kbinValue);
                            }
                        });
                    }($));

                    //get and showing brand credit card
                    function getBrandCard(cardBinVal) {
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
                                    }
                                });
                            }
                        });
                    };

                    ;(function calcTotal($, undefined) {
                        //Update the total value according with installments
                        $('#card_installments').on('change', function() {
                            var currency = parseFloat($(this).val()).toFixed(2);
                            $('#card_total').text('R$ ' + currency);
                        });
                    }($));
    </script>

</div>