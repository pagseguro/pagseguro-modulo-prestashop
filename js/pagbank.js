/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author	  2011-2024 PrestaBR - https://prestabr.com.br
 * @copyright 1996-2024 PagBank - https://pagseguro.uol.com.br
 * @license	  Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

// Variáveis
var orderValue = '';
var cardNumber = '';
var cardBin = '';
var card_brand = '';
var timeout = 30;
var valid_area_codes = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', 
						'32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49',
						'51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73',
						'74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92',
						'93', '94', '95', '96', '97', '98', '99'];

$(document).ready(function() {
	//conditions
	var pay_options = document.querySelectorAll("input[name='payment-option']");
    var conditions_to_approve = document.getElementById('conditions_to_approve[terms-and-conditions]');
	var card_form = $('#card_pagbank');
	var bankslip_form = $('#bankslip_pagbank');
	var pix_form = $('#pix_pagbank');
	var pagbank_module = false;
	if (conditions_to_approve != null) {
		conditions_to_approve.addEventListener('change', function() {
			if (this.checked) {
				pay_options.forEach((option) => {
					if (option.dataset.moduleName == "pagbank" && option.checked) {
						pagbank_module = true;
					}
				});
				if (pagbank_module === true) {
					if (card_form.is(":visible")) {
						if (ps_validateCard() == false) {
							document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (bankslip_form.is(":visible")) {
						if (ps_validateBankslip() == false) {
							document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (pix_form.is(":visible")) {
						if (ps_validatePix() == false) {
							document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
				}
			}
		});
	} else {
		pay_options.forEach((option) => {
			if (option.dataset.moduleName == "pagbank" && option.checked) {
				pagbank_module = true;
			}
		});
		if (pagbank_module === true) {
			if (card_form.is(":visible")) {
				ps_validateCard();
			}
			if (bankslip_form.is(":visible")) {
				ps_validateBankslip();
			}
			if (pix_form.is(":visible")) {
				ps_validatePix();
			}
		}
	}
	
	//Atualiza a página se o total do pedido mudar
	var orderValueField = document.getElementById('order_value');
	if (orderValueField != null) {
		orderValueField.addEventListener('change', function() {
			if (ps_version == '1.6') {
				window.location.reload(true);
			}
		});
	}
	var installmentsQtyField = document.getElementById('card_installment_qty');
	if (installmentsQtyField != null) {
		installmentsQtyField.addEventListener('change', function(e) {
			ps_setInstallment('card_installment_qty');
		});
	}
	var submitCardButton = document.getElementById('submitCard');
	if (submitCardButton != null) {
		submitCardButton.addEventListener('click', function (e) {
			e.preventDefault();
			ps_cardCheckout(e);
		});
	}
	var submitBankSlipButton = document.getElementById('submitBankSlip');
	if (submitBankSlipButton != null) {
		submitBankSlipButton.addEventListener('click', function (e) {
			e.preventDefault();
			ps_bankslipCheckout(e);
		});
	}
	var submitPixButton = document.getElementById('submitPix');
	if (submitPixButton != null) {
		submitPixButton.addEventListener('click', function (e) {
			e.preventDefault();
			ps_pixCheckout(e);
		});
	}
	
	var savedCardToken = document.getElementsByClassName('check_token');
	if (savedCardToken != null) {
		Array.from(savedCardToken).forEach(function(el) {
			el.addEventListener('change', function() {
				checkCardToken(el);
			});
		});
	}
	
	var thisNum, brand;
	
    var cardNumberField = document.getElementById('card_number');
	if (cardNumberField != null) {
		//Popula cartão em branco
		sendToCard(false, 'card-number', '****************');
		sendToCard(false, 'card-name', 'TITULAR DO CARTÃO');
		sendToCard(false, 'card-expiry-month', '**');
		sendToCard(false, 'card-expiry-year', '**');
		sendToCard(false, 'card-number', '****************');
		cardNumberField.addEventListener('keyUp', function (e) {
			thisNum = this.value;
			if (thisNum.length == 6) {
				brand = ps_getBrand(thisNum);
			}
		});
		cardNumberField.addEventListener('blur', function (e) {
			brand = document.getElementById('ps_card_brand').value.toLowerCase();
			thisNum = this.value;
			if (thisNum !== '') {
				newBrand = ps_getBrand(thisNum);
				validateCardNumber();
				if(thisNum.length > 14){
					sendToCard(this.id, 'card-number');
				}
				if (newBrand === '' || newBrand === false) {
					if (ps_version == '1.6') {
						document.getElementById('credit-icon').innerHTML = '<i class="icon icon-credit-card fa fa-credit-card"></i>';
					} else {
						document.getElementById('credit-icon').innerHTML = '<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>';
					}
				}
			} else {
				this.parentElement.parentElement.classList.remove('form-ok', 'form-error');
				if (ps_version == '1.6') {
					document.getElementById('credit-icon').innerHTML = '<i class="icon icon-credit-card fa fa-credit-card"></i>';
				} else {
					document.getElementById('credit-icon').innerHTML = '<i class="icon icon-credit-card fa fa-credit-card material-icons">credit_card</i>';
				}
		}
		});
	}
    var saveCardFaq = document.getElementById('save-card-faq');
	if (saveCardFaq != null) {
		$('.fancy-button').fancybox();
	}

});

function ps_getBrand(cardNumber) {
    "use strict";
	var currentBrand = document.getElementById('ps_card_brand').value.toLowerCase();
	var card_bin = cardNumber.substring(0,6);
	var brand = '';
	var cardnumber = cardNumber.replace(/[^0-9]+/g, '');

	var cards = {
		amex: /^3[47][0-9]{13}$/,
		aura: /^((?!504175))^((?!5067))(^50[0-9])/,
		banese: /^636117/,
		cabal: /(60420[1-9]|6042[1-9][0-9]|6043[0-9]{2}|604400)/,
		diners: /(36[0-8][0-9]{3}|369[0-8][0-9]{2}|3699[0-8][0-9]|36999[0-9])/,
		discover: /^6(?:011|5[0-9]{2})[0-9]{12}/,
		elo: /^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|^627780|^63(6297|6368|6369)|^65(0(0(3([1-3]|[5-9])|4([0-9])|5[0-1])|4(0[5-9]|[1-3][0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8]|4[1-9]|[5-8][0-9]|9[0-8])|7(0[0-9]|1[0-8]|2[0-7])|9(0[1-9]|[1-6][0-9]|7[0-8]))|16(5[2-9]|[6-7][0-9])|50(0[0-9]|1[0-9]|2[1-9]|[3-4][0-9]|5[0-8]))/,
		fort: /^628167/,
		grandcard: /^605032/,
		hipercard: /^606282|^3841(?:[0|4|6]{1})0/,
		jcb: /^(?:2131|1800|35\d{3})\d{11}/,
		mastercard: /^((5(([1-2]|[4-5])[0-9]{8}|0((1|6)([0-9]{7}))|3(0(4((0|[2-9])[0-9]{5})|([0-3]|[5-9])[0-9]{6})|[1-9][0-9]{7})))|((508116)\\d{4,10})|((502121)\\d{4,10})|((589916)\\d{4,10})|(2[0-9]{15})|(67[0-9]{14})|(506387)\\d{4,10})/,
		personalcard: /^636085/,
		sorocred: /^627892|^636414/,
		valecard: /^606444|^606458|^606482/,
		visa: /^4[0-9]{15}$/,
	};

	for (var flag in cards) {
		if(cards[flag].test(cardnumber)) {
			brand = flag;
		}
	}
	if (brand != '') {
		populateCard(brand);
		if (brand.toLowerCase() != currentBrand) {
			ps_getInstallments(card_bin);
		}
		return brand;
	}else{
		return false;
	}
}

function ps_getInstallments(card_number) {
	var card_bin = card_number.substring(0,6);
    var orderValue = document.getElementById('order_value').value;
    var maxInstallments = document.getElementById('ps_max_installments').value;
    var installmentsMinValue = document.getElementById('ps_installments_min_value').value;
    var installmentsMinType = document.getElementById('ps_installments_min_type').value;

	var opts = DOMPurify.sanitize('<option value=""> - Selecione o Cartão - </option>', {
		SAFE_FOR_JQUERY: true
	});
	
	var params = {
		'action': 'installments',
		'value': orderValue.replace(/[.,\s]/g, ''),
		'payment_methods': 'credit_card',
		'credit_card_bin': card_bin,
	};
	//chamada Ajax API
	$.ajax({
		url: functionUrl,
		cache: false,
		dataType: 'Json',
		data: params,
		beforeSend: function () {
			showLoading(false, 'installments');
		},
		success: function (data, xhr) {
			var response = data.response;
			var error_msg;
			if (response.error_messages && response.error_messages != false) {
				error_msg = 'Cartão inválido! Por favor, verifique.';
				showError('<p>'+ error_msg +'</p>', 10);
				showLoading('hide');
			}else {
				var cardObject = response.payment_methods.credit_card;
				var cardBrand = Object.keys(cardObject)[0];
				var installments = cardObject[cardBrand].installment_plans;
				document.getElementById('get_installments_fees').value = JSON.stringify(installments);
				if (maxInstallments < 2) {
					maxInstallments = document.getElementById("ps_max_installments").value;
				}
				opts = DOMPurify.sanitize('<option value=""> -- </option>', {
					SAFE_FOR_JQUERY: true
				});
				installments.forEach((parc, index) => {
					var optionQty = parc.installments;
					var optionValue = Number(parc.installment_value/100);

					if (optionQty == 1 && discount_type > 0 && credit_card_value > 0 && discount_card == 1) {
						optionValue = credit_card_value;
					}

					var optionTotal = Number((optionQty * optionValue));
					var strInterest = '';
					if (parc.interest_free === true) {
						strInterest = ' (sem juros)';
					} else {
						strInterest = '';
					}
					var optionLabel = (optionQty + ' x ' + formatMoney(optionValue) + strInterest + ' Total: ' + formatMoney(optionTotal)); // Label do option
					var formattedValue = Number(optionValue).toMoney(2, '.', ',');

					if (installmentsMinValue == 0) {
						DOMPurify.sanitize(opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>', {
							SAFE_FOR_JQUERY: true
						});
					}else if(installmentsMinValue >= 1 && installmentsMinType == 0){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue) {
								//
							}else{
								DOMPurify.sanitize(opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>', {
									SAFE_FOR_JQUERY: true
								});
							}
						}
					}else if(installmentsMinValue >= 1 && installmentsMinType == 1){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue) {
								if (optionQty == 1) {
									DOMPurify.sanitize(opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>', {
										SAFE_FOR_JQUERY: true
									});
								}
							}else{
								DOMPurify.sanitize(opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>', {
									SAFE_FOR_JQUERY: true
								});
							}
						}
					}
				});
				document.getElementById('card_installment_qty').innerHTML = opts;
				document.getElementById('card_installment_qty').click();
			}
		},
		complete: function () {
			showLoading('hide');
		},
		error: function (xhr) {
			console.log(xhr.status);
			showLoading('hide');
		}
	});

}

function ps_cardCheckout(e) {
	e.preventDefault();
	window.onbeforeunload = function(){ return true; }
	document.getElementsByTagName('body')[0].style = 'overscroll-behavior: contain';
    if (ps_validateCard() !== false) {
		showLoading();
		var formData = getFormData('card_pagbank');
		return sendAjaxCall('processCard', formData);
    }else{
		document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_bankslipCheckout(e) {
	e.preventDefault();
	window.onbeforeunload = function(){ return true; }
	document.getElementsByTagName('body')[0].style = 'overscroll-behavior: contain';
    if (ps_validateBankslip() !== false) {
		showLoading();
		var formData = getFormData('bankslip_pagbank');
		return sendAjaxCall('processBankSlip', formData);
    }else{
		document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_pixCheckout(e) {
	e.preventDefault();
	window.onbeforeunload = function(){ return true; }
	document.getElementsByTagName('body')[0].style = 'overscroll-behavior: contain';
	if (ps_validatePix() !== false){
		showLoading();
		var formData = getFormData('pix_pagbank');
		return sendAjaxCall('processPix', formData);
    }else{
		document.getElementById('pagbank-container').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_setInstallment(id) {
    var sel = document.getElementById(id);
	var option = sel.options[sel.selectedIndex];
    if (option.value > 0) {
        document.getElementById('ps_card_installment_value').value = option.getAttribute('dataprice');
        document.getElementById('ps_card_installments').value = option.value;
    }
	ps_validateCard();
}

function ps_validateCard() {
	var cardTokenId = document.getElementById('ps_card_token_id');
	var html = '';
	var errorFields = [];
	var address_error = false;

	var titular = document.getElementById('card_name').value.trim();
	if (titular.length == 0) {
		html += 'Titular do Cartão não preenchido. <br>';
		errorFields.push('card_name');
	}

	var telephone = document.getElementById('card_phone').value.replace(/[^0-9]/g,'');    
	if (telephone.length == 0) {
		html += 'Telefone não preenchido. <br>';
		errorFields.push('card_phone');
	} else { 
		if (!validatePhoneNumber('card_phone')) {
			html += 'Telefone inválido. <br>';
			errorFields.push('card_phone');
		}
	}

	var cpf = document.getElementById('card_doc').value.replace(/[^0-9]/g, '');
	if (cpf.length == 0) {
		html += 'CPF não preenchido. <br>';
		errorFields.push('card_doc');
	} else if (!verifyDoc('card_doc')) {
		html += 'CPF inválido. <br>';
		errorFields.push('card_doc');
	}

	if (cardTokenId.value > 0) {
		if (msg_console === 1) {
			console.log('cartão tokenizado.');
		}
	} else {
		var cardNumber = document.getElementById('card_number').value.replace(/[^0-9]/g, '');
		if (cardNumber.length == 0) {
			html += 'Número do Cartão não preenchido. <br>';
			errorFields.push('card_number');
		} else if (validateCardNumber() === false) {
			html += 'Cartão inválido. Favor verificar. <br>';
			errorFields.push('card_number');
		}

		var expMonth = document.getElementById('card_month').value.replace(/[^0-9]/g, '');
		if (expMonth.length == 0) {
			html += 'Mês do Vencimento do Cartão não preenchido. <br>';
			errorFields.push('card_month');
		}

		var expYear = document.getElementById('card_year').value.replace(/[^0-9]/g, '');
		if (expYear.length == 0) {
			html += 'Ano do Vencimento do Cartão não preenchido. <br>';
			errorFields.push('card_year');
		}
		var brand = document.getElementById('ps_card_brand').value.trim().toLowerCase();
		var cvv = document.getElementById('card_cvv').value.replace(/[^0-9]/g, '');
		if (cvv.length == 0) {
			html += 'Código de Segurança do Cartão não preenchido. <br>';
			errorFields.push('card_cvv');
		} else if (checkCVV(brand) === false) {
			html += 'Código de Segurança do Cartão inválido! Favor verificar. <br>';
			errorFields.push('card_cvv');
		}
	}
	var installments_qty = document.getElementById('card_installment_qty').value;
	if (installments_qty.length == 0 || parseInt(installments_qty) < 1) {
		html += 'Quantidade de Parcelas não preenchida. <br>';
		errorFields.push('card_installment_qty');
	}

	var invoiceAddress = document.getElementById('ps_card_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br>';
		errorFields.push('ps_card_address_invoice');
		address_error = true;
	}

	var postcodeNumber = document.getElementById('ps_card_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br>';
		errorFields.push('ps_card_postcode_invoice');
		address_error = true;
	}

	var invoiceNumber = document.getElementById('ps_card_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br>';
		errorFields.push('ps_card_number_invoice');
		address_error = true;
	}

	var invoiceDistrict = document.getElementById('ps_card_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br>';
		errorFields.push('ps_card_address2_invoice');
		address_error = true;
	}

	var invoiceCity = document.getElementById('ps_card_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br>';
		errorFields.push('ps_card_city_invoice');
		address_error = true;
	}

	var invoiceState = document.getElementById('ps_card_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br>';
		errorFields.push('ps_card_state_invoice');
		address_error = true;
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (msg_console === 1) {
				console.log(errorFields[i]);
			}
			if (ps_version >= '1.7') {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('has-success');
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('has-danger');
			} else {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('form-ok');
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('form-error');
			}
		}
	}

	if (html.length > 0) {
		if (msg_console === 1) {
			console.log(html);
		}
		showError(html, 5);
		if (address_error === true) {
			$('#ps_card_address').collapse('show');
		}
		if (ps_version >= '1.7') {
			checkTos(false);
		}
		return false;
	} else {
		if (ps_version >= '1.7') {
			checkTos(true);
		}
		return true;
	}
}

function checkField(field_id) {
	var field = document.getElementById(field_id);
	var parentItem = field.parentElement.parentElement;
    var card_name = document.getElementById('card_name');
    var bankslip_name = document.getElementById('bankslip_name');
    var pix_name = document.getElementById('pix_name');
	var card_form = $('#card_pagbank');
	var bankslip_form = $('#bankslip_pagbank');
	var pix_form = $('#pix_pagbank');
	
    if (field_id == 'card_name' || field_id == 'pix_name' || field_id == 'bankslip_name') {
        var cardName = field.value.trim();
        if (cardName.length > 3) {
			if (cardName.match('^[a-z A-Z]{3,45}$')) {
				if (ps_version >= '1.7') {
					parentItem.classList.remove('has-danger');
					parentItem.classList.add('has-success');
				} else {
					parentItem.classList.remove('form-error');
					parentItem.classList.add('form-ok');
				}
	
				if (card_name != null && card_name.value != cardName) {
					card_name.value = cardName;
				}
				if (bankslip_name != null && bankslip_name.value != cardName) {
					bankslip_name.value = cardName;
				}
				if (pix_name != null && pix_name.value != cardName) {
					pix_name.value = cardName;
				}
			} else {
				if (ps_version >= '1.7') {
					parentItem.classList.remove('has-success');
					parentItem.classList.add('has-danger');
				} else {
					parentItem.classList.remove('form-ok');
					parentItem.classList.add('form-error');
				}
			}
		} else {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-success');
				parentItem.classList.add('has-danger');
			} else {
				parentItem.classList.remove('form-ok');
				parentItem.classList.add('form-error');
			}
		}
    } else if (field_id == 'card_year' || field_id == 'card_month') {
        var cardYearField = document.getElementById(field_id);
		var cardYear = cardYearField.options[cardYearField.selectedIndex].value;
        if (cardYear.length < 2) {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-success');
				parentItem.classList.add('has-danger');
			} else {
				parentItem.classList.remove('form-ok');
				parentItem.classList.add('form-error');
			}
        } else {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-danger');
				parentItem.classList.add('has-success');
			} else {
				parentItem.classList.remove('form-error');
				parentItem.classList.add('form-ok');
			}
        }
    } else if (field_id == 'card_installment_qty') {
        var cardInstField = document.getElementById(field_id);
		var cardInst = cardInstField.options[cardInstField.selectedIndex].value;
        if (cardInst < 1) {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-success');
				parentItem.classList.add('has-danger');
			} else {
				parentItem.classList.remove('form-ok');
				parentItem.classList.add('form-error');
			}
        } else {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-danger');
				parentItem.classList.add('has-success');
			} else {
				parentItem.classList.remove('form-error');
				parentItem.classList.add('form-ok');
			}
        }
    } else if (field_id == 'card_phone') {
        var cardPhoneField = document.getElementById(field_id);
		var cardPhone = cardPhoneField.value.replace(/[^0-9]/g, '');
        if (cardPhone.length < 10) {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-success');
				parentItem.classList.add('has-danger');
			} else {
				parentItem.classList.remove('form-ok');
				parentItem.classList.add('form-error');
			}
        } else {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-danger');
				parentItem.classList.add('has-success');
			} else {
				parentItem.classList.remove('form-error');
				parentItem.classList.add('form-ok');
			}
        }
    } else if (field_id == 'card_cvv') {
        var cardCvv = document.getElementById(field_id).value.replace(/[^0-9]/g, '');
        if (cardCvv.length < 3) {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-success');
				parentItem.classList.add('has-danger');
			} else {
				parentItem.classList.remove('form-ok');
				parentItem.classList.add('form-error');
			}
        } else {
			if (ps_version >= '1.7') {
				parentItem.classList.remove('has-danger');
				parentItem.classList.add('has-success');
			} else {
				parentItem.classList.remove('form-error');
				parentItem.classList.add('form-ok');
			}
        }
    } else if (field_id == 'card_doc') {
		verifyDoc(field_id);
	}

	if (card_form.is(":visible")) {
		ps_validateCard();
	}
	if (bankslip_form.is(":visible")) {
		ps_validateBankslip();
	}
	if (pix_form.is(":visible")) {
		ps_validatePix();
	}
}

function ps_validateBankslip() {
    var html = '';
    var errorFields = [];
    var adress_error = false;

    var telephone = document.getElementById('bankslip_phone').value.replace(/[^0-9]/g, '');
    if (telephone.length == 0) {
        html += 'Telefone não preenchido. <br>';
		errorFields.push('bankslip_phone');
    } else if (!validatePhoneNumber('bankslip_phone')) {
        html += 'Telefone inválido. <br>';
		errorFields.push('bankslip_phone');
    }
    var cpf = document.getElementById('bankslip_doc').value.replace(/[^0-9]/g, '');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br>';
		errorFields.push('bankslip_doc');
    } else {
        if (!verifyDoc('bankslip_doc')) {
            html += 'CPF/CNPJ inválido. <br>';
			errorFields.push('bankslip_doc');
        }
    }
    var nome = document.getElementById('bankslip_name').value;
    if (nome.length == 0) {
        html += 'Nome é obrigatório. <br>';
		errorFields.push('bankslip_name');
    }
    if (nome.length < 4) {
        html += 'Nome Inválido. <br>';
		errorFields.push('bankslip_name');
    }

	var invoiceAddress = document.getElementById('ps_bankslip_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br>';
		errorFields.push('ps_bankslip_address_invoice');
		adress_error = true;
	}

	var postcodeNumber = document.getElementById('ps_bankslip_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br>';
		errorFields.push('ps_bankslip_postcode_invoice');
		adress_error = true;
	}

	var invoiceNumber = document.getElementById('ps_bankslip_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br>';
		errorFields.push('ps_bankslip_number_invoice');
		adress_error = true;
	}

	var invoiceDistrict = document.getElementById('ps_bankslip_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br>';
		errorFields.push('ps_bankslip_address2_invoice');
		adress_error = true;
	}

	var invoiceCity = document.getElementById('ps_bankslip_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br>';
		errorFields.push('ps_bankslip_city_invoice');
		adress_error = true;
	}

	var invoiceState = document.getElementById('ps_bankslip_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br>';
		errorFields.push('ps_bankslip_state_invoice');
		adress_error = true;
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (msg_console === 1) {
				console.log(errorFields[i]);
			}
			if (ps_version >= '1.7') {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('has-success');
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('has-danger');
			} else {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('form-ok'); 
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('form-error'); 
			}
		}
	}

    if (html.length > 0) {
        showError(html, 5);
		if (adress_error === true) {
			$('#ps_bankslip_address').collapse('show');
		}
		if (ps_version >= '1.7') {
			checkTos(false);
		}
        return false;
    } else {
		if (ps_version >= '1.7') {
			checkTos(true);
		}
        return true;
    }
}

function ps_validatePix() {
    var html = '';
    var errorFields = [];
	var pix_adress_error = false;

    var telephone = document.getElementById('pix_phone').value.replace(/[^0-9]/g, '');
    if (telephone.length == 0) {
        html += 'Telefone não preenchido. <br>';
		errorFields.push('pix_phone');
    } else if (!validatePhoneNumber('pix_phone')) {
        html += 'Telefone inválido. <br>';
		errorFields.push('pix_phone');
    }

    var cpf = document.getElementById('pix_doc').value.replace(/[^0-9]/g, '');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br>';
		errorFields.push('pix_doc');
    } else {
        if (!verifyDoc('pix_doc')) {
            html += 'CPF/CNPJ inválido. <br>';
			errorFields.push('pix_doc');
        }
    }

    var nome = document.getElementById('pix_name').value;
    if (nome.length == 0) {
        html += 'Nome é obrigatório. <br>';
		errorFields.push('pix_name');
    }
    if (nome.length < 4) {
        html += 'Nome Inválido. <br>';
		errorFields.push('pix_name');
    }

	var invoiceAddress = document.getElementById('ps_pix_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br>';
		errorFields.push('ps_pix_address_invoice');
		pix_adress_error = true;
	}

	var postcodeNumber = document.getElementById('ps_pix_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br>';
		errorFields.push('ps_pix_postcode_invoice');
		pix_adress_error = true;
	}

	var invoiceNumber = document.getElementById('ps_pix_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br>';
		errorFields.push('ps_pix_number_invoice');
		pix_adress_error = true;
	}

	var invoiceDistrict = document.getElementById('ps_pix_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br>';
		errorFields.push('ps_pix_address2_invoice');
		pix_adress_error = true;
	}

	var invoiceCity = document.getElementById('ps_pix_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br>';
		errorFields.push('ps_pix_city_invoice');
		pix_adress_error = true;
	}

	var invoiceState = document.getElementById('ps_pix_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br>';
		errorFields.push('ps_pix_state_invoice');
		pix_adress_error = true;
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (msg_console === 1) {
				console.log(errorFields[i]);
			}
			if (ps_version >= '1.7') {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('has-success');
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('has-danger');
			} else {
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.remove('form-ok');
				document.getElementById(errorFields[i]).parentElement.parentElement.classList.add('form-error');
			}
		}
	}

    if (html.length > 0) {
        showError(html, 5);
		if (pix_adress_error === true) {
			$('#ps_pix_address').collapse('show');
		}
		if (ps_version >= '1.7') {
			checkTos(false);
		}
        return false;
    } else {
		if (ps_version >= '1.7') {
			checkTos(true);
		}
        return true;
    }
}

function ps_parseError(erros) {
    var html = '';
    if (typeof erros == 'object') {
        for (var i in erros) {
            html += (erros[i] + '');
        }
    }
    return html;
}

function ps_validateAreaCode(fone) {
    var clean = fone.replace(/\D/g, '').trim();
    var areaCode = clean.substring(0, 2);
    var areaCodeExists = inArray(areaCode, valid_area_codes);

    if (areaCodeExists === false) {
        return false;
    }
    return true;
}

var formatMoney = function (value) {
    var valueAsNumber = Number(value);
    return 'R$ ' + valueAsNumber.toMoney(2, ',', '.');
};

Number.prototype.toMoney = function (decimals, decimal_sep, thousands_sep) {
    var n = this,
    c = isNaN(decimals) ? 2 : Math.abs(decimals),
    d = decimal_sep || '.',
    t = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    sign = (n < 0) ? '-' : '',
    i = parseInt(n = Math.abs(n).toFixed(c)) + '',
    j = ((j = i.length) > 3) ? j % 3 : 0;
    return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
};

function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
    }

    alert(out);
    var pre = document.createElement('pre');
    pre.innerHTML = out;
    document.body.appendChild(pre);
}

function populateSelect(brand) {
    if (brand == 'Hipercard') {
        document.getElementById('card_number').setAttribute('maxlength', 19);
    } else {
        document.getElementById('card_number').setAttribute('maxlength', 16);
    }
    var sbrand = brand.toLowerCase();
    document.getElementById('loader').style.display = 'none';
    document.getElementsByClassName('images')[0].getElementsByTagName('div')[0].getElementsByTagName('img')[0].classList.remove("selected");
    document.getElementById(brand).classList.add("selected");
    document.getElementById('card_association').value = sbrand;
}

function updateValue() {
    var sel = document.getElementById('installments');
	var option = sel.options[sel.selectedIndex].text;
    document.getElementById('installmentValue').value = option;
}

function validateCPF(id) {
    var cpfField = document.getElementById(id);
    var cpfFieldParent = cpfField.parentElement.parentElement;
    var cpf = cpfField.value;
    var err = 0;
    var exp = /\.|\-/g;
        cpf = cpf.toString().replace(exp, "");
    if (cpf.length !== 11 || cpf === "00000000000" || cpf === "11111111111" || cpf === "22222222222" || cpf === "33333333333" || cpf === "44444444444" || cpf === "55555555555" || cpf === "66666666666" || cpf === "77777777777" || cpf === "88888888888" || cpf === "99999999999") {
        err = 1;
    }
    var soma = 0;
    for (var i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    var resto = 11 - (soma % 11);
    if (resto == 10 || resto == 11) {
        resto = 0;
    }
    if (resto != parseInt(cpf.charAt(9))) {
        err = 1;
    }
    soma = 0;
    for (i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    if (resto == 10 || resto == 11) {
        resto = 0;
    }
    if (resto != parseInt(cpf.charAt(10))) {
        err = 1;
    }
    if (err == 0) {
		if (ps_version >= '1.7') {
			cpfFieldParent.classList.remove('has-danger');
			cpfFieldParent.classList.add('has-success');
		} else {
			cpfFieldParent.classList.remove('form-error');
			cpfFieldParent.classList.add('form-ok');
		}
        return true;
    } else {
		if (ps_version >= '1.7') {
			cpfFieldParent.classList.remove('has-success');
			cpfFieldParent.classList.add('has-danger');
		} else {
			cpfFieldParent.classList.remove('form-ok');
			cpfFieldParent.classList.add('form-error');
		}
        showError('CPF incorreto. Favor verificar.', 5);
        return false;
    }
}

function validateCNPJ(id) {
    var cnpjField = document.getElementById(id);
    var cnpjFieldParent = cnpjField.parentElement.parentElement;
    var cnpj = cnpjField.value;
    var valida = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    var dig1 = new Number;
    var dig2 = new Number;

    var exp = /\.|\-|\//g;
        cnpj = cnpj.toString().replace(exp, "");
    var digito = new Number(eval(cnpj.charAt(12) + cnpj.charAt(13)));

    for (var i = 0; i < valida.length; i++) {
        dig1 += (i > 0 ? (cnpj.charAt(i - 1) * valida[i]) : 0);
        dig2 += cnpj.charAt(i) * valida[i];
    }
    dig1 = (((dig1 % 11) < 2) ? 0 : (11 - (dig1 % 11)));
    dig2 = (((dig2 % 11) < 2) ? 0 : (11 - (dig2 % 11)));

    if (cnpj === '') {
		if (ps_version >= '1.7') {
			cnpjFieldParent.classList.remove('has-danger');
			cnpjFieldParent.classList.remove('has-success');
		} else {
			cnpjFieldParent.classList.remove('form-error');
			cnpjFieldParent.classList.remove('form-ok');
		}
    } else if (((dig1 * 10) + dig2) == digito) {
		if (ps_version >= '1.7') {
			cnpjFieldParent.classList.remove('has-danger');
			cnpjFieldParent.classList.add('has-success');
		} else {
			cnpjFieldParent.classList.remove('form-error');
			cnpjFieldParent.classList.add('form-ok');
		}
        return true;
    } else {
		if (ps_version >= '1.7') {
			cnpjFieldParent.classList.remove('has-success');
			cnpjFieldParent.classList.add('has-danger');
		} else {
			cnpjFieldParent.classList.remove('form-ok');
			cnpjFieldParent.classList.add('form-error');
		}
        showError('CNPJ incorreto. Favor verificar.', 5);
        return false;
    }
}

function validateCardNumber() {
    //"use strict";
	var cardTokenId = document.getElementById('ps_card_token_id');
	if (cardTokenId.value > 0) {
		if (msg_console === 1) {
			console.log('cartão tokenizado!');
		}
		return true;
	}
    var cardField = document.getElementById('card_number');
    var cardFieldParent = cardField.parentElement.parentElement;
    var creditCardNumber = cardField.value.replace(/[\s+|\.|\-]/g, '');
    var cardType = ps_getBrand(creditCardNumber);
    if (cardType === "hipercard") {
		if (ps_version >= '1.7') {
			cardFieldParent.classList.remove('has-danger');
			cardFieldParent.classList.add('has-success');
		} else {
			cardFieldParent.classList.remove('form-error');
			cardFieldParent.classList.add('form-ok');
		}
	return true; // hipercard não valida.
    } else {
        // Luhn algorithm
        var checksum = 0;
        for (var i = (2 - (creditCardNumber.length % 2)); i <= creditCardNumber.length; i += 2) {
            checksum += parseInt(creditCardNumber.charAt(i - 1), 10);
        }
        for (i = (creditCardNumber.length % 2) + 1; i < creditCardNumber.length; i += 2) {
            var digit = parseInt(creditCardNumber.charAt(i - 1), 10) * 2;
            if (digit < 10) {
                checksum += digit;
            } else {
                checksum += (digit - 9);
            }
        }
        if ((checksum % 10) === 0) {
			if (ps_version >= '1.7') {
				cardFieldParent.classList.remove('has-danger');
				cardFieldParent.classList.add('has-success');
			} else {
				cardFieldParent.classList.remove('form-error');
				cardFieldParent.classList.add('form-ok');
			}
            if (msg_console === 1) {
                console.log('Cartão válido!');
            }
            return true;
        } else {
			if (ps_version >= '1.7') {
				cardFieldParent.classList.remove('has-success');
				cardFieldParent.classList.add('has-danger');
			} else {
				cardFieldParent.classList.remove('form-ok');
				cardFieldParent.classList.add('form-error');
			}
            if (msg_console === 1) {
                console.log('Cartão inválido!');
            }
            showError('Cartão inválido: ' + cardField.value, 7);
            return false;
        }
    }
}

function checkCVV(card_brand) {
    "use strict";
    var cvvField = document.getElementById('card_cvv');
    var cvvFieldParent = cvvField.parentElement.parentElement;
	var brand;
    if (card_brand && card_brand != 'undefined') {
        brand = card_brand.toLowerCase();
    } else {
        brand = document.getElementById('ps_card_brand').value.toLowerCase();
    }
    if (cvvField.value) {
        if (brand == 'amex' && cvvField.value.length != 4 || brand != 'amex' && cvvField.value.length != 3) {
			if (fone === '') {
				if (ps_version >= '1.7') {
					cvvFieldParent.classList.remove('has-success');
					cvvFieldParent.classList.add('has-danger');
				} else {
					cvvFieldParent.classList.remove('form-ok');
		            cvvFieldParent.classList.add('form-error');
				}
			}
            if (msg_console === 1) {
                console.log('CVV inválido. ' + brand + ' com ' + cvvField.value.length + ' caracteres.');
            }
            showError('Código de Validação inválido. ' + brand.toUpperCase() + ' com ' + cvvField.value.length + ' caracteres.', 7);
            return false;
        } else {
            cvvFieldParent.classList.remove('form-error', 'form-ok');
            return true;
        }
    }
}

function validatePhoneNumber(fieldId) {
    "use strict";
    var foneField = document.getElementById(fieldId);
    var foneFieldParent = foneField.parentElement.parentElement;
    var fone = foneField.value;
    var card_phone = document.getElementById('card_phone');
    var bankslip_phone = document.getElementById('bankslip_phone');
    var pix_phone = document.getElementById('pix_phone');

	if (!fone || fone === false) {
		fone = document.getElementById('card_phone').value;
	}
	if (fone === '') {
		if (ps_version >= '1.7') {
			foneFieldParent.classList.remove('has-success');
			foneFieldParent.classList.add('has-danger');
		} else {
			foneFieldParent.classList.remove('form-ok');
			foneFieldParent.classList.add('form-error');
		}
		return;
	}

    var clean = fone.replace(/\D/g, "").trim();
    var reg = /^[1-9]{2}[2-9][0-9]{7,8}$/;
    var areaCode = clean.substring(0, 2);

    if (reg.test(clean)) {
        var areaCodeExists = inArray(areaCode, valid_area_codes);
        if (areaCodeExists < 0) {
            if (msg_console === 1) {
                console.log('DDD não encontrado (' + areaCode + ')');
            }
			foneFieldParent.classList.remove('has-success');
			foneFieldParent.classList.add('has-danger');
            showError('DDD não encontrado (' + areaCode + ')', 5);
            return false;
        } else {
            if (msg_console === 1) {
                console.log('(' + areaCode + ') DDD válido!');
            }
			if (ps_version >= '1.7') {
				foneFieldParent.classList.remove('has-danger');
				foneFieldParent.classList.add('has-success');
			} else {
				foneFieldParent.classList.remove('form-error');
	            foneFieldParent.classList.add('form-ok');
			}
			
			if (card_phone != null && card_phone.value != fone) {
				card_phone.value = fone;
			}
			if (bankslip_phone != null && bankslip_phone.value != fone) {
				bankslip_phone.value = fone;
			}
			if (pix_phone != null && pix_phone.value != fone) {
				pix_phone.value = fone;
			}

            return true;
        }
    } else {
        if (msg_console === 1) {
            console.log('Fone: ' + clean);
        }
		if (ps_version >= '1.7') {
			foneFieldParent.classList.remove('has-success');
			foneFieldParent.classList.add('has-danger');
		} else {
			foneFieldParent.classList.remove('form-ok');
			foneFieldParent.classList.add('form-error');
		}
        showError('Telefone inválido: ' + fone, 5);
        return false;
    }
}

function showLoading(hide, id) {
    "use strict";

	var submitCard = document.getElementById('submitCard');
	var submitBankSlip = document.getElementById('submitBankSlip');
	var submitPix = document.getElementById('submitPix');
	
	if (!hide || hide == ''){
		if (id == 'installments' || id == 'delete_card') {
			document.getElementById('pagbankmsg').innerHTML = 'Validando...';
		} else {
			document.getElementById('pagbankmsg').innerHTML = 'Processando pagamento...';
		}
		if (ps_version < '1.7') {
			if (submitCard != null) {
				submitCard.disabled = true;
			}
			if (submitBankSlip != null) {
				submitBankSlip.disabled = true;
			}
			if (submitPix != null) {
				submitPix.disabled = true;
			}
		}
		document.getElementById('pagbankproccess').style.display = 'block';
		document.getElementById('fancy_load').classList.add('loading');
		document.getElementById('fancy_load').style.width = window.innerWidth;
	}else{
		if (ps_version < '1.7') {
			if (submitCard != null) {
				submitCard.disabled = false;
			}
			if (submitBankSlip != null) {
				submitBankSlip.disabled = false;
			}
			if (submitPix != null) {
				submitPix.disabled = false;
			}
		}
		document.getElementById('pagbankproccess').style.display = 'none';
		document.getElementById('fancy_load').classList.remove('loading');
	}
}

function noCopy(teclapress) {
    "use strict";
    var tecla;
    if (navigator.appName === "Netscape") {
        tecla = teclapress.which;
    } else {
        tecla = teclapress.keyCode;
    }
    var ctrl = teclapress.ctrlKey;

    if (ctrl && tecla === 67) {
        return false;
    }
    if (ctrl && tecla === 86) {
        return false;
    }
}

function typeKey(tecla) {
    "use strict";
    var maxLength = document.getElementById("card_cvv").getAttribute("maxlength");
    if (document.getElementById("card_cvv").value.length >= maxLength) {
        return false;
    }
    return document.checkout.card_cvv.value += tecla;
}

function clearCvc() {
    "use strict";
    document.checkout.card_cvv.value = "";
}

function populateCard(brand) {
    "use strict";
    document.getElementById('ps_card_brand').value = brand;
    document.getElementById('credit-icon').innerHTML = '<img class="addon-img" src="' + this_path + 'img/' + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />';
    document.querySelector('#card_container .card-brand').innerHTML = '<img class="addon-img" src="' + this_path + 'img/' + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />';
    if (brand === 'aura') {
        document.getElementById('card_number').setAttribute('maxlength', 19);
    } else {
        document.getElementById('card_number').setAttribute('maxlength', 16);
    }
    if (brand === 'amex') {
        document.getElementById('card_cvv').setAttribute('maxlength', 4);
    } else {
        document.getElementById('card_cvv').setAttribute('maxlength', 3);
    }
}

function toggleCardBack(action) {
    "use strict";
    if (action === 'add') {
		document.getElementById('card_container').classList.add('flip');
		setTimeout(function () {
			document.getElementById('card_container').classList.remove('flip');
			document.getElementById('card_container').classList.add('verso');
		}, 200);
        
    } else {
		document.getElementById('card_container').classList.add('flipback');
		setTimeout(function () {
			document.getElementById('card_container').classList.remove('flipback', 'verso');
		}, 100);
    }
}

function sendToCard(id, classe, str) {
    "use strict";
	if (!str || str == '') {
		str = document.getElementById(id).value;
	}
    if (str.length > 1) {
        document.getElementById('card_container').getElementsByClassName(classe)[0].innerHTML = str;
        if (classe === 'card-number') {
            document.getElementById('number_card').innerHTML = str.replace(/(.{4})/g, '$1 &nbsp;');
        }
    }
}

function showError(str, t) {
    "use strict";
	var controlError = document.getElementById('pagbank_control_error');
    controlError.innerHTML = str;
	controlError.classList.add('alert', 'alert-danger');
	controlError.style.display = 'block';

	setTimeout(function () {
        controlError.innerHTML = '';
		controlError.classList.remove('alert', 'alert-danger');
		controlError.style.display = 'none';
    }, (1000 * t));
}

function parseValue(value) {
    return parseFloat(value).toFixed(2);
}

function verifyDoc(id) {
    "use strict";
    var cpf_cnpj = document.getElementById(id);
    var card_doc = document.getElementById('card_doc');
    var bankslip_doc = document.getElementById('bankslip_doc');
    var pix_doc = document.getElementById('pix_doc');
	var fieldValue = cpf_cnpj.value;
    var num = cpf_cnpj.value.replace(/\D/g, "");
	if(card_doc != null) {
		card_doc.value = fieldValue;
	}
	if(bankslip_doc != null) {
		bankslip_doc.value = fieldValue;
	}
	if(pix_doc != null) {
		pix_doc.value = fieldValue;
	}
    if (num.length > 12) {
        mascara(cpf_cnpj, cnpjmask);
        if (!validateCNPJ(id)) {
            return false;
        }
        return true;
    } else {
        mascara(cpf_cnpj, cpfmask);
        if (!validateCPF(id)) {
            return false;
        }
        return true;
    }
}

function getFormData(formId) {
	var unindexed_array = $('#' + formId).serializeArray();
    var formdata = {};

	$.map(unindexed_array, function(n, i){
		formdata[n.name] = n.value;
	});
	if (formId == 'card_pagbank'){
		if (formdata['ps_card_token_id'] > 0) {
			formdata.cardTokenId = formdata['ps_card_token_id'];
		} else {
			var cardData = PagSeguro.encryptCard({
				publicKey: public_key,
				holder: formdata.card_name,
				number: formdata.card_number,
				expMonth: formdata.card_month,
				expYear: formdata.card_year,
				securityCode: formdata.card_cvv
			});
			formdata.encryptedCard = cardData.encryptedCard;
		}
		formdata.card_bin = formdata.card_number.substring(0,6);
		delete formdata.card_number; 
		delete formdata.card_month;
		delete formdata.card_year
		delete formdata.card_cvv;
	}
    return formdata;
}

function sendAjaxCall(actionCalled, formData, id = false) {
	formData.action = actionCalled;
	var ret;
	$.ajax({
		url: functionUrl,
		cache: false,
		dataType: 'Json',
		data: formData,
		beforeSend: function () {
			if (id !== false) {
				showLoading(false, id);
			} else {
				showLoading();
			}
		},
		success: function (data, xhr) {
			document.getElementById('pagbank_response').value = JSON.stringify(data.response);
			if (data == 'OK') {
				ret = data;
				var item = document.getElementById('token_' + formData.id_customer_token);
				item.parentElement.parentElement.parentElement.parentElement.remove();
				window.alert('Cartão apagado com sucesso!');
				showLoading('hide');
				window.location.reload(true);
			} else if (data.errors === false && data.response !== false && (data.status > 199 && data.status < 300)) {
				var resp = data.response;
				if(resp.hasOwnProperty('charges')){
					var charge = resp.charges[0];
					var payment_status = charge.payment_response.message;
					var payment_status_code = charge.payment_response.code;
					
					var resp_string = '<b>' + parseChargeStatus(charge.status) + '</b>!<br/>' + payment_status + ' (' + payment_status_code + ')';
					var pagbankmsg = document.getElementById('pagbankmsg');
					pagbankmsg.innerHTML = resp_string;
					
					if (charge.status == 'AVAILABLE' || charge.status == 'AUTHORIZED' || charge.status == 'PAID' || charge.status == 'WAITING' || charge.status == 'IN_ANALYSIS') {
						//Success
						setTimeout(function () {
							redirectValidation();
						}, 2000);
						ret = true;
					}else{
						//Declined / Canceled
						setTimeout(function () {
							pagbankmsg.innerHTML = 'Por favor, verifique os dados do cartão e tente novamente, ou escolha outra opção de pagamento.';
						}, 2000);
						setTimeout(function () {
							showLoading('hide');
						}, 5000);
						ret = false;
					}
				}else if(resp.hasOwnProperty('qr_codes')){ //PIX
					var resp_string = 'Pedido Processado!<br/>Aguardando Pagamento!';
					var pagbankmsg = document.getElementById('pagbankmsg');
					pagbankmsg.innerHTML = resp_string;

					//Redirect PIX page
					setTimeout(function () {
						redirectValidation();
					}, 2000);
					ret = true;
				}
			}else{
				//Trata erros
				var resp_string = 'Houve um erro ao processar seu pagamento. Por favor, tente novamente.';
				var pagbankmsg = document.getElementById('pagbankmsg');
				pagbankmsg.innerHTML = resp_string;
				ret = false;
				window.onbeforeunload = null;
				setTimeout(function () {
					window.location.reload(true);
				}, 3000);
			}
		},
		complete: function () {
			window.onbeforeunload = null;
			setTimeout(function () {
				showLoading('hide');
			}, 5000);
		},
		error: function (xhr) {
			console.log(xhr.status);
		}
	});
	return ret;
}

function parseChargeStatus(status) {
	var ret = '';
	if (!status || status == ''){
		ret = false;
	}else{
		if (status == 'AUTHORIZED') {
			ret = 'Pagamento autorizado';
		}else if (status == 'AVAILABLE') {
			ret = 'Pagamento autorizado';
		}else if (status == 'PAID') {
			ret = 'Pagamento autorizado';
		}else if (status == 'IN_ANALYSIS') {
			ret = 'Pagamento em análise';
		}else if (status == 'WAITING') {
			ret = 'Aguardando pagamento';
		}else if (status == 'CANCELED') {
			ret = 'Pagamento cancelado';
		}else if (status == 'DECLINED') {
			ret = 'Pagamento não autorizado';
		}else{
			ret = status;
		}
	}	
	return ret;
}

function redirectValidation() {
	setTimeout(function () {
		document.getElementById('pagbank_success').submit();
	}, 3000);
}

function checkCardToken(el) {
	//console.log(el);
	var savedCards = document.getElementsByClassName('check_token');
	var selectedCard = document.getElementById('selected_card_token');
	var cardTokenId = document.getElementById('ps_card_token_id');
	var cardName = el.dataset.name;
	var cardBrand = el.dataset.brand;
	var cardLastDigits = el.dataset.lastdigits;
	var cardFirstDigits = el.dataset.firstdigits;
	var cardMonth = String(el.dataset.month).padStart(2, '0');
	var cardYear = el.dataset.year.toString().slice(-2);
	var checkedItem = false;
	Array.from(savedCards).forEach(function(item) {
		if (item.parentElement.classList.contains('checked') || item.checked == true) {
			checkedItem = true;
		} else {
			item.checked = false;
			item.parentElement.classList.remove('checked');
		}
	});
	
	if (checkedItem === true) {
		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = 'none';
		});
		//el.checked = true;
		//el.parentElement.classList.add('checked');
		selectedCard.innerHTML = '<p>Você está utilizando o cartão: <br /><b class="text-uppercase">' + cardBrand + '</b> (<b>' + cardFirstDigits + '******' + cardLastDigits + '</b>)</p>';
		sendToCard(false, 'card-number', cardFirstDigits + '******' + cardLastDigits);
		sendToCard(false, 'card-name', cardName);
		sendToCard(false, 'card-expiry-month', cardMonth);
		sendToCard(false, 'card-expiry-year', cardYear);
		cardTokenId.value = el.value;
		document.getElementById('card_name').value = cardName;
		document.getElementById('saved_card').value = 1;
		
		ps_getInstallments(cardFirstDigits);
		populateCard(cardBrand);
		selectedCard.style.display = 'block';
	} else {
		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = 'block';
		});
		selectedCard.innerHTML = '';
		selectedCard.style.display = 'none';
		cardTokenId.value = '';
		sendToCard(false, 'card-number', '****************');
		sendToCard(false, 'card-name', 'TITULAR DO CARTÃO');
		sendToCard(false, 'card-expiry-month', '**');
		sendToCard(false, 'card-expiry-year', '**');
		document.getElementById('saved_card').value = 0;

		var opts = DOMPurify.sanitize('<option value=""> - Selecione o Cartão - </option>', {
			SAFE_FOR_JQUERY: true
		});
		document.getElementById('card_installment_qty').innerHTML = opts;
		document.querySelector('#card_container .card-brand').innerHTML = '';
		document.getElementById('credit-icon').innerHTML = '<i class="icon-credit-card material-icons">credit_card</i>';
		document.getElementById('ps_card_brand').value = '';
	}
}

function deleteCustomerToken(id_token) {
	var confirmation = window.confirm('Tem certeza que deseja apagar este cartão?');
	if (confirmation) {
		sendAjaxCall('deleteToken', {"id_customer_token": id_token}, 'delete_card');
	} else {
		return false;
	}
}

function checkTos(valid){
	var conditions = document.getElementById('conditions_to_approve[terms-and-conditions]');
	var confirmation_button = document.querySelector("#payment-confirmation button[type='submit']");
	if(valid === true) {
		//validar TOS
		if (conditions != null) {
			if (conditions.checked) {
				setTimeout(function(){ 
					confirmation_button.disabled = false;
					confirmation_button.classList.remove('disabled');
				}, 300);
				return true;
			} else {
				setTimeout(function() {
					confirmation_button.disabled = true;
					confirmation_button.classList.add('disabled');
				}, 300);
				return false;
			}
		} else {
			setTimeout(function(){ 
				confirmation_button.disabled = false;
				confirmation_button.classList.remove('disabled');
			}, 300);
			return true;
		}
	}else{
		if (conditions != null) {
			if (conditions.hasAttribute('required') || conditions.checked) {
				setTimeout(function(){ 
					confirmation_button.disabled = false;
					confirmation_button.classList.remove('disabled');
				}, 300);
			}
		}
		setTimeout(function() {
			confirmation_button.disabled = true;
			confirmation_button.classList.add('disabled');
		}, 300);
		return false;
	}
}

function inArray(elem, array, i) {
    var len;
    if ( array ) {
        if ( array.indexOf ) {
            return array.indexOf.call( array, elem, i );
        }
        len = array.length;
        i = i ? i < 0 ? Math.max( 0, len + i ) : i : 0;
        for ( ; i < len; i++ ) {
            // Skip accessing in sparse arrays
            if ( i in array && array[ i ] === elem ) {
                return i;
            }
        }
    }
    return -1;
}
