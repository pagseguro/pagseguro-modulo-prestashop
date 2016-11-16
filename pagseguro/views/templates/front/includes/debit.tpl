<div class="item-tab" id="debit-online" role="tabpanel">
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
