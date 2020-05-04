/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 */

// Variaveis
var valorPedido = '';
var numCartao = '';
var cardBin = '';
var bandeira = '';
var timeout = 30;
var ddd_validos = ['11','12','13','14','15','16','17','18','19','21','22','24','27','28','31','32','33','34','35','37','38','41','42','43','44','45','46','47','48','49','51','53','54','55','61','62','63','64','65','66','67','68','69','71','73','74','75','77','79','81','82','83','84','85','86','87','88','89','91','92','93','94','95','96','97','98','99'];

$(document).ready(function() {
	valorPedido = Number($('#valor_pedido').val());    
	ps_getSessionId();
	maxParcelas = Number($('#max_parcelas').val());
	parcelasSemJuros = Number($('#parcelas_sem_juros').val());
	parcelaMinima = Number($('#parcela_minima').val());

	$('#card_inst').change(function(e) {
		$('#valor_parcela').val(this.value);
	});
	$("#card_number").keyup(function(){
		var thisNum = $(this).val();
		if (thisNum.length == 6) {
			var brand = ps_getBrand(thisNum);
		}
	});
	$("#card_number").blur(function(e) {
		var brand = $('#ps_cartao_bandeira').val().toLowerCase();
		var thisNum = $(this).val();
		if (thisNum !== '') {
			if (ps_getBrand(thisNum) === '' || ps_getBrand(thisNum) === false) {
				$("#credit-icon").html('<i class="material-icons">credit_card</i>');
			}
		}else{
			$(this).parent().parent().removeClass('has-success').removeClass('has-danger');
			$(this).removeClass('form-control-success').removeClass('form-control-danger');
			$("#credit-icon").html('<i class="material-icons">credit_card</i>');
		}
		checkTokenGeneration();
	});
	$("#card_cvv").blur(function(e) {
		checkTokenGeneration();
	});
	$("#card_month").blur(function(e) {
		checkTokenGeneration();
	});
	$("#card_year").blur(function(e) {
		checkTokenGeneration();
	});
});

function ps_getSessionId() {
    var html = '';
    ps_msgFancyBox('1');
    
    $.ajax({
		url: urlFuncoes,
		type: 'POST',
		data: {acao: 'session'},
		cache: false,
		success: function(resposta) {
			if (resposta.length > 1) {
				var jresposta = JSON.parse(resposta);
				if (msg_console === 1) {
					console.log('Session ID: ' + jresposta.id);
				}
				PagSeguroDirectPayment.setSessionId(jresposta.id);
			}else {
				html += 'Clique F5 para tentar novamente.';
				showError(html,5);
			}
		},
		error: function(resposta) {
			if (msg_console === 1) {
				console.log(resposta.errors);
			}
			html += '<br>Clique F5 para tentar novamente.';
			var msgErro = ps_trataErro(resposta.errors);
			showError(msgErro & html,5);
		},
		complete: function() {
			$.fancybox.close();
			ps_setSenderHash();
			ps_getPaymentMethods(valorPedido);
			ps_getInstallments('visa');
		}
    });
}

function ps_setSenderHash() {
	PagSeguroDirectPayment.onSenderHashReady(function(response){
		if(response.status == 'error') {
			if (msg_console === 1) {
				console.log(response.message);
			}
			return false;
		}
	    var hash = response.senderHash;
		if (msg_console === 1) {
			console.log('SenderHash: ' + hash);
		}
		return true;
	});
}

function ps_getBrand(numCartao) {
	card_bin = numCartao.substring(0,6);
	PagSeguroDirectPayment.getBrand({
		cardBin: card_bin,
		success: function(resposta) {
			if (msg_console === 1) {
				console.log(resposta);
			}
			bandeira = resposta.brand.name;
			populateCard(bandeira);
			ps_getInstallments(bandeira);
			return bandeira;
		},
		error: function(resposta) {
			if (msg_console === 1) {
				console.log(resposta.errors);
			}
			var msgErro = ps_trataErro(resposta.errors);
			showError('Não foi possível obter a Bandeira do cartão. Verifique se o número está correto. <br>.' + msgErro, 5);
		},
		complete: function(resposta) {
		}
	});
}

function ps_getPaymentMethods(valor) {
	msg_indisponivel = '<div class="alert alert-danger"><p></p></div>';
    PagSeguroDirectPayment.getPaymentMethods({
    	amount: valor,
    	success: function(resposta) {
			if (msg_console === 1) {
	    	    console.log(resposta);
			}
    	    if(resposta.BOLETO == false || resposta.BOLETO == 'undefined'){
    	        $('#toggle-boleto').addClass('disabled').attr('title','Esta opção não está disponível! :(');
    	        $('#pagseguro-boleto').html(msg_indisponivel);
    	        //$('#boleto-tab').remove();				
    	    }
    	    if(resposta.CREDIT_CARD === false || resposta.CREDIT_CARD == 'undefined'){
    	        $('#toggle-credito').addClass('disabled').attr('title','Esta opção não está disponível! :(');
    	        $('#pagseguro-credito').html(msg_indisponivel);
    	        //$('#cartao-tab').remove();				
    	    }
    	    if(resposta.ONLINE_DEBIT === false || resposta.ONLINE_DEBIT == 'undefined'){
    	        $('#toggle-debito').addClass('disabled').attr('title','Esta opção não está disponível! :(');
    	        $('#debito-tab').html(msg_indisponivel);				
    	        //$('#pagseguro-debito').remove();
    	    }
    	    $.fancybox.close();
    	},
    	error: function(resposta) {
			if (msg_console === 1) {
	    	    console.log(resposta.errors);
			}
			var msgErro = ps_trataErro(resposta.errors);
			showError(msgErro,5);
    	},
    	complete: function(resposta) {
    	}
    });
}

function ps_getInstallments(bandeira) {
    if(parcelasSemJuros == 1){
	var maxparcelasSemJuros = 0;
    }else{
	var maxparcelasSemJuros = parcelasSemJuros;
    }
    PagSeguroDirectPayment.getInstallments({
		amount: valorPedido,
		maxInstallmentNoInterest: maxparcelasSemJuros,
		brand:  bandeira.toLowerCase(),
		success: function(resposta) {
			if (msg_console === 1) {
				console.log(resposta);
			}
			var parcelamento = resposta.installments[bandeira];
			if (typeof(maxParcelas) == 'undefined' || maxParcelas < 2) {
				var maxParcelas = + document.getElementById("max_parcelas").value;
			}
			var opts = DOMPurify.sanitize('<option value=""> -- </option>', {SAFE_FOR_JQUERY: true});
			for (var i in parcelamento) {
				var optionItem = parcelamento[i];
				var optionQuantidade = optionItem.quantity; 
				var optionValor = optionItem.installmentAmount; 
				if (optionItem.interestFree == true) {
					var strJuros = ' (sem juros)';
				}else {
					var strJuros = '';
				}
				var optionLabel = (optionQuantidade + ' x ' + formatMoney(optionValor) + strJuros); // Label do option
				var valor = Number(optionValor).toMoney(2,'.',',');
				
				if (optionItem.quantity <= maxParcelas) {
					if (valor >= parcelaMinima) {
						DOMPurify.sanitize(opts += '<option value="' + optionItem.quantity + '" dataPrice="' + valor + '">'+ optionLabel +'</option>', {SAFE_FOR_JQUERY: true});
					}
				}
			};
			$('#card_inst').html(opts);
		},
		error: function(resposta) {
			if (msg_console === 1) {
			    console.log(resposta.errors);
			}
			var html = 'Não foi possível obter os dados de parcelamento. <br>.';
			var msgErro = ps_trataErro(resposta.errors);
			showError(msgErro & html,5);
		},
		complete: function(resposta) {
		}
    });
}

function ps_finalizarCartao() {
	if (ps_validarCartao() !== false 
		&& $('#ps_cartao_token').val().length > 1 
		&& $('#ps_cartao_hash').val().length > 1
	){
		showLoading();
		return true;
    }else{
		return false;
	}
}

function checkTokenGeneration(){
	if (valCartao() !== false
		&& $('#card_cvv').val().length > 2
		&& $('#ps_cartao_bandeira').val().length > 1
		&& $('#card_month').val().length > 0
		&& $('#card_year').val().length > 0
	){
		return ps_createCardToken();
	}else{
		$("#payment-confirmation button[type='submit']").attr("disabled", true);
		return false
	}
}
function ps_createCardToken() {
	ps_setSenderHash();
	PagSeguroDirectPayment.createCardToken({
		cardNumber: $('#card_number').val().trim(),
		brand: $('#ps_cartao_bandeira').val().trim().toLowerCase(),
		cvv: $('#card_cvv').val(),
		expirationMonth: $('#card_month').val(),
		expirationYear: $('#card_year').val(),		    
		success: function(resposta) {
			$('#ps_cartao_token').val(resposta.card.token);
			var hashComprador = PagSeguroDirectPayment.getSenderHash();
			
			$('#ps_cartao_hash').val(hashComprador);
			if (msg_console === 1) {
				console.log(resposta);
				console.log('Cartão - TOKEN: ' + $('#ps_cartao_token').val());
				console.log('Cartão - HASH: ' + $('#ps_cartao_hash').val());
			}
			if ($('#ps_cartao_token').val().length > 0 && $('#ps_cartao_hash').val().length > 0){
				//showLoading();
				if (!$('#conditions-to-approve .custom-checkbox input[type=checkbox]').prop('required')) {
					$("#payment-confirmation button[type='submit']").removeAttr('disabled');
				}
				return true; //true
			}
		},
		error: function(resposta) {
			if (msg_console === 1) {
				console.log(resposta.errors);
			}
			var msgErro = ps_trataErro(resposta.errors);
			showError(msgErro,5);
			$.fancybox.close();
		},
		complete: function(resposta) {
		}
	});
}

function ps_finalizarBoleto() {
    if (ps_validarBoleto() !== false) {
		ps_setSenderHash();
		var hashComprador = PagSeguroDirectPayment.getSenderHash();
		$('#ps_boleto_hash').val(hashComprador);
		if ($('#ps_boleto_hash').val().length > 0) {
			showLoading();
			return true;
			//$('#boleto_pagseguropro').submit();	
		}
		if (msg_console === 1) {
			console.log(hashComprador);
		}
    }
}

function ps_finalizarTransf() {
    if (ps_validarTransf()) {
		ps_setSenderHash();
		var hashComprador = PagSeguroDirectPayment.getSenderHash();
		$('#ps_transf_hash').val(hashComprador);
		if ($('#ps_transf_hash').val().length > 0){
			showLoading();
			return true;
			//$('#debito_pagseguropro').submit();
		}
		if (msg_console === 1) {
			console.log(hashComprador);
		}
    }
}

function ps_informarParcela(id){
	var option = $('#'+id).find('option:selected');
	if (option.length) {
		$('#ps_cartao_valor_parcela').val(option.attr('dataPrice'));
		$('#ps_cartao_parcelas').val(option.val());
	}	
}

function ps_validarCartao() {
    var html = '';
	var errorFields = Array();
    var titular = $('#card_name').val().trim();    
    if (titular.length == 0) {
    	html += 'Titular do Cartão não preenchido. <br>';
		errorFields.push('card_name');
    }

    var dataNasc = $('#card_birth').val().replace(/[^0-9]/g,'');    
    if (dataNasc.length == 0) {
    	html += 'Data de Nascimento não preenchida. <br>';
		errorFields.push('card_birth');
    }

    var telefone = $('#card_phone').val().replace(/[^0-9]/g,'');    
    if (telefone.length == 0) {
    	html += 'Telefone não preenchido. <br>';
		errorFields.push('card_phone');
    }else{ 
		if (!validarTel('card_phone')) {
    		html += 'Telefone inválido. <br>';
			errorFields.push('card_phone');
		}
    }
    
    var cpf = $('#card_doc').val().replace(/[^0-9]/g,'');    
    if (cpf.length == 0) {
    	html += 'CPF não preenchido. <br>';
		errorFields.push('card_doc');
    }else if (!verifica('card_doc')) {
    	html += 'CPF inválido. <br>';
		errorFields.push('card_doc');
    }

    var numCartao = $('#card_number').val().replace(/[^0-9]/g,'');    
    if (numCartao.length == 0) {
    	html += 'Número do Cartão não preenchido. <br>';
		errorFields.push('card_number');
    }else if(valCartao() === false) {
    	html += 'Cartão inválido. Favor verificar. <br>';
		errorFields.push('card_number');
    }
    
    var mesVenc = $('#card_month').val().replace(/[^0-9]/g,'');    
    if (mesVenc.length == 0) {
    	html += 'Mês do Vencimento do Cartão não preenchido. <br>';
		errorFields.push('card_month');
    }
    
    var anoVenc = $('#card_year').val().replace(/[^0-9]/g,'');    
    if (anoVenc.length == 0) {
    	html += 'Ano do Vencimento do Cartão não preenchido. <br>';
		errorFields.push('card_year');
    }
    
    var codSeg = $('#card_cvv').val().replace(/[^0-9]/g,'');
    var brand = $('#ps_cartao_bandeira').val().trim().toLowerCase();
    if (codSeg.length == 0) {
    	html += 'Código de Segurança do Cartão não preenchido. <br>';
		errorFields.push('card_cvv');
    }else if(checkCVV(brand) === false){
    	html += 'Código de Segurança do Cartão inválido! Favor verificar. <br>';
		errorFields.push('card_cvv');
    }
    
    var parcelas = $('#card_inst').val();    
    if (parcelas.length == 0) {
		console.log('Parcelas: ' + parcelas);
    	html += 'Quantidade de Parcelas não preenchida. <br>';
		errorFields.push('card_inst');
    }
	var valor_parc = $('#ps_cartao_valor_parcela').val();
    if (valor_parc.length == 0) {
    	html += 'Valor das Parcelas não informado. <br>';
		errorFields.push('card_inst');
    }

    var endCobranca = $('#ps_cartao_endereco_cobranca').val().trim();    
    if (endCobranca.length == 0) {
	html += 'Endereço de Cobrança não preenchido. <br>';
	errorFields.push('ps_cartao_endereco_cobranca');
    }
	
    var numCobranca = $('#ps_cartao_numero_cobranca').val().trim();    
    if (numCobranca.length == 0) {
	html += 'Número do Endereço não preenchido. <br>';
	errorFields.push('ps_cartao_numero_cobranca');
    }

    var bairroCobranca = $('#ps_cartao_bairro_cobranca').val().trim();
    if (bairroCobranca.length == 0) {
	html += 'Bairro do Endereço não preenchido. <br>';
	errorFields.push('ps_cartao_bairro_cobranca');
    }
	
    var cidadeCobranca = $('#ps_cartao_cidade_cobranca').val().trim();
    if (cidadeCobranca.length == 0) {
	html += 'Cidade do Endereço não preenchido. <br>';
	errorFields.push('ps_cartao_cidade_cobranca');
    }
	
    var ufCobranca = $('#ps_cartao_uf_cobranca').val();
    if (ufCobranca.length == 0) {
	html += 'Estado do Endereço não preenchido. <br>';
	errorFields.push('ps_cartao_uf_cobranca');
    }
    
    if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
	for (var i = 0; i < errorFields.length; i++) {
		if (msg_console === 1) {
			console.log(errorFields[i]);
		}
		$('#'+errorFields[i]).parent().parent().addClass('has-danger').removeClass('has-success');
		$('#'+errorFields[i]).removeClass('form-control-success').addClass('form-control-danger');
	}
    }

    if (html.length > 0) {
		if (msg_console === 1) {
			console.log(html);
		}
		showError(html,5);
		$("#ps_endereco").collapse('show');
		return false;
    }else {
		return true;
    }
}

function ps_validarBoleto() {
    var html = '';
    var telefone = $('#boleto_phone').val().replace(/[^0-9]/g,'');
    if (telefone.length == 0) {
    	html += 'Telefone não preenchido. <br>';
    }else if (validarTel('boleto_phone') === false) {
		html += 'Telefone inválido. <br>';
    }
    
    var cpf = $('#boleto_doc').val().replace(/[^0-9]/g,'');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br>';
    }else {
		if (!verifica('boleto_doc')) {
			html += 'CPF/CNPJ inválido. <br>';
		}
    }
    
    if (html.length > 0) {
		showError(html,5);
		return false;
    }else {
		return true;
    }
}    

function ps_validarTransf() {
    var html = '';

    if (!$("input[name='ps_transf']:checked").val()) {
    	html += 'Banco não selecionado. <br>';
    }
    
    var telefone = $('#transf_phone').val().replace(/[^0-9]/g,'');
    if (telefone.length == 0) {
    	html += 'Telefone não preenchido. <br>';
    }else{ 
		if (!validarTel('transf_phone')) {
    		html += 'Telefone inválido. <br>';
    	}
	}
	
    var cpf = $('#transf_doc').val().replace(/[^0-9]/g,'');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br>';
    }else {
		if (cpf.length > 0 && !verifica('transf_doc')) {
			html += 'CPF/CNPJ inválido. <br>';
		}
    }

    if (html.length > 0) {
		showError(html,5);
		return false;
    }else {
		return true;
    }
}    

function ps_trataErro(erros) {	
    var html = '';
    if (typeof erros == 'object') {
		for (i in erros) {
		    html += ('' + erros[i] + '');
		}
    }
    return html;
}

function ps_validarCNPJ(cnpj) {
    var i = 0;
    var strMul = "6543298765432";
    var iLenMul = 0;
    var iSoma = 0;
    var strNum_base = 0;
    var iLenNum_base = 0;

    cnpj = cnpj.replace(/[^0-9]/g,'');

    if (cnpj.length == 0) {
        return true;
    }

    if (cnpj.length != 14 || cnpj == "00000000000000") {
        return false;
    }

    strNum_base = cnpj.substring(0, 12);
    iLenNum_base = strNum_base.length - 1;
    iLenMul = strMul.length - 1;

    for ( i = 0; i < 12; i++)
        iSoma = iSoma + parseInt(strNum_base.substring((iLenNum_base - i), (iLenNum_base - i) + 1), 10) * parseInt(strMul.substring((iLenMul - i), (iLenMul - i) + 1), 10);

    iSoma = 11 - (iSoma - Math.floor(iSoma / 11) * 11);

    if (iSoma == 11 || iSoma == 10)
        iSoma = 0;

    strNum_base = strNum_base + iSoma;
    iSoma = 0;
    iLenNum_base = strNum_base.length - 1;

    for ( i = 0; i < 13; i++)
        iSoma = iSoma + parseInt(strNum_base.substring((iLenNum_base - i), (iLenNum_base - i) + 1), 10) * parseInt(strMul.substring((iLenMul - i), (iLenMul - i) + 1), 10);

    iSoma = 11 - (iSoma - Math.floor(iSoma / 11) * 11);

    if (iSoma == 11 || iSoma == 10)
        iSoma = 0;

    strNum_base = strNum_base + iSoma;

    if (cnpj != strNum_base) {
        return false;
    }

    return true;

}

function ps_validarDDD(fone) {
	var clean = fone.replace(/\D/g,'').trim();
	var ddd = clean.substring(0, 2);
	var dddExists = $.inArray(ddd, ddd_validos);
    
    if (dddExists === false) {
		return false;
    }
    return true;
}

function ps_msgFancyBox(tipoMsg) {
    var html = '';

    switch (tipoMsg) {
		case '1':
			html =  '<img src="' + urlImg + 'loading.gif" alt="Carregando" width="20" height="20" style="display:block; margin:auto;" />';
			break;    
    }

    $.fancybox.open([{
        modal : true,
        type : 'image',
        href : urlImg + 'loading.gif',
        closeBtn  : false,
        closeClick  : false,
        fitToView: false,
        autoSize: false,
        autoDimensions: false,
        autoHeight : false,
        autoWidth : false,
		width: 10,
		height: 10,
        padding : 0,
		helpers:  {
			overlay : {
				closeClick: false,
				lock: true
			}
		},
		afterShow: function() {	    
		},
		afterClose: function() {	    
		}
    }]);
}

var formatMoney = function(valor) {
    var valorAsNumber = Number(valor);
    return 'R$ ' + valorAsNumber.toMoney(2,',','.');
};

Number.prototype.toMoney = function(decimals, decimal_sep, thousands_sep) {
    var n = this,
    c = isNaN(decimals) ? 2 : Math.abs(decimals),
    d = decimal_sep || '.', 
    t = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    sign = (n < 0) ? '-' : '',
    i = parseInt(n = Math.abs(n).toFixed(c)) + '', 
    j = ((j = i.length) > 3) ? j % 3 : 0; 
    return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : ''); 
};

function updateActiveOption(option) {
	$('html, body').animate({
		scrollTop: $('#opc_payment_methods').offset().top
	}, 1000);
}

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);
    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre)
}

function populateSelect(brand) {
	if(brand == 'Hipercard') {
		$('#card_number').attr('maxlength', 19);
	}else{
		$('#card_number').attr('maxlength', 16);
	}
    var sbrand = brand.toLowerCase();
    $("#loader").css("display","none");
	$(".images").find("div > img").removeClass("selected");
    $("#"+ brand).addClass("selected");
    $("#card_association").val(brand);
}

function updateValue() {
    var text = $("#installments option:selected").text();
    $("#installmentValue").val(text) ;
}

function ps_validarCPF(id){
	var cpfField = $('#'+id);
	var cpf = cpfField.val();
	var err = 0;	
	exp = /\.|\-/g
	cpf = cpf.toString().replace(exp,""); 	
	if(cpf.length !== 11 || cpf === "00000000000" || cpf === "11111111111" || cpf === "22222222222" || cpf === "33333333333" || cpf === "44444444444" || cpf === "55555555555" || cpf === "66666666666" || cpf === "77777777777" || cpf === "88888888888" || cpf === "99999999999") {
		err = 1;
	}
	soma = 0;
	for(i = 0; i < 9; i++)
	{
		soma += parseInt(cpf.charAt(i)) * (10 - i);
	}	
	resto = 11 - (soma % 11);
	if(resto == 10 || resto == 11) {
		resto = 0;
	}
	if(resto != parseInt(cpf.charAt(9))) {
		err = 1;
	}	
	soma = 0;
	for(i = 0; i < 10; i ++)
	{
		soma += parseInt(cpf.charAt(i)) * (11 - i);
	}
	resto = 11 - (soma % 11);
	if(resto == 10 || resto == 11) {
		resto = 0;
	}	
	if(resto != parseInt(cpf.charAt(10))) {
		err = 1;
	}
	if (err == 0) {	
		cpfField.parent().parent().removeClass('has-danger').addClass('has-success');
		cpfField.addClass('form-control-success').removeClass('form-control-danger');
		return true;
	}else{
		cpfField.parent().parent().addClass('has-danger').removeClass('has-success');
		cpfField.removeClass('form-control-success').addClass('form-control-danger');
		showError('CPF incorreto. Favor verificar.', 5);
    	return false;
	}
 }
 
function validarCNPJ(id){
	var cnpjField = $('#'+id);
	var cnpj = cnpjField.val();
	var valida = new Array(6,5,4,3,2,9,8,7,6,5,4,3,2);
	var dig1= new Number;
	var dig2= new Number;
	
	exp = /\.|\-|\//g;
	cnpj = cnpj.toString().replace( exp, "" ); 
	var digito = new Number(eval(cnpj.charAt(12)+cnpj.charAt(13)));
			
	for(i = 0; i<valida.length; i++){
			dig1 += (i>0? (cnpj.charAt(i-1)*valida[i]):0);  
			dig2 += cnpj.charAt(i)*valida[i];       
	}
	dig1 = (((dig1%11)<2)? 0:(11-(dig1%11)));
	dig2 = (((dig2%11)<2)? 0:(11-(dig2%11)));
	
	if(cnpj === '') {
		cnpjField.parent().parent().removeClass('has-danger').removeClass('has-success');
		cpfField.removeClass('form-control-success').removeClass('form-control-danger');
	} else if(((dig1*10)+dig2) == digito) {
		cnpjField.parent().parent().removeClass('has-danger').addClass('has-success');
		cnpjField.removeClass('form-control-danger').addClass('form-control-success');
		return true;
	}else {
		cnpjField.parent().parent().addClass('has-danger').removeClass('has-success');
		cnpjField.addClass('form-control-danger').removeClass('form-control-success');
		showError('CNPJ incorreto. Favor verificar.', 5);
		return false;
	}
}

function valCartao() {
	"use strict";
	var cardField = $("#card_number");
	var creditCardNumber = cardField.val().replace(/[\s+|\.|\-]/g, '');
	var cardType = ps_getBrand(creditCardNumber);
	if (cardType === "hipercard") {
		cardField.parent().parent().removeClass('has-danger').addClass('has-success');
		cardField.removeClass('form-control-danger').addClass('form-control-success');
		return true; // hipercard não valida.
	} else {
		// Luhn algorithm
		var checksum = 0;
		for (var i=(2-(creditCardNumber.length % 2)); i<=creditCardNumber.length; i+=2) {
			checksum += parseInt(creditCardNumber.charAt(i-1), 10);
		}
		for (i=(creditCardNumber.length % 2) + 1; i<creditCardNumber.length; i+=2) {
			var digit = parseInt(creditCardNumber.charAt(i-1), 10) * 2;
			if (digit < 10) { 
				checksum += digit; 
			} else { 
				checksum += (digit-9); 
			}
		}
		if ((checksum % 10) === 0) {
			cardField.parent().parent().removeClass('has-danger').addClass('has-success');
			cardField.removeClass('form-control-danger').addClass('form-control-success');
			if (msg_console === 1) {
				console.log('cartão válido!');
			}
			return true; 
		} else {
			cardField.parent().parent().addClass('has-danger').removeClass('has-success');
			cardField.addClass('form-control-danger').removeClass('form-control-success');
			if (msg_console === 1) {
				console.log('Cartão inválido: ' + cardField.val());
			}
			showError('Cartão inválido: ' + cardField.val(), 7);
			return false;
		}
	}
}

function checkCVV(bandeira) {
	"use strict";
	var cvvField = $("#card_cvv");
	if (bandeira && bandeira != 'undefined') {
		var brand = bandeira.toLowerCase();
	}else{
	    var brand = $('#ps_cartao_bandeira').val().toLowerCase();
	}
	if (cvvField.val()) {
		if (brand == 'amex' && cvvField.val().length != 4 || brand != 'amex' && cvvField.val().length != 3) {
			cvvField.parent().parent().addClass('has-danger').removeClass('has-success');
			cvvField.addClass('form-control-danger').removeClass('form-control-success');
			if (msg_console === 1) {
				console.log('CVV inválido. '+ brand +' com '+ cvvField.val().length +' caracteres.');
			}
			showError('Código de Validação inválido. '+ brand.toUpperCase() +' com '+ cvvField.val().length +' caracteres.', 7);
			return false;
		}else{
			cvvField.parent().parent().addClass('has-success').removeClass('has-danger');
			cvvField.addClass('form-control-success').removeClass('form-control-danger');
			return true;
		}
	}
}

function validarTel(id) {
	"use strict";
	//return true;
	var foneField = $("#" + id);
	var fone = foneField.val()
	if (!fone || fone === false) {
		fone = $('#card_phone').val();
	}
	if(fone === '') {
		foneField.parent().parent().removeClass('has-danger').removeClass('has-success');
		foneField.removeClass('form-control-danger').removeClass('form-control-success');
		return;
	}

	var clean = fone.replace(/\D/g,"").trim();
	var reg = /^[1-9]{2}[2-9][0-9]{7,8}$/;	
	var ddd = clean.substring(0, 2);
	var checkfone = reg.test(clean);

	if(checkfone !== false) {
		var dddExists = $.inArray(ddd, ddd_validos);
		if (dddExists < 0) {
			if (msg_console === 1) {
				console.log('DDD não encontrado (' + ddd + ')');
			}
			foneField.parent().parent().addClass('has-danger').removeClass('has-success');
			foneField.addClass('form-control-danger').removeClass('form-control-success');
			showError('DDD não encontrado (' + ddd + ')', 5);
			return false;
		}else{
			if (msg_console === 1) {
				console.log('(' + ddd + ') DDD válido!');
				console.log('Fone: ' + clean);
			}
			foneField.parent().parent().removeClass('has-danger').addClass('has-success');
			foneField.removeClass('form-control-danger').addClass('form-control-success');
			return true;
		}
	}else{
		if (msg_console === 1) {
			console.log('Fone: ' + clean);
		}
		foneField.parent().parent().addClass('has-danger').removeClass('has-success');
		foneField.addClass('form-control-danger').removeClass('form-control-success');
		showError('Telefone inválido: ' + fone, 5);
		return false;
	}
}

function showLoading() {
	"use strict";
	/*$('#submitCard').prop("disabled",true);
	$('#submitBankSlip').prop("disabled",true);*/
	$('#pagseguroproproccess').show();
	$('#fancy_load').addClass('loading').css("width", $(window).width());
}

function noCopy(teclapress) {
	"use strict";
	var tecla;
	if(navigator.appName === "Netscape") {
		tecla = teclapress.which;
	}else {
		tecla = teclapress.keyCode;
	}	
	var ctrl = teclapress.ctrlKey;
	
	if (ctrl && tecla===67) {
		return false;
	}
	if (ctrl && tecla===86) {
		return false;
	}
}

function Digitar(tecla) {
	"use strict";
	var maxLength = document.getElementById("card_cvv").getAttribute("maxlength");
	if (document.getElementById("card_cvv").value.length >= maxLength) {
		return false;
	}
	return document.checkout.card_cvv.value += tecla;
}

function clearCvc() {
	"use strict";
	document.checkout.card_cvv.value= "";
}

function populateCard(brand) {
	"use strict";
	$('#ps_cartao_bandeira').val(bandeira);
	$("#credit-icon, .card-brand").html('<img class="addon-img" src="'+ url_img + bandeira.toLowerCase() + '-mini.png" alt="' + bandeira + '" />');
	if (brand === 'aura') {
		$('#card_number').attr('maxlength', 19);
	}else{
		$('#card_number').attr('maxlength', 16);
	}
	if (brand === 'amex') {
		$('#card_cvv').attr('maxlength', 4);
	}else{
		$('#card_cvv').attr('maxlength', 3);
	}
}

function toggleVerso(action) {
	"use strict";
	if (action === 'add') {
		$('#card_container').addClass('verso');
	}else{
		$('#card_container').removeClass('verso');
	}
}

function sendToCard(str, classe) {
	"use strict";
	if (str.length > 1) {
		$('#card_container .' + classe).html(str);
		if(classe === 'card-number') {
			var txt = $('#number_card').html();
			$('#number_card').html(txt.replace(/(.{4})/g, '$1 '));
		}
	}
}

function showError(str, t) {
	"use strict";	
	$('#pagseguroprocontrolaErro').html(str).addClass('alert alert-danger').show();
	$('html, body').stop().animate({
		scrollTop: $('#checkout-payment-step').offset().top
	}, 300);
	setTimeout(function(){
		$('#pagseguroprocontrolaErro').html('').removeClass('alert alert-danger').hide();
	}, (1000 * t));
}

function parseValue(value) {
	return parseFloat(value).toFixed(2);
}

function verifica(id) {
	"use strict";	
	var cpf_cnpj = document.getElementById(id);
	var num = cpf_cnpj.value.replace(/\D/g,"");
	if (num.length > 12) {
		//console.log('CNPJ (' + num.length + ' dígitos).')
		mascara(cpf_cnpj, cnpjmask);
		if (!validarCNPJ(id)){
			return false;
		}
		return true;
	}else{
		//console.log('CPF (' + num.length + ' dígitos).')
		mascara(cpf_cnpj, cpfmask);
		if (!ps_validarCPF(id)){
			return false;
		}
		return true;
	}
}
