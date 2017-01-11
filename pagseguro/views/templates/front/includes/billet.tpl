<div class="item-tab" id="billet" role="tabpanel">
    <h3 class="title-tab">Boleto</h3>
    <form class="form-horizontal clearfix" name="form-bilit">
        <div class="form-group">
            <label class="col-xs-12 col-sm-2 control-label" for="card_cod">CPF/CNPJ</label>
            <div class="col-xs-12 col-sm-10">
                <input class="form-control cpf-cnpj-mask" id="document-boleto" name="document" type="text">
            </div>
        </div>
        <div align="right">
            Esta compra está sendo feita no Brasil <img src="{$modules_dir}/pagseguro/flag-origin-country.png">
        </div>
        <button class="btn-pagseguro cart_navigation --align-right" id="payment-boleto">Concluir</button>
    </form>
    <ul class="list-warning">
        <li>Imprima o boleto e pague no banco</li>
        <li>Ou pague pela internet utilizando o código de barras do boleto</li>
        <li>o prazo de validade do boleto é de 1 dia útil</li>
    </ul>
</div><!-- /.item-tab#bilet -->
