<div class="item-tab current" id="credit-card" role="tabpanel">
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
                <input class="form-control credit-card-mask" id="card_num" name="card_num" pattern="[0-9]*" type="text" required>
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
                <div class="row">
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
        <div class="form-group form-selector display-none">
            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Parcelas</label>
                <div class="col-xs-12 col-sm-6">
                    <select id="card_installments" name="card_installments" class="form-control">
                        <option value="" disabled selected>Escolha o N° de parcelas</option>
                    </select>
                </div>
        </div>
        <div class="form-group credit-total display-none form-selector">
            <label class="col-xs-12 col-sm-2 control-label" for="card_installments">Total</label>
            <div class="col-xs-12 col-sm-10">
                <span id="card_total">R$ 00,00</span>
            </div>
        </div>
        <div align="right">
            Esta compra está sendo feita no Brasil <img src="{$modules_dir}/pagseguro/flag-origin-country.png">
        </div>
        <button class="btn-pagseguro --align-right" id="payment-credit-card">Concluir</button>
    </form>
</div><!-- /.item-tab#credit-card -->
