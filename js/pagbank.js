/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2026 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2026 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

var orderValue = '';
var cardNumber = '';
var cardBin = '';
var card_brand = '';
var valid_area_codes = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', 
						'32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49',
						'51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73',
						'74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92',
						'93', '94', '95', '96', '97', '98', '99'];

$(document).ready(function() {
	var pay_options = document.querySelectorAll("input[name='payment-option']");
    var conditions_to_approve = document.getElementById('conditions_to_approve[terms-and-conditions]');
	var card_form = $('#card_pagbank');
	var bankslip_form = $('#bankslip_pagbank');
	var pix_form = $('#pix_pagbank');
	var wallet_form = $('#wallet_pagbank');
	var google_form = $('#google_pagbank');
	var pagbank_module = false;
	if (typeof pgb_ps_version !== 'undefined' && pgb_ps_version >= '9.0') {
		var bootstrap_version = $.fn.modal.Constructor.VERSION;
		if (typeof bootstrap_version !== 'undefined' && bootstrap_version >= '5.2') {
			const elements = document.querySelectorAll('.pagbank_form');
			elements.forEach(element => {
				element.classList.add('bootstrap_5');
			});
		}
	}
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
							document.getElementById('pagbank_card_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (bankslip_form.is(":visible")) {
						if (ps_validateBankslip() == false) {
							document.getElementById('pagbank_bankslip_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (pix_form.is(":visible")) {
						if (ps_validatePix() == false) {
							document.getElementById('pagbank_pix_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (wallet_form.is(":visible")) {
						if (ps_validateWallet() == false) {
							document.getElementById('pagbank_wallet_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
							this.checked = false;
						}
					}
					if (google_form.is(":visible")) {
						if (ps_validateGoogle() == false) {
							document.getElementById('pagbank_google_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
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
				document.getElementById('card_name').focus();
			}
			if (bankslip_form.is(":visible")) {
				ps_validateBankslip();
				document.getElementById('bankslip_name').focus();
			}
			if (pix_form.is(":visible")) {
				ps_validatePix();
				document.getElementById('pix_name').focus();
			}
			if (wallet_form.is(":visible")) {
				ps_validateWallet();
				document.getElementById('wallet_name').focus();
			}
			if (google_form.is(":visible")) {
				ps_validateGoogle();
				document.getElementById('google_name').focus();
			}
		}
	}

	var orderValueField = document.getElementById('order_value');
	if (orderValueField != null) {
		orderValueField.addEventListener('change', function() {
			if (pgb_ps_version < '1.7') {
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
	var installmentsGoogleQtyField = document.getElementById('google_card_installment_qty');
	if (installmentsGoogleQtyField != null) {
		installmentsGoogleQtyField.addEventListener('change', function(e) {
			ps_setInstallment('google_card_installment_qty');
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
	var submitWalletButton = document.getElementById('submitWallet');
	if (submitWalletButton != null) {
		submitWalletButton.addEventListener('click', function (e) {
			e.preventDefault();
			ps_walletCheckout(e);
		});
	}
	var submitGoogleButton = document.getElementById('submitGoogle');
	if (submitGoogleButton != null) {
		submitGoogleButton.addEventListener('click', function (e) {
			e.preventDefault();
			ps_googleCheckout(e);
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
	
	sendToCard(false, 'card-number', '****************');
	sendToCard(false, 'card-name', 'TITULAR DO CARTÃO');
	sendToCard(false, 'card-expiry-month', '**');
	sendToCard(false, 'card-expiry-year', '**');
	sendToCard(false, 'card-number', '****************');

	var thisNum;
	var cardNumberField = document.getElementById('card_number');
	if(typeof cardNumberField !== 'undefined' && cardNumberField !== null) {
		cardNumberField.addEventListener('blur', function (e) {
			thisNum = this.value.replace(/[^0-9]+/g, '');
			if (thisNum !== '' && thisNum.length >= 13) {
				ps_getInstallments(thisNum.substring(0,6));
				sendToCard(this.id, 'card-number');
			}
		});
	}

    var saveCardFaq = document.getElementById('save-card-faq');
	if (saveCardFaq != null) {
		$('.fancy-button').fancybox();
	}

	if(typeof pgb_payment_google_pay !== 'undefined' && pgb_payment_google_pay == 1 &&
	typeof pgb_google_merchant_id.length !== 'undefined' && pgb_google_merchant_id.length >= 13){
		getGooglePaymentsClient();
		onGooglePayLoaded();
	}
});

function getGooglePaymentsClient() {
	if (pgb_google_environment == 1) {
		var google_env = 'PRODUCTION';
	} else {
		var google_env = 'TEST';
	}
	var paymentsClient = new google.payments.api.PaymentsClient({environment: google_env});
	return paymentsClient;
}

function getGooglePaymentDataRequest() {
	var baseRequest = {
		apiVersion: 2,
		apiVersionMinor: 0
	};
	var tokenizationSpec = {
		type: 'PAYMENT_GATEWAY',
			parameters: {
			'gateway': 'pagbank',
			'gatewayMerchantId': pgb_account_id
			}
		};
	var	baseCardPaymentMethod = {
		type: 'CARD',
		tokenizationSpecification: tokenizationSpec,
		parameters: {
			allowedCardNetworks: ['VISA', 'MASTERCARD', 'ELO', 'AMEX'],
			allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
			billingAddressRequired: true,
			billingAddressParameters: {
				format: 'FULL',
				phoneNumberRequired: true
			}
		}
	};
	var orderValue = document.getElementById('order_value').value;
	var paymentDataRequest = Object.assign({}, baseRequest);
		paymentDataRequest.allowedPaymentMethods = [baseCardPaymentMethod];
		paymentDataRequest.transactionInfo = {
			countryCode: 'BR',
			currencyCode: 'BRL',
			totalPriceStatus: 'FINAL',
			totalPrice: orderValue
		};
		paymentDataRequest.merchantInfo = {
			merchantName: pgb_shop_name,
			merchantId: pgb_google_merchant_id
		};

	return paymentDataRequest;
}

function onGooglePayLoaded() {
	var paymentsClient = getGooglePaymentsClient();
	var paymentDataRequest = getGooglePaymentDataRequest();
	paymentsClient.isReadyToPay(paymentDataRequest)
	.then(function(response) {
		if (response.result) {
			addGooglePayButton();
		}
	})
	.catch(function(err) {
		console.error(err);
	});
}

function addGooglePayButton() {
	var paymentsClient = getGooglePaymentsClient();
	var show_btn_google = paymentsClient.createButton({
		buttonColor: 'black',
		buttonType: 'pay',
		buttonRadius: 4,
		buttonLocale: 'pt',
		buttonSizeMode: 'fill',
		onClick: onGooglePaymentButtonClicked
	});
	document.getElementById('show_btn_google').appendChild(show_btn_google);
}

function onGooglePaymentButtonClicked() {
	var paymentsClient = getGooglePaymentsClient();
	var paymentDataRequest = getGooglePaymentDataRequest();
	paymentsClient.loadPaymentData(paymentDataRequest).then(function(paymentData){
		var paymentToken = paymentData.paymentMethodData.tokenizationData.token;
		var paymentBrand = paymentData.paymentMethodData.info.cardNetwork;
		var paymentLastDigits = paymentData.paymentMethodData.info.cardDetails
		infoAndBrandGooglePayment(paymentBrand, paymentLastDigits, paymentToken);
		if (pgb_msg_console == 1) {
			console.log(paymentToken);
			console.log(paymentBrand);
			console.log(paymentLastDigits);
		}
	}).catch(function(err){
		if (pgb_msg_console == 1) {
			console.log('Verifique se o Merchant ID está correto.');
			console.error(err);
		}
	});
}

function infoAndBrandGooglePayment(paymentBrand, paymentLastDigits, signature) {
	if (paymentBrand == 'VISA') {
		c_b = 400052;
	} else if (paymentBrand == 'MASTERCARD') {
		c_b = 555566;
	} else if (paymentBrand == 'ELO') {
		c_b = 451416;
	} else if (paymentBrand == 'AMEX') {
		c_b = 375365;
	}
	ps_getInstallments(c_b, true);
	document.getElementById('google_card_brand').value = paymentBrand;
	document.getElementById('google_card_bin').value = c_b;
	document.getElementById('google_last_digits').value = paymentLastDigits;
	document.getElementById('google_signature').value = JSON.stringify(signature);
	var selectedCardGoogle = document.getElementById('google_selected_card');
	selectedCardGoogle.innerHTML = DOMPurify.sanitize('<p>Você selecionou o cartão: <br /><b class="text-uppercase">' + paymentBrand + ' - FINAL: ' + paymentLastDigits + '</b></p>', { SAFE_FOR_JQUERY: true });
	selectedCardGoogle.style.display = 'block';
}

function ps_getInstallments(card_number, google_pay = false) {
	if (google_pay){
		var card_bin = card_number;
	} else {
		var card_bin = card_number.substring(0,6);
	}
    var orderValue = document.getElementById('order_value').value;
    var maxInstallments = pgb_max_installments;
    var installmentsMinValue = pgb_installments_min_value;
    var installmentsMinType = pgb_installments_min_type;
	var opts = '<option value=""> - Digite o número do cartão - </option>';
	var params = {
		'action': 'installments',
		'value': orderValue.replace(/[.,\s]/g, ''),
		'payment_methods': 'credit_card',
		'credit_card_bin': card_bin,
	};
	$.ajax({
		url: pgb_function_url,
		cache: false,
		dataType: 'Json',
		data: params,
		beforeSend: function () {
			showLoading(false, 'installments');
		},
		success: function (data, xhr) {
			var response = data.response;
			var error_msg;
			if(typeof response == 'string'){
				response = JSON.parse(data.response);
			}console.log(response);
			if (response.error_messages && response.error_messages != false || Array.isArray(response.error_messages)) {
				changeFieldClassName('card_number', true);
				error_msg = 'Cartão inválido! Por favor, informe outro cartão.';
				showError('<p>'+ error_msg +'</p>', 10, 'pagbank_card_error');
				showLoading('hide');
				document.getElementById('card_installment_qty').innerHTML = DOMPurify.sanitize(opts, { SAFE_FOR_JQUERY: true });
				document.getElementById('card_installment_qty').click();
				document.getElementById('credit-icon').innerHTML = DOMPurify.sanitize('<i class="icon-credit-card material-icons"></i>', { SAFE_FOR_JQUERY: true });
				document.getElementById('card_brand').value = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
				document.getElementById('card_bin').value = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
				document.querySelector('#card_container .card-brand').innerHTML = DOMPurify.sanitize('<i class="icon-credit-card material-icons"></i>', { SAFE_FOR_JQUERY: true });
			} else {
				var cardObject = response.payment_methods.credit_card;
				var cardBrand = Object.keys(cardObject)[0];
				var installments = cardObject[cardBrand].installment_plans;
				if (google_pay) {
					document.getElementById('google_get_installments_fees').value = JSON.stringify(installments);
				} else {
					populateCard(cardBrand);
					document.getElementById('card_bin').value = card_bin;
					document.getElementById('get_installments_fees').value = JSON.stringify(installments);
				}
				opts = '<option value=""> - Selecione a parcela - </option>';
				installments.forEach((parc) => {
					var optionQty = parc.installments;
					var optionValue = Number(parc.installment_value/100);

					if (!google_pay && optionQty == 1 && pgb_discount_type > 0 && pgb_credit_card_value > 0 && pgb_discount_card == 1) {
						optionValue = pgb_credit_card_value;
					} else if (google_pay && optionQty == 1 && pgb_discount_type > 0 && pgb_google_pay_value > 0 && pgb_discount_google == 1) {
						optionValue = pgb_google_pay_value;
					}

					var optionTotal = Number((optionQty * optionValue));
					var strInterest = '';
					if (parc.interest_free === true) {
						strInterest = ' (sem juros)';
					} else {
						strInterest = '';
					}
					var optionLabel = (optionQty + ' x ' + formatMoney(optionValue) + strInterest + ' Total: ' + formatMoney(optionTotal));
					var formattedValue = Number(optionValue).toMoney(2, '.', ',');

					if (installmentsMinValue == 0) {
						opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>';
					}else if(installmentsMinValue >= 1 && installmentsMinType == 0){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue) {
								//
							}else{
								opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>';
							}
						}
					}else if(installmentsMinValue >= 1 && installmentsMinType == 1){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue) {
								if (optionQty == 1) {
									opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>';
								}
							}else{
								opts += '<option value="' + optionQty + '" dataprice="' + formattedValue + '">' + optionLabel + '</option>';
							}
						}
					}
				});
				if (google_pay){
					document.getElementById('google_card_installment_qty').innerHTML = DOMPurify.sanitize(opts, { SAFE_FOR_JQUERY: true });
					document.getElementById('google_card_installment_qty').click();
				} else {
					document.getElementById('card_installment_qty').innerHTML = DOMPurify.sanitize(opts, { SAFE_FOR_JQUERY: true });
					document.getElementById('card_installment_qty').click();
					changeFieldClassName('card_number');
				}
			}
		},
		complete: function () {
			showLoading('hide');
		},
		error: function (xhr) {
			if (pgb_msg_console == 1) {
				console.log(xhr.status);
			}
			showLoading('hide');
		}
	});

}

function ps_keydown() {
	document.addEventListener('keydown', function(event) {
		if (event.ctrlKey && (event.key === 'r' || event.keyCode === 82)) {
		event.preventDefault();
		}
		if (event.key === 'F5' || event.keyCode === 116) {
		event.preventDefault();
		}
	});
	document.getElementsByTagName('body')[0].style = 'overscroll-behavior: contain';
}

function ps_cardCheckout(e) {
	e.preventDefault();
    if (ps_validateCard() !== false) {
		ps_keydown();
		showLoading();
		var encryptedCard = getEncryptedCard();
		if(encryptedCard !== false) {
			document.getElementById('encrypted_card').value = encryptedCard;
			var card_pagbank = document.getElementById('card_pagbank');
			card_pagbank.submit();
		}
    }else{
		document.getElementById('pagbank_card_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_bankslipCheckout(e) {
	e.preventDefault();
    if (ps_validateBankslip() !== false) {
		ps_keydown();
		showLoading();
		var bankslip_pagbank = document.getElementById('bankslip_pagbank');
		bankslip_pagbank.submit();
    }else{
		document.getElementById('pagbank_bankslip_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_pixCheckout(e) {
	e.preventDefault();
	if (ps_validatePix() !== false){
		ps_keydown();
		showLoading();
		var pix_pagbank = document.getElementById('pix_pagbank');
		pix_pagbank.submit();
    }else{
		document.getElementById('pagbank_pix_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_walletCheckout(e) {
	e.preventDefault();
	if (ps_validateWallet() !== false){
		ps_keydown();
		showLoading();
		var wallet_pagbank = document.getElementById('wallet_pagbank');
		wallet_pagbank.submit();
    }else{
		document.getElementById('pagbank_wallet_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_googleCheckout(e) {
	e.preventDefault();
	if (ps_validateGoogle() !== false){
		ps_keydown();
		showLoading();
		var google_pagbank = document.getElementById('google_pagbank');
		google_pagbank.submit();
    }else{
		document.getElementById('pagbank_google_error').scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
		return false;
	}
}

function ps_setInstallment(id) {
    var sel = document.getElementById(id);
	var sel_name = document.getElementById(id).name;
	var option = sel.options[sel.selectedIndex];
	if (option.value > 0) {
		if (sel_name === 'card_installment_qty') {
			document.getElementById('card_installment_value').value = option.getAttribute('dataprice');
			document.getElementById('card_installments').value = option.value;
		} else if (sel_name === 'google_card_installment_qty') {
			document.getElementById('google_installment_value').value = option.getAttribute('dataprice');
			document.getElementById('google_installments').value = option.value;
		}
	}
}

function checkField(field_id) {
	var field = document.getElementById(field_id);
    var card_name = document.getElementById('card_name');
    var bankslip_name = document.getElementById('bankslip_name');
    var pix_name = document.getElementById('pix_name');
	var wallet_name = document.getElementById('wallet_name');
	var google_name = document.getElementById('google_name');
	var card_form = $('#card_pagbank');
	var bankslip_form = $('#bankslip_pagbank');
	var pix_form = $('#pix_pagbank');
	var wallet_form = $('#wallet_pagbank');
	var google_form = $('#google_pagbank');
	
    if (field_id == 'card_name' || 
		field_id == 'pix_name' || 
		field_id == 'bankslip_name' || 
		field_id == 'wallet_name' ||
		field_id == 'google_name'
	) {
        var cardName = field.value.trim();
        if (cardName.length > 3) {
			if (cardName.match('^[a-z A-Z]{3,45}$')) {
				changeFieldClassName(field_id);
	
				if (card_name != null && card_name.value != cardName) {
					card_name.value = cardName;
				}
				if (bankslip_name != null && bankslip_name.value != cardName) {
					bankslip_name.value = cardName;
				}
				if (pix_name != null && pix_name.value != cardName) {
					pix_name.value = cardName;
				}
				if (wallet_name != null && wallet_name.value != cardName) {
					wallet_name.value = cardName;
				}
				if (google_name != null && google_name.value != cardName) {
					google_name.value = cardName;
				}
			} else {
				changeFieldClassName(field_id, true);
			}
		} else {
			changeFieldClassName(field_id, true);
		}
    } else if (field_id == 'card_year' || field_id == 'card_month') {
        var cardYearField = document.getElementById(field_id);
		var cardYear = cardYearField.options[cardYearField.selectedIndex].value;
        if (cardYear.length < 2) {
			changeFieldClassName(field_id, true);
        } else {
			changeFieldClassName(field_id);
        }
    } else if (field_id == 'card_installment_qty') {
        var cardInstField = document.getElementById(field_id);
		var cardInst = cardInstField.options[cardInstField.selectedIndex].value;
        if (cardInst < 1) {
			changeFieldClassName(field_id, true);
        } else {
			changeFieldClassName(field_id);
        }
    } else if (field_id == 'card_cvv') {
        var cardCvv = document.getElementById(field_id).value.replace(/[^0-9]/g, '');
        if (cardCvv.length < 3) {
			changeFieldClassName(field_id, true);
        } else {
			changeFieldClassName(field_id);
        }
    } else if (field_id == 'card_doc') {
		verifyDoc(field_id);
	} else {
        if (field.length == 0) {
			changeFieldClassName(field_id, true);
        } else {
			changeFieldClassName(field_id);
        }
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
	if (wallet_form.is(":visible")) {
		ps_validateWallet();
	}
	if (google_form.is(":visible")) {
		ps_validateGoogle();
	}
}

function ps_validateCard() {
	var cardTokenId = document.getElementById('card_token_id');
	var html = '';
	var errorFields = [];
	var okFields = [];
	var address_error = false;

	var holder = document.getElementById('card_name').value.trim();
	if (holder.length == 0) {
		html += 'Titular do Cartão não preenchido. <br />';
		errorFields.push('card_name');
	} else if (holder.length < 3) {
        html += 'Titular do Cartão é Inválido. <br />';
		errorFields.push('card_name');
	} else {
		okFields.push('card_name');
	}

	var telephone = document.getElementById('card_phone').value.replace(/[^0-9]/g,'');    
	if (telephone.length == 0) {
		html += 'Telefone não preenchido. <br />';
		errorFields.push('card_phone');
	} else if (!validatePhoneNumber('card_phone')) {
		html += 'Telefone inválido. <br />';
		errorFields.push('card_phone');
	} else {
		okFields.push('card_phone');
	}

	var cpf = document.getElementById('card_doc').value;
	if (cpf.length == 0) {
		html += 'CPF/CNPJ não preenchido. <br />';
		errorFields.push('card_doc');
	} else if (!verifyDoc('card_doc')) {
		html += 'CPF/CNPJ é inválido. <br />';
		errorFields.push('card_doc');
	} else {
		okFields.push('card_doc');
	}

	if (cardTokenId.value > 0) {
		if (pgb_msg_console == 1) {
			console.log('cartão tokenizado.');
		}
	} else {
		var cardNumber = document.getElementById('card_number').value.replace(/[^0-9]/g, '');
		if (cardNumber.length < 13) {
			html += 'Número do Cartão não preenchido. <br />';
			errorFields.push('card_number');
		} else {
			okFields.push('card_number');
		}

		var expMonth = document.getElementById('card_month').value.replace(/[^0-9]/g, '');
		if (expMonth.length == 0) {
			html += 'Mês do Vencimento do Cartão não preenchido. <br />';
			errorFields.push('card_month');
		} else {
			okFields.push('card_month');
		}

		var expYear = document.getElementById('card_year').value.replace(/[^0-9]/g, '');
		if (expYear.length == 0) {
			html += 'Ano do Vencimento do Cartão não preenchido. <br />';
			errorFields.push('card_year');
		} else {
			okFields.push('card_year');
		}

		var brand = document.getElementById('card_brand').value.trim().toLowerCase();
		var cvv = document.getElementById('card_cvv').value.replace(/[^0-9]/g, '');
		if (cvv.length == 0) {
			html += 'Código de Segurança do Cartão não preenchido. <br />';
			errorFields.push('card_cvv');
		} else if (checkCVV(brand) === false) {
			html += 'Código de Segurança do Cartão inválido! Por favor, verifique. <br />';
			errorFields.push('card_cvv');
		} else {
			okFields.push('card_cvv');
		}
	}
	var installments_qty = document.getElementById('card_installment_qty').value;
	if (installments_qty.length == 0 || parseInt(installments_qty) < 1) {
		html += 'Quantidade de Parcelas não preenchida. <br />';
		errorFields.push('card_installment_qty');
	} else {
		okFields.push('card_installment_qty');
	}

	var invoiceAddress = document.getElementById('card_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('card_address_invoice');
		address_error = true;
	} else {
		okFields.push('card_address_invoice');
	}

	var postcodeNumber = document.getElementById('card_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('card_postcode_invoice');
		address_error = true;
	} else {
		okFields.push('card_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('card_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('card_number_invoice');
		address_error = true;
	} else {
		okFields.push('card_number_invoice');
	}

	var invoiceDistrict = document.getElementById('card_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('card_address2_invoice');
		address_error = true;
	} else {
		okFields.push('card_address2_invoice');
	}

	var invoiceCity = document.getElementById('card_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('card_city_invoice');
		address_error = true;
	} else {
		okFields.push('card_city_invoice');
	}

	var invoiceState = document.getElementById('card_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('card_state_invoice');
		address_error = true;
	} else {
		okFields.push('card_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false, false, true);
		}
	}

	if (html.length > 0) {
		if (pgb_msg_console == 1) {
			console.log(html);
		}
		showError(html, 5, 'pagbank_card_error');
		if (address_error === true) {
			$('#card_address').collapse('show');
		}
		if (pgb_ps_version >= '1.7') {
			checkTos(false);
		}
		return false;
	} else {
		if (pgb_ps_version >= '1.7') {
			checkTos(true);
		}
		return true;
	}
}

function ps_validateBankslip() {
    var html = '';
    var errorFields = [];
	var okFields = [];
    var adress_error = false;

    var nome = document.getElementById('bankslip_name').value.trim();
    if (nome.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('bankslip_name');
	} else if (nome.length < 3) {
        html += 'Nome/Razão Social é Inválido. <br />';
		errorFields.push('bankslip_name');
    } else {
		okFields.push('bankslip_name');
	}

    var telephone = document.getElementById('bankslip_phone').value.replace(/[^0-9]/g, '');
    if (telephone.length == 0) {
        html += 'Telefone não preenchido. <br />';
		errorFields.push('bankslip_phone');
    } else if (!validatePhoneNumber('bankslip_phone')) {
        html += 'Telefone inválido. ';
		errorFields.push('bankslip_phone');
    } else {
		okFields.push('bankslip_phone');
	}

    var cpf = document.getElementById('bankslip_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br />';
		errorFields.push('bankslip_doc');
    } else if (!verifyDoc('bankslip_doc')) {
		html += 'CPF/CNPJ inválido. <br />';
		errorFields.push('bankslip_doc');
    } else {
		okFields.push('bankslip_doc');
	}

	var invoiceAddress = document.getElementById('bankslip_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('bankslip_address_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_address_invoice');
	}

	var postcodeNumber = document.getElementById('bankslip_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('bankslip_postcode_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('bankslip_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('bankslip_number_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_number_invoice');
	}

	var invoiceDistrict = document.getElementById('bankslip_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('bankslip_address2_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_address2_invoice');
	}

	var invoiceCity = document.getElementById('bankslip_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('bankslip_city_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_city_invoice');
	}

	var invoiceState = document.getElementById('bankslip_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('bankslip_state_invoice');
		adress_error = true;
	} else {
		okFields.push('bankslip_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false, false, true);
		}
	}

    if (html.length > 0) {
        showError(html, 5, 'pagbank_bankslip_error');
		if (adress_error === true) {
			$('#bankslip_address').collapse('show');
		}
		if (pgb_ps_version >= '1.7') {
			checkTos(false);
		}
        return false;
    } else {
		if (pgb_ps_version >= '1.7') {
			checkTos(true);
		}
        return true;
    }
}

function ps_validatePix() {
    var html = '';
    var errorFields = [];
	var okFields = [];
	var pix_adress_error = false;

    var nome = document.getElementById('pix_name').value.trim();
    if (nome.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('pix_name');
    } else if (nome.length < 3) {
        html += 'Nome/Razão Social é Inválido. <br />';
		errorFields.push('pix_name');
    } else {
		okFields.push('pix_name');
	}

    var telephone = document.getElementById('pix_phone').value.replace(/[^0-9]/g, '');
    if (telephone.length == 0) {
        html += 'Telefone não preenchido. <br />';
		errorFields.push('pix_phone');
    } else if (!validatePhoneNumber('pix_phone')) {
        html += 'Telefone inválido. ';
		errorFields.push('pix_phone');
    } else {
		okFields.push('pix_phone');
	}

    var cpf = document.getElementById('pix_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br />';
		errorFields.push('pix_doc');
    } else if (!verifyDoc('pix_doc')) {
		html += 'CPF/CNPJ inválido. <br />';
		errorFields.push('pix_doc');
    } else {
		okFields.push('pix_doc');
	}

	var invoiceAddress = document.getElementById('pix_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('pix_address_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_address_invoice');
	}

	var postcodeNumber = document.getElementById('pix_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('pix_postcode_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('pix_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('pix_number_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_number_invoice');
	}

	var invoiceDistrict = document.getElementById('pix_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('pix_address2_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_address2_invoice');
	}

	var invoiceCity = document.getElementById('pix_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('pix_city_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_city_invoice');
	}

	var invoiceState = document.getElementById('pix_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('pix_state_invoice');
		pix_adress_error = true;
	} else {
		okFields.push('pix_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false, false, true);
		}
	}

    if (html.length > 0) {
        showError(html, 5, 'pagbank_pix_error');
		if (pix_adress_error === true) {
			$('#pix_address').collapse('show');
		}
		if (pgb_ps_version >= '1.7') {
			checkTos(false);
		}
        return false;
    } else {
		if (pgb_ps_version >= '1.7') {
			checkTos(true);
		}
        return true;
    }
}

function ps_validateWallet() {
    var html = '';
    var errorFields = [];
	var okFields = [];
	var wallet_adress_error = false;

    var nome = document.getElementById('wallet_name').value.trim();
    if (nome.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('wallet_name');
    } else if (nome.length < 3) {
        html += 'Nome/Razão Social é Inválido. <br />';
		errorFields.push('wallet_name');
    } else {
		okFields.push('wallet_name');
	}

    var telephone = document.getElementById('wallet_phone').value.replace(/[^0-9]/g, '');
    if (telephone.length == 0) {
        html += 'Telefone não preenchido. <br />';
		errorFields.push('wallet_phone');
    } else if (!validatePhoneNumber('wallet_phone')) {
        html += 'Telefone inválido. ';
		errorFields.push('wallet_phone');
    } else {
		okFields.push('wallet_phone');
	}

    var cpf = document.getElementById('wallet_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (cpf.length == 0) {
        html += 'CPF/CNPJ é obrigatório. <br />';
		errorFields.push('wallet_doc');
    } else if (!verifyDoc('wallet_doc')) {
		html += 'CPF/CNPJ inválido. <br />';
		errorFields.push('wallet_doc');
    } else {
		okFields.push('wallet_doc');
	}

	var invoiceAddress = document.getElementById('wallet_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('wallet_address_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_address_invoice');
	}

	var postcodeNumber = document.getElementById('wallet_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('wallet_postcode_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('wallet_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('wallet_number_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_number_invoice');
	}

	var invoiceDistrict = document.getElementById('wallet_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('wallet_address2_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_address2_invoice');
	}

	var invoiceCity = document.getElementById('wallet_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('wallet_city_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_city_invoice');
	}

	var invoiceState = document.getElementById('wallet_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('wallet_state_invoice');
		wallet_adress_error = true;
	} else {
		okFields.push('wallet_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false, false, true);
		}
	}

    if (html.length > 0) {
        showError(html, 5, 'pagbank_wallet_error');
		if (wallet_adress_error === true) {
			$('#wallet_address').collapse('show');
		}
		if (pgb_ps_version >= '1.7') {
			checkTos(false);
		}
        return false;
    } else {
		if (pgb_ps_version >= '1.7') {
			checkTos(true);
		}
        return true;
    }
}

function ps_validateGoogle() {
	var html = '';
	var errorFields = [];
	var okFields = [];
	var address_error = false;

	var holder = document.getElementById('google_name').value.trim();
	if (holder.length == 0) {
		html += 'Titular do Cartão não preenchido. <br />';
		errorFields.push('google_name');
    } else if (holder.length < 3) {
        html += 'Titular do Cartão é Inválido. <br />';
		errorFields.push('google_name');
    } else {
		okFields.push('google_name');
	}

	var telephone = document.getElementById('google_phone').value.replace(/[^0-9]/g,'');    
	if (telephone.length == 0) {
		html += 'Telefone não preenchido. <br />';
		errorFields.push('google_phone');
	} else if (!validatePhoneNumber('google_phone')) {
		html += 'Telefone inválido. <br />';
		errorFields.push('google_phone');
	} else {
		okFields.push('google_phone');
	}

	var cpf = document.getElementById('google_doc').value.replace(/[^A-Za-z0-9]/g, '');
	if (cpf.length == 0) {
		html += 'CPF/CNPJ não preenchido. <br />';
		errorFields.push('google_doc');
	} else if (!verifyDoc('google_doc')) {
		html += 'CPF/CNPJ é inválido. <br />';
		errorFields.push('google_doc');
	} else {
		okFields.push('google_doc');
	}

	var installments_qty = document.getElementById('google_card_installment_qty').value;
	if (installments_qty.length == 0 || parseInt(installments_qty) < 1) {
		html += 'Quantidade de Parcelas não preenchida. <br />';
		errorFields.push('google_card_installment_qty');
	} else {
		okFields.push('google_card_installment_qty');
	}

	var invoiceAddress = document.getElementById('google_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('google_address_invoice');
		address_error = true;
	} else {
		okFields.push('google_address_invoice');
	}

	var postcodeNumber = document.getElementById('google_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('google_postcode_invoice');
		address_error = true;
	} else {
		okFields.push('google_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('google_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('google_number_invoice');
		address_error = true;
	} else {
		okFields.push('google_number_invoice');
	}

	var invoiceDistrict = document.getElementById('google_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('google_address2_invoice');
		address_error = true;
	} else {
		okFields.push('google_address2_invoice');
	}

	var invoiceCity = document.getElementById('google_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('google_city_invoice');
		address_error = true;
	} else {
		okFields.push('google_city_invoice');
	}

	var invoiceState = document.getElementById('google_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('google_state_invoice');
		address_error = true;
	} else {
		okFields.push('google_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false, false, true);
		}
	}

	if (html.length > 0) {
		if (pgb_msg_console == 1) {
			console.log(html);
		}
		showError(html, 5, 'pagbank_google_error');
		if (address_error === true) {
			$('#google_address').collapse('show');
		}
		if (pgb_ps_version >= '1.7') {
			checkTos(false);
		}
		return false;
	} else {
		if (pgb_ps_version >= '1.7') {
			checkTos(true);
		}
		return true;
	}
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

function validateCPF(id) {
    var cpfField = document.getElementById(id);
    var cpf = cpfField.value;
    var err = 0;
    var exp = /\.|\-/g;
        cpf = cpf.toString().replace(exp, "");
	var id_error = '';
	if (id == 'bankslip_doc') {
		id_error = 'pagbank_bankslip_error';
	} else if (id == 'card_doc') {
		id_error = 'pagbank_card_error';
	} else if (id == 'google_doc') {
		id_error = 'pagbank_google_error';
	} else if (id == 'pix_doc') {
		id_error = 'pagbank_pix_error';
	} else if (id == 'wallet_doc') {
		id_error = 'pagbank_wallet_error';
	}
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
		changeFieldClassName(id);
        return true;
    } else {
		changeFieldClassName(id, true);
        showError('CPF incorreto. Por favor, verifique.', 5, id_error);
        return false;
    }
}

function validateCNPJ(id) {
	var cnpjField = document.getElementById(id);
	var cnpj = cnpjField.value;
	var tamanhoCNPJSemDV = 12;
	var regexCNPJ = /^([A-Z\d]){12}(\d){2}$/;
	var regexCaracteresMascara = /[./-]/g;
	var regexCaracteresNaoPermitidos = /[^A-Z\d./-]/i;
	var valorBase = "0".charCodeAt(0);
	var pesosDV = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
	var cnpjZerado = "00000000000000";
	var id_error = '';
	if (id == 'bankslip_doc') {
		id_error = 'pagbank_bankslip_error';
	} else if (id == 'card_doc') {
		id_error = 'pagbank_card_error';
	} else if (id == 'google_doc') {
		id_error = 'pagbank_google_error';
	} else if (id == 'pix_doc') {
		id_error = 'pagbank_pix_error';
	} else if (id == 'wallet_doc') {
		id_error = 'pagbank_wallet_error';
	}

	if (!regexCaracteresNaoPermitidos.test(cnpj)) {
		let cnpjSemMascara = cnpj.replace(regexCaracteresMascara, "");
		if (regexCNPJ.test(cnpjSemMascara) && cnpjSemMascara !== cnpjZerado) {
			const dvInformado = cnpjSemMascara.substring(tamanhoCNPJSemDV);
			if (!regexCaracteresNaoPermitidos.test(cnpj)) {
				let cnpjSemMascara = cnpj.replace(regexCaracteresMascara, "");
				let somatorioDV1 = 0;
				let somatorioDV2 = 0;
				for (let i = 0; i < tamanhoCNPJSemDV; i++) 
				{
					const asciiDigito = cnpjSemMascara.charCodeAt(i) - valorBase;
					somatorioDV1 += asciiDigito * pesosDV[i + 1];
					somatorioDV2 += asciiDigito * pesosDV[i];
				}
				const dv1 = somatorioDV1 % 11 < 2 ? 0 : 11 - (somatorioDV1 % 11);
				somatorioDV2 += dv1 * pesosDV[tamanhoCNPJSemDV];
				const dv2 = somatorioDV2 % 11 < 2 ? 0 : 11 - (somatorioDV2 % 11);
				const dvCalculado = `${dv1}${dv2}`;
				if (dvInformado === dvCalculado) {
					changeFieldClassName(id);
					return true;
				} else {
					changeFieldClassName(id, true);
					showError('CNPJ incorreto. Por favor, verifique. <br />', 5, id_error);
					return false;
				}
			}
		}
    } else {
		changeFieldClassName(id, true);
		showError('CNPJ incorreto. Por favor, verifique. <br />', 5, id_error);
    	return false;
	}
}

function checkCVV(card_brand) {
    "use strict";
    var cvvField = document.getElementById('card_cvv');
	var brand;
    if (card_brand && card_brand != 'undefined') {
        brand = card_brand.toLowerCase();
    } else {
        brand = document.getElementById('card_brand').value.toLowerCase();
    }
    if (cvvField.value) {
        if (brand == 'amex' && cvvField.value.length != 4 || brand != 'amex' && cvvField.value.length != 3) {
			if (fone === '') {
				changeFieldClassName('card_cvv', true);
			}
            if (pgb_msg_console == 1) {
                console.log('CVV inválido. ' + brand + ' com ' + cvvField.value.length + ' caracteres.');
            }
            showError('Código de Validação inválido. ' + brand.toUpperCase() + ' com ' + cvvField.value.length + ' caracteres.', 7, 'pagbank_card_error');
            return false;
        } else {
			changeFieldClassName('card_cvv');
            return true;
        }
    }
}

function validatePhoneNumber(fieldId) {
    "use strict";
    var foneField = document.getElementById(fieldId);
    var fone = foneField.value;
    var card_phone = document.getElementById('card_phone');
    var bankslip_phone = document.getElementById('bankslip_phone');
    var pix_phone = document.getElementById('pix_phone');
	var google_phone = document.getElementById('google_phone');
	var wallet_phone = document.getElementById('wallet_phone');
	var id_error = '';
	if (fieldId == 'bankslip_phone') {
		id_error = 'pagbank_bankslip_error';
	} else if (fieldId == 'card_phone') {
		id_error = 'pagbank_card_error';
	} else if (fieldId == 'google_phone') {
		id_error = 'pagbank_google_error';
	} else if (fieldId == 'pix_phone') {
		id_error = 'pagbank_pix_error';
	} else if (fieldId == 'wallet_phone') {
		id_error = 'pagbank_wallet_error';
	}

	if (!fone || fone === false) {
		fone = document.getElementById('card_phone').value;
	}
	if (fone === '') {
		changeFieldClassName(fieldId, true);
		return;
	}

    var clean = fone.replace(/\D/g, "").trim();
    var reg = /^[1-9]{2}[2-9][0-9]{7,8}$/;
    var areaCode = clean.substring(0, 2);

    if (reg.test(clean)) {
        var areaCodeExists = inArray(areaCode, valid_area_codes);
        if (areaCodeExists < 0) {
            if (pgb_msg_console == 1) {
                console.log('DDD não encontrado (' + areaCode + ')');
            }
			changeFieldClassName(fieldId, true);
            showError('DDD não encontrado (' + areaCode + ')', 5, id_error);
            return false;
        } else {
			changeFieldClassName(fieldId);
			
			if (card_phone != null && card_phone.value != fone) {
				card_phone.value = fone;
			}
			if (bankslip_phone != null && bankslip_phone.value != fone) {
				bankslip_phone.value = fone;
			}
			if (pix_phone != null && pix_phone.value != fone) {
				pix_phone.value = fone;
			}
			if (google_phone != null && google_phone.value != fone) {
				google_phone.value = fone;
			}
			if (wallet_phone != null && wallet_phone.value != fone) {
				wallet_phone.value = fone;
			}

            return true;
        }
    } else {
        if (pgb_msg_console == 1) {
            console.log('Fone: ' + clean);
        }
		changeFieldClassName(fieldId, true);

        showError('Telefone inválido: ' + fone, 5, id_error);
        return false;
    }
}

function showLoading(hide, id) {
    "use strict";

	var submitCard = document.getElementById('submitCard');
	var submitBankSlip = document.getElementById('submitBankSlip');
	var submitPix = document.getElementById('submitPix');
	var submitWallet = document.getElementById('submitWallet');
	var submitGoogle = document.getElementById('submitGoogle');
	
	if (!hide || hide == ''){
		if (id == 'installments' || id == 'delete_card') {
			document.getElementById('pagbankmsg').innerHTML = DOMPurify.sanitize('Validando...', { SAFE_FOR_JQUERY: true });
		} else {
			document.getElementById('pagbankmsg').innerHTML = DOMPurify.sanitize('Por favor, aguarde.<br />Processando pagamento...', { SAFE_FOR_JQUERY: true });
		}
		if (pgb_ps_version < '1.7') {
			if (submitCard != null) {
				submitCard.disabled = true;
			}
			if (submitBankSlip != null) {
				submitBankSlip.disabled = true;
			}
			if (submitPix != null) {
				submitPix.disabled = true;
			}
			if (submitWallet != null) {
				submitWallet.disabled = true;
			}
			if (submitGoogle != null) {
				submitGoogle.disabled = true;
			}
		}
		document.getElementById('pagbankproccess').style.display = 'block';
		document.getElementById('fancy_load').classList.add('loading');
		document.getElementById('fancy_load').style.width = window.innerWidth;
	}else{
		if (pgb_ps_version < '1.7') {
			if (submitCard != null) {
				submitCard.disabled = false;
			}
			if (submitBankSlip != null) {
				submitBankSlip.disabled = false;
			}
			if (submitPix != null) {
				submitPix.disabled = false;
			}
			if (submitWallet != null) {
				submitWallet.disabled = false;
			}
			if (submitGoogle != null) {
				submitGoogle.disabled = false;
			}
		}
		document.getElementById('pagbankproccess').style.display = 'none';
		document.getElementById('fancy_load').classList.remove('loading');
	}
}

function populateCard(brand) {
    "use strict";
	if(brand == ''){
		document.querySelector('#card_container .card-brand').innerHTML = DOMPurify.sanitize('<i class="icon-credit-card material-icons"></i>', { SAFE_FOR_JQUERY: true });
	} else {
		document.getElementById('card_brand').value = brand;
		document.getElementById('credit-icon').innerHTML = DOMPurify.sanitize('<img class="addon-img" src="' + pgb_img_path + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />', { SAFE_FOR_JQUERY: true });
		document.querySelector('#card_container .card-brand').innerHTML = DOMPurify.sanitize('<img class="addon-img" src="' + pgb_img_path + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />', { SAFE_FOR_JQUERY: true });
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
		var card_container = document.getElementById('card_container');
		if(typeof card_container !== 'undefined' && card_container !== null){
			card_container.getElementsByClassName(classe)[0].innerHTML = DOMPurify.sanitize(str, {SAFE_FOR_JQUERY: true});
			if (classe === 'card-number') {
				document.getElementById('number_card').innerHTML = DOMPurify.sanitize(str.replace(/(.{4})/g, '$1 &nbsp;'), {SAFE_FOR_JQUERY: true});
			}
		}
    }
}

function showError(str, t, id) {
    "use strict";
	var controlError = document.getElementById(id);
    controlError.innerHTML = DOMPurify.sanitize(str, {SAFE_FOR_JQUERY: true});
	controlError.classList.add('alert', 'alert-danger');
	controlError.style.display = 'block';

	setTimeout(function () {
        controlError.innerHTML = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		controlError.classList.remove('alert', 'alert-danger');
		controlError.style.display = 'none';
    }, (1000 * t));
}

function verifyDoc(id) {
    "use strict";
    var cpf_cnpj = document.getElementById(id);
    var card_doc = document.getElementById('card_doc');
    var bankslip_doc = document.getElementById('bankslip_doc');
    var pix_doc = document.getElementById('pix_doc');
	var wallet_doc = document.getElementById('wallet_doc');
	var google_doc = document.getElementById('google_doc');
	var fieldValue = cpf_cnpj.value;
    var num = cpf_cnpj.value;
	if(card_doc != null) {
		card_doc.value = fieldValue;
	}
	if(bankslip_doc != null) {
		bankslip_doc.value = fieldValue;
	}
	if(pix_doc != null) {
		pix_doc.value = fieldValue;
	}
	if(wallet_doc != null) {
		wallet_doc.value = fieldValue;
	}
	if(google_doc != null) {
		google_doc.value = fieldValue;
	}
    if (num.length > 14) {
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

function getEncryptedCard() {
	var unindexed_array = $('#card_pagbank').serializeArray();
    var formdata = {};
	$.map(unindexed_array, function(n, i){
		formdata[n.name] = n.value;
	});
	var cardData = PagSeguro.encryptCard({
		publicKey: pgb_public_key,
		holder: formdata.card_name,
		number: formdata.card_number,
		expMonth: formdata.card_month,
		expYear: formdata.card_year,
		securityCode: formdata.card_cvv
	});
	formdata = {};
    return cardData.encryptedCard;
}

function sendAjaxCall(actionCalled, formData, id = false) {
	formData.action = actionCalled;
	var ret;
	$.ajax({
		url: pgb_function_url,
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
			if (data == 'OK') {
				ret = data;
				var item = document.getElementById('token_' + formData.id_customer_token);
				item.parentElement.parentElement.parentElement.parentElement.remove();
				window.alert('Cartão apagado com sucesso!');
				showLoading('hide');
				window.location.reload(true);
			} else {
				var resp_string = 'Houve um erro ao processar seu pagamento. Por favor, tente novamente.';
				var pagbankmsg = document.getElementById('pagbankmsg');
				pagbankmsg.innerHTML = DOMPurify.sanitize(resp_string, { SAFE_FOR_JQUERY: true });
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
			if (pgb_msg_console == 1) {
				console.log(xhr.status);
			}
		}
	});
	return ret;
}

function checkCardToken(el) {
	var savedCards = document.getElementsByClassName('check_token');
	var selectedCard = document.getElementById('selected_card_token');
	var cardTokenId = document.getElementById('card_token_id');
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
		selectedCard.innerHTML = DOMPurify.sanitize('<p>Você está utilizando o cartão: <br /><b class="text-uppercase">' + cardBrand + '</b> (<b>' + cardFirstDigits + '******' + cardLastDigits + '</b>)</p>', { SAFE_FOR_JQUERY: true });
		sendToCard(false, 'card-number', cardFirstDigits + '******' + cardLastDigits);
		sendToCard(false, 'card-name', cardName);
		sendToCard(false, 'card-expiry-month', cardMonth);
		sendToCard(false, 'card-expiry-year', cardYear);
		cardTokenId.value = el.value;
		document.getElementById('card_name').value = cardName;
		document.getElementById('saved_card').value = 1;
		ps_getInstallments(cardFirstDigits);
		selectedCard.style.display = 'block';
	} else {
		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = 'block';
		});
		selectedCard.innerHTML = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		selectedCard.style.display = 'none';
		cardTokenId.value = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		sendToCard(false, 'card-number', '****************');
		sendToCard(false, 'card-name', 'TITULAR DO CARTÃO');
		sendToCard(false, 'card-expiry-month', '**');
		sendToCard(false, 'card-expiry-year', '**');
		document.getElementById('saved_card').value = 0;
		document.getElementById('card_installment_qty').innerHTML = DOMPurify.sanitize('<option value=""> - Digite o número do cartão - </option>', { SAFE_FOR_JQUERY: true });
		document.querySelector('#card_container .card-brand').innerHTML = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		document.getElementById('credit-icon').innerHTML = DOMPurify.sanitize('<i class="icon-credit-card material-icons"></i>', { SAFE_FOR_JQUERY: true });
		document.getElementById('card_brand').value = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		document.getElementById('card_number').value = DOMPurify.sanitize('', { SAFE_FOR_JQUERY: true });
		changeFieldClassName('card_number', false, true);
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
    if (array) {
        if ( array.indexOf ) {
            return array.indexOf.call( array, elem, i );
        }
        len = array.length;
        i = i ? i < 0 ? Math.max( 0, len + i ) : i : 0;
        for ( ; i < len; i++ ) {
            if ( i in array && array[ i ] === elem ) {
                return i;
            }
        }
    }
    return -1;
}

function changeFieldClassName(field, error, remove, add, all) {
	var change_field = document.getElementById(field);
	if (pgb_ps_version >= '9.0') {
		var bs_version = $.fn.modal.Constructor.VERSION;
		var bootstrap5 = false;
		if (typeof bs_version !== 'undefined' && bs_version >= '5.2') {
			bootstrap5 = true;
		}
		if(error === true) {
			if (bootstrap5) {
				change_field.classList.remove('is-valid');
				change_field.classList.add('is-invalid');
			} else {
				change_field.parentElement.classList.remove('has-success');
				change_field.parentElement.classList.add('has-danger');
			}
		} else if(remove === true) {
			if (bootstrap5) {
				change_field.classList.remove('is-valid');
			} else {
				change_field.parentElement.classList.remove('has-success');
			}
		} else if(add === true) {
			if (bootstrap5) {
				change_field.classList.add('is-valid');
			} else {
				change_field.parentElement.classList.add('has-success');
			}
		} else if(all === true) {
			if (bootstrap5) {
				change_field.classList.remove('is-valid');
				change_field.classList.remove('is-invalid');
			} else {
				change_field.parentElement.classList.remove('has-success');
				change_field.parentElement.classList.remove('has-danger');
			}
		} else {
			if (bootstrap5) {
				change_field.classList.remove('is-invalid');
				change_field.classList.add('is-valid');
			} else {
				change_field.parentElement.classList.remove('has-danger');
				change_field.parentElement.classList.add('has-success');
			}
		}
	} else if (pgb_ps_version >= '1.7') {
		if(error === true) {
			change_field.parentElement.classList.remove('has-success');
			change_field.parentElement.classList.add('has-danger');
		} else if(remove === true) {
			change_field.parentElement.classList.remove('has-success');
		} else if(add === true) {
			change_field.parentElement.classList.add('has-success');
		} else if(all === true) {
			change_field.parentElement.classList.remove('has-success');
			change_field.parentElement.classList.remove('has-danger');
		} else {
			change_field.parentElement.classList.remove('has-danger');
			change_field.parentElement.classList.add('has-success');
		}
	} else {
		if(error === true) {
			if (change_field.parentElement.classList.contains('selector') || change_field.parentElement.classList.contains('input-group')) {
				change_field.parentElement.parentElement.classList.remove('form-ok');
				change_field.parentElement.parentElement.classList.add('form-error');
			} else {
				change_field.parentElement.classList.remove('form-ok');
				change_field.parentElement.classList.add('form-error');
			}
		} else if(remove === true) {
			if (change_field.parentElement.classList.contains('selector') || change_field.parentElement.classList.contains('input-group')) {
				change_field.parentElement.parentElement.classList.remove('form-ok');
			} else {
				change_field.parentElement.classList.remove('form-ok');
			}
		} else if(add === true) {
			if (change_field.parentElement.classList.contains('selector') || change_field.parentElement.classList.contains('input-group')) {
				change_field.parentElement.parentElement.classList.add('form-ok');
			} else {
				change_field.parentElement.classList.add('form-ok');
			}
		} else if(all === true) {
			if (change_field.parentElement.classList.contains('selector') || change_field.parentElement.classList.contains('input-group')) {
				change_field.parentElement.parentElement.classList.remove('form-ok');
				change_field.parentElement.parentElement.classList.remove('form-error');
			} else {
				change_field.parentElement.classList.remove('form-ok');
				change_field.parentElement.classList.remove('form-error');
			}
		} else {
			if (change_field.parentElement.classList.contains('selector') || change_field.parentElement.classList.contains('input-group')) {
				change_field.parentElement.parentElement.classList.remove('form-error');
				change_field.parentElement.parentElement.classList.add('form-ok');
			} else {
				change_field.parentElement.classList.remove('form-error');
				change_field.parentElement.classList.add('form-ok');
			}
		}
	}
}
