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

var orderValuePagbank = '';
var cardNumber = '';
var cardBrand = '';
var isSecondField = '';
var checkTwoOpt = false;
var checkCardOne = false;
var checkCardTwo = false;
$(document).ready(function() {
	var payOptions = document.querySelectorAll("input[name='payment-option']");
    var conditionsToApprove = document.getElementById('conditions_to_approve[terms-and-conditions]');
	var cardForm = $('#card_pagbank');
	var bankslipForm = $('#bankslip_pagbank');
	var pixForm = $('#pix_pagbank');
	var walletForm = $('#wallet_pagbank');
	var googleForm = $('#google_pagbank');
	var pagbankModule = false;
	var payTwoCard = document.getElementById('pay_two_card');
	if (checkBootStrapFive()) {
		document.querySelectorAll('.pagbank_form').forEach(e => e.classList.add('bootstrap_5'));
		document.querySelectorAll('img[src*="pagbank-logo-animado_35px.gif"]').forEach(img => img.classList.add('pagbank-logo-b5'));
	}
	if (conditionsToApprove != null) {
		conditionsToApprove.addEventListener('change', function() {
			if (this.checked) {
				payOptions.forEach((option) => {
					if (option.dataset.moduleName === "pagbank" && option.checked) {
						pagbankModule = true;
					}
				});
				if (pagbankModule) {
					if (cardForm.is(":visible")) {
						if (checkTwoOpt) {
							var checkCardOne = psValidateCard(false, true);
							var checkCardTwo = psValidateCard(true, true);
							if (!checkCardOne || !checkCardTwo) {
								this.checked = false;
							}
						} else {
							if (!psValidateCard(false, true)) {
								this.checked = false;
							}
						}
					}
					if (bankslipForm.is(":visible")) {
						if (!psValidateBankslip(true)) {
							this.checked = false;
						}
					}
					if (pixForm.is(":visible")) {
						if (!psValidatePix(true)) {
							this.checked = false;
						}
					}
					if (walletForm.is(":visible")) {
						if (!psValidateWallet(true)) {
							this.checked = false;
						}
					}
					if (googleForm.is(":visible")) {
						if (!psValidateGoogle(true)) {
							this.checked = false;
						}
					}
				}
			}
		});
	} else {
		payOptions.forEach((option) => {
			if (option.dataset.moduleName === "pagbank" && option.checked) {
				pagbankModule = true;
			}
		});
		if (pagbankModule) {
			if (cardForm.is(":visible")) {
				if (checkTwoOpt) {
					psValidateCard(false, true);
					psValidateCard(true, true);
				} else {
					psValidateCard(false, true);
				}
			}
			if (bankslipForm.is(":visible")) {
				psValidateBankslip(true);
			}
			if (pixForm.is(":visible")) {
				psValidatePix(true);
			}
			if (walletForm.is(":visible")) {
				psValidateWallet(true);
			}
			if (googleForm.is(":visible")) {
				psValidateGoogle(true);
			}
		}
	}

	var orderValueFieldPagbank = document.getElementById('order_value_pagbank');
	if (orderValueFieldPagbank != null) {
		orderValueFieldPagbank.addEventListener('change', function() {
			if (parseFloat(pgb_ps_version) < 1.7) {
				window.location.reload();
			}
		});
	}
	var installmentsQtyField = document.getElementById('card_installment_qty');
	if (installmentsQtyField != null) {
		installmentsQtyField.addEventListener('change', function(e) {
			psSetInstallment('card_installment_qty');
		});
	}
	var installmentsQtyFieldTwo = document.getElementById('card_installment_qty_two');
	if (installmentsQtyFieldTwo != null) {
		installmentsQtyFieldTwo.addEventListener('change', function(e) {
			psSetInstallment('card_installment_qty_two');
		});
	}
	var installmentsGoogleQtyField = document.getElementById('google_card_installment_qty');
	if (installmentsGoogleQtyField != null) {
		installmentsGoogleQtyField.addEventListener('change', function(e) {
			psSetInstallment('google_card_installment_qty');
		});
	}
	var submitCardButton = document.getElementById('submitCard');
	if (submitCardButton != null) {
		submitCardButton.addEventListener('click', function (e) {
			e.preventDefault();
			psCardCheckout(e);
		});
	}
	var submitBankSlipButton = document.getElementById('submitBankSlip');
	if (submitBankSlipButton != null) {
		submitBankSlipButton.addEventListener('click', function (e) {
			e.preventDefault();
			psBankslipCheckout(e);
		});
	}
	var submitPixButton = document.getElementById('submitPix');
	if (submitPixButton != null) {
		submitPixButton.addEventListener('click', function (e) {
			e.preventDefault();
			psPixCheckout(e);
		});
	}
	var submitWalletButton = document.getElementById('submitWallet');
	if (submitWalletButton != null) {
		submitWalletButton.addEventListener('click', function (e) {
			e.preventDefault();
			psWalletCheckout(e);
		});
	}
	var submitGoogleButton = document.getElementById('submitGoogle');
	if (submitGoogleButton != null) {
		submitGoogleButton.addEventListener('click', function (e) {
			e.preventDefault();
			psGoogleCheckout(e);
		});
	}
	
	var savedCardToken = document.getElementsByClassName('check_token');
	if (savedCardToken != null) {
		Array.from(savedCardToken).forEach(function(el) {
			el.addEventListener('change', function() {
				checkCardToken();
			});
		});
	}
	
	sendToCard(false, 'mockup_number', '****************');
	sendToCard(false, 'mockup_name', 'TITULAR DO CARTÃO');
	sendToCard(false, 'mockup_expiry_month', '**');
	sendToCard(false, 'mockup_expiry_year', '**');

	var thisNum;
	var cardNumberField = document.getElementById('card_number');
	if(typeof cardNumberField !== 'undefined' && cardNumberField !== null) {
		cardNumberField.addEventListener('blur', function (e) {
			thisNum = this.value.replace(/[^0-9]+/g, '');
			if (thisNum !== '' && thisNum.length >= 13) {
				if (checkTwoOpt) {
					var minInst = Number(pgb_installments_min_value).toMoney(2, ',', '.');
					var cardOneVal = document.getElementById('card_one_input').value;
					if (moneyToCents(cardOneVal) < moneyToCents(pgb_installments_min_value) || !cardOneVal) {
						showError('O valor do cartão 1 não pode ser menor do que R$ ' + minInst, 5, 'pagbank_card_error');
						changeFieldClassName('card_one_input', true);
						changeFieldClassName('card_number', true);
					} else {
						psGetInstallments(thisNum.substring(0,6), false, 1);
						sendToCard(this.id, 'mockup_number');
					}
				} else {
					psGetInstallments(thisNum.substring(0,6));
					sendToCard(this.id, 'mockup_number');
				}
			}
		});
	}

    var saveCardFaq = document.getElementById('save-card-faq');
	if (saveCardFaq != null) {
		$('.fancy-button').fancybox();
	}

	if (payTwoCard) {
		sendToCard(false, 'mockup_number_two', '****************', true);
		sendToCard(false, 'mockup_name_two', 'TITULAR DO CARTÃO', true);
		sendToCard(false, 'mockup_expiry_month_two', '**', true);
		sendToCard(false, 'mockup_expiry_year_two', '**', true);

		var thisNumTwo;
		var cardNumberFieldTwo = document.getElementById('card_number_two');
		if(typeof cardNumberFieldTwo !== 'undefined' && cardNumberFieldTwo !== null) {
			cardNumberFieldTwo.addEventListener('blur', function (e) {
				thisNumTwo = this.value.replace(/[^0-9]+/g, '');
				if (thisNumTwo !== '' && thisNumTwo.length >= 13) {
					var minInst = Number(pgb_installments_min_value).toMoney(2, ',', '.');
					var cardOneVal = document.getElementById('card_one_input').value;
					if (moneyToCents(cardOneVal) < moneyToCents(pgb_installments_min_value) || !cardOneVal) {
						showError('O valor do cartão 1 não pode ser menor do que R$ ' + minInst, 5, 'pagbank_card_error');
						changeFieldClassName('card_one_input', true);
						changeFieldClassName('card_number_two', true);
					} else {
						psGetInstallments(thisNumTwo.substring(0,6), false, 2);
						sendToCard(this.id, 'mockup_number_two', false, true);
					}
				}
			});
		}

		var chooseCard = document.getElementById('choose_card');
		var cardOneTab = document.getElementById('card_one_tab');
		var cardOne = document.getElementById('card_one');
		var cardTwoTab = document.getElementById('card_two_tab');
		var cardTwo = document.getElementById('card_two');
		var discountInfo = document.getElementById('discount_info');
		payTwoCard.addEventListener('change', function() {
			if (this.checked) {
				document.getElementById('pay_two_card_check').value = 1;
				chooseCard.style.display = 'block';
				cardOneTab.classList.add('active');
				cardOne.style.display = 'block';
				cardTwoTab.classList.remove('active');
				cardTwo.style.display = 'none';
				checkTwoOpt = true;
				resetFields(true, true);
				Array.from(document.getElementsByClassName('card_title')).forEach(function(e) {
					e.style.display = 'block';
				});
				if (discountInfo) {
					discountInfo.style.display = 'none';
				}
			} else {
				document.getElementById('pay_two_card_check').value = 0;
				chooseCard.style.display = 'none';
				cardOneTab.classList.add('active');
				cardOne.style.display = 'block';
				cardTwo.style.display = 'none';
				checkTwoOpt = false;
				resetFields(true, true);
				Array.from(document.getElementsByClassName('card_title')).forEach(function(e) {
					e.style.display = 'none';
				});
				if (discountInfo) {
					discountInfo.style.display = 'block';
				}
			}
		});
		cardOneTab.onclick = function(){
			cardOneTab.classList.add('active');
			cardOne.style.display = 'block';
			cardTwoTab.classList.remove('active');
			cardTwo.style.display = 'none';
		}
		cardTwoTab.onclick = function(){
			cardTwoTab.classList.add('active');
			cardTwo.style.display = 'block';
			cardOneTab.classList.remove('active');
			cardOne.style.display = 'none';
		}

		var cardOneField = document.getElementById('card_one_input');
		var cardTwoField = document.getElementById('card_two_input');
		var orderTotalPagbank = parseFloat(orderValueFieldPagbank.value);
		cardOneField.addEventListener('input', function(v) {
			var cardOneValue = cardOneField.value.replace(/[^0-9]/g, "");
			var cardTwoValue = cardTwoField.value.replace(/[^0-9]/g, "");
			if (cardOneValue < 1 || cardOneValue === '') {
				cardOneValue = '';
				cardTwoValue = '';
			} else if ((cardOneValue / 100) == pgb_installments_min_value) {
				cardTwoValue = parseFloat(orderTotalPagbank - (cardOneValue / 100)).toLocaleString('pt-BR', {minimumFractionDigits: 2});
			} else if ((cardOneValue / 100) <= (orderTotalPagbank - pgb_installments_min_value)) {
				cardTwoValue = parseFloat(orderTotalPagbank - (cardOneValue / 100)).toLocaleString('pt-BR', {minimumFractionDigits: 2});
			} else {
				cardOneValue = parseFloat(orderTotalPagbank - pgb_installments_min_value).toLocaleString('pt-BR', {minimumFractionDigits: 2});
				cardTwoValue = Number(pgb_installments_min_value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

			}
			cardOneField.value = cardOneValue.toLocaleString('pt-BR', {minimumFractionDigits: 2});
			cardTwoField.value = cardTwoValue.toLocaleString('pt-BR', {minimumFractionDigits: 2});
			resetFields(true);
		});
	}

	if(typeof pgb_payment_google_pay !== 'undefined' && pgb_payment_google_pay == 1 &&
	typeof pgb_google_merchant_id.length !== 'undefined' && pgb_google_merchant_id.length >= 13){
		getGooglePaymentsClient();
		onGooglePayLoaded();
	}
});

function checkBootStrapFive() {
	if (typeof pgb_ps_version !== 'undefined' && parseFloat(pgb_ps_version) >= 9) {
		var bootstrapVersion = $.fn.modal.Constructor.VERSION;
		if (typeof bootstrapVersion !== 'undefined' && parseFloat(bootstrapVersion) >= 5.2) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function resetSavedCard() {
	if (checkBootStrapFive()) {
		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = '';
		});
	} else {
		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = 'block';
		});
	}
	boxCardExists = document.getElementsByName('check_token').length > 0;
	if (boxCardExists) {
		var selectedCard = document.getElementById('selected_card_token');
		document.getElementById('saved_card').value = 0;
		document.getElementById('card_token_id').value = '';
		if (parseFloat(pgb_ps_version) >= 1.7) {
			Array.prototype.forEach.call(document.querySelectorAll('input[name="check_token"]'), function(radio) {
				radio.checked = false;
			});
		} else {
			var radios = document.querySelectorAll('input[name="check_token"]');
			for (var i = 0; i < radios.length; i++) {
				radios[i].checked = false;
				var span = radios[i].parentNode;
				if (span && span.classList) {
					span.classList.remove('checked');
				}
				var wrapperDiv = span ? span.parentNode : null;
				if (wrapperDiv && wrapperDiv.classList) {
					wrapperDiv.classList.remove('hover');
					wrapperDiv.classList.remove('focus');
				}
			}
		}
		selectedCard.innerHTML = '';
		selectedCard.style.display = 'none';
		document.getElementById('reload_button').style.display = 'none';
	}
}

function resetFields(two = false, clear = false) {
	var clearOpts = '<option value=""> - Digite o número do cartão - </option>';

	document.getElementById('card_brand').value = '';
	document.getElementById('card_bin').value = '';
	document.getElementById('card_installments').value = '';

	sendToCard(false, 'mockup_number', '****************');
	sendToCard(false, 'mockup_name', 'TITULAR DO CARTÃO');
	sendToCard(false, 'mockup_expiry_month', '**');
	sendToCard(false, 'mockup_expiry_year', '**');
	populateCard(false, false);
	resetSavedCard();

	document.getElementById('card_number').value = '';
	document.getElementById('card_month').value = '';
	document.getElementById('card_year').value = '';
	document.getElementById('card_cvv').value = '';
	document.getElementById('card_installment_qty').innerHTML = DOMPurify.sanitize(clearOpts, { SAFE_FOR_JQUERY: true });
	document.getElementById('card_installment_qty').click();

	if (two) {
		document.getElementById('card_brand_two').value = '';
		document.getElementById('card_bin_two').value = '';
		document.getElementById('card_installments_two').value = '';

		sendToCard(false, 'mockup_number_two', '****************', true);
		sendToCard(false, 'mockup_name_two', 'TITULAR DO CARTÃO', true);
		sendToCard(false, 'mockup_expiry_month_two', '**', true);
		sendToCard(false, 'mockup_expiry_year_two', '**', true);
		populateCard(false, true);

		document.getElementById('card_number_two').value = '';
		document.getElementById('card_month_two').value = '';
		document.getElementById('card_year_two').value = '';
		document.getElementById('card_cvv_two').value = '';
		document.getElementById('card_installment_qty_two').innerHTML = DOMPurify.sanitize(clearOpts, { SAFE_FOR_JQUERY: true });
		document.getElementById('card_installment_qty_two').click();

		if (clear) {
			document.getElementById('card_one_input').value = '';
			document.getElementById('card_two_input').value = '';
		}
	}

	if (checkTwoOpt) {
		psValidateCard();
		psValidateCard(true);
	} else {
		psValidateCard();
	}
}

function generateRecaptcha(type){
	return new Promise((resolve, reject) => {
		grecaptcha.enterprise.ready(async () => {
			try {
				var recaptchaToken = await grecaptcha.enterprise.execute(pgb_recaptcha_site_key, {action: 'submit'});
				document.getElementById('recaptcha_' + type).value = recaptchaToken;
				if (pgb_msg_console == 1) {
					console.log(recaptchaToken);
				}
				resolve(recaptchaToken);
			} catch (error) {
				if (pgb_msg_console == 1) {
					console.log('Houve um erro ao gerar o recaptcha.');
					console.error(error);
				}
				reject(error);
			}
		});
	});
}

function getGooglePaymentsClient() {
	if (pgb_google_environment == 1) {
		var googleEnv = 'PRODUCTION';
	} else {
		var googleEnv = 'TEST';
	}
	var paymentsClient = new google.payments.api.PaymentsClient({environment: googleEnv});
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
	var orderValuePagbank = document.getElementById('order_value_pagbank').value;
	var paymentDataRequest = Object.assign({}, baseRequest);
		paymentDataRequest.allowedPaymentMethods = [baseCardPaymentMethod];
		paymentDataRequest.transactionInfo = {
			countryCode: 'BR',
			currencyCode: 'BRL',
			totalPriceStatus: 'FINAL',
			totalPrice: orderValuePagbank
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
	.catch(function(error) {
		if (pgb_msg_console == 1) {
			console.error(error);
		}
	});
}

function addGooglePayButton() {
	var paymentsClient = getGooglePaymentsClient();
	var showBtnGoogle = paymentsClient.createButton({
		buttonColor: 'black',
		buttonType: 'pay',
		buttonRadius: 4,
		buttonLocale: 'pt',
		buttonSizeMode: 'fill',
		onClick: onGooglePaymentButtonClicked
	});
	document.getElementById('show_btn_google').appendChild(showBtnGoogle);
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
	}).catch(function(error){
		if (pgb_msg_console == 1) {
			console.log('Verifique se o Merchant ID está correto.');
			console.error(error);
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
	psGetInstallments(c_b, true);
	document.getElementById('google_card_brand').value = paymentBrand;
	document.getElementById('google_card_bin').value = c_b;
	document.getElementById('google_signature').value = JSON.stringify(signature);
	var selectedCardGoogle = document.getElementById('google_selected_card');
	selectedCardGoogle.innerHTML = DOMPurify.sanitize('<p>Você selecionou o cartão: <br /><b class="text-uppercase">' + paymentBrand + ' - FINAL: ' + paymentLastDigits + '</b></p>', { SAFE_FOR_JQUERY: true });
	selectedCardGoogle.style.display = 'block';
}

function psGetInstallments(cardNumber, googlePay = false, cardTwo = false, byToken = false) {
	isSecondField = '';
	if (checkTwoOpt) {
		if (cardTwo == 1) {
    		var partialValue = document.getElementById('card_one_input').value;
			var orderValuePagbank = partialValue.replace(/[,\s]/g, '');
			isSecondField = '';
		} else {
			var partialValue = document.getElementById('card_two_input').value;
			var orderValuePagbank = partialValue.replace(/[,\s]/g, '');
			isSecondField = '_two';
		}
	} else{
    	var orderValuePagbank = document.getElementById('order_value_pagbank').value;
	}
    var maxInstallments = pgb_max_installments;
    var installmentsMinValue = pgb_installments_min_value;
    var installmentsMinType = pgb_installments_min_type;
	var opts = '<option value=""> - Digite o número do cartão - </option>';
	var params = {
		'action': 'installments',
		'value': orderValuePagbank.replace(/[.,\s]/g, ''),
		'payment_methods': 'credit_card',
		'credit_card_bin': cardNumber,
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
			}
			if (response.error_messages && response.error_messages != false || Array.isArray(response.error_messages)) {
				if (!byToken) {
					changeFieldClassName('card_number' + isSecondField, true);
				}
				error_msg = 'Cartão inválido! Por favor, informe outro cartão.';
				showError('<p>'+ error_msg +'</p>', 10, 'pagbank_card_error');
				showLoading('hide');
				if (!cardTwo) {
					resetFields();
				} else {
					resetFields(true);
				}
			} else {
				var cardObject = response.payment_methods.credit_card;
				var cardBrand = Object.keys(cardObject)[0];
				var installments = cardObject[cardBrand].installment_plans;
				if (!googlePay) {
					if (isSecondField) {
						populateCard(cardBrand, true);
					} else {
						populateCard(cardBrand);
					}
					document.getElementById('card_bin' + isSecondField).value = cardNumber;
				}
				opts = '<option value=""> - Selecione a parcela - </option>';
				installments.forEach((parc) => {
					var optionQty = parc.installments;
					var optionValue = Number(parc.installment_value/100);
					var optionTotal = Number(parc.amount.value/100);
					var strInterest = '';
					if (parc.interest_free) {
						strInterest = ' (sem juros)';
					} else {
						strInterest = '';
					}
					var optionLabel = (optionQty + ' x de ' + formatMoney(optionValue) + strInterest + ' — Total: ' + formatMoney(optionTotal));
					if (!checkTwoOpt) {
						if (pgb_discount_value > 0) {
							var infoDiscount = '';
							if (pgb_discount_type == 1) {
								infoDiscount = ' (-' + pgb_discount_value + '%)';
							} else if (pgb_discount_type == 2) {
								infoDiscount = ' (-' + formatMoney(pgb_discount_value) + ')';
							}
							if (!googlePay && optionQty == 1 && pgb_discount_type > 0 && pgb_credit_card_value > 0 && pgb_discount_card == 1) {
								optionLabel = (optionQty + ' x de ' + formatMoney(optionValue) + strInterest + ' — Total: ' + formatMoney(pgb_credit_card_value) + infoDiscount);
							} else if (googlePay && optionQty == 1 && pgb_discount_type > 0 && pgb_google_pay_value > 0 && pgb_discount_google == 1) {
								optionLabel = (optionQty + ' x de ' + formatMoney(optionValue) + strInterest + ' — Total: ' + formatMoney(pgb_google_pay_value) + infoDiscount);
							}
						}
					}
					if (installmentsMinValue == 0) {
						opts += '<option value="' + optionQty + '">' + optionLabel + '</option>';
					}else if(installmentsMinValue >= 1 && installmentsMinType == 0){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue) {
								//
							}else{
								opts += '<option value="' + optionQty + '">' + optionLabel + '</option>';
							}
						}
					}else if(installmentsMinValue >= 1 && installmentsMinType == 1){
						if (optionQty <= maxInstallments) {
							if (installmentsMinValue > optionValue || (typeof pgb_two_card_inst !== 'undefined' && pgb_two_card_inst == 0 && cardTwo == 2)) {
								if (optionQty == 1) {
									opts += '<option value="' + optionQty + '">' + optionLabel + '</option>';
								}
							}else{
								opts += '<option value="' + optionQty + '">' + optionLabel + '</option>';
							}
						}
					}
				});
				if (googlePay){
					document.getElementById('google_card_installment_qty').innerHTML = DOMPurify.sanitize(opts, { SAFE_FOR_JQUERY: true });
					document.getElementById('google_card_installment_qty').click();
				} else {
					document.getElementById('card_installment_qty' + isSecondField).innerHTML = DOMPurify.sanitize(opts, { SAFE_FOR_JQUERY: true });
					document.getElementById('card_installment_qty' + isSecondField).click();
					changeFieldClassName('card_number' + isSecondField, false);
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

function psKeydown() {
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

async function processSubmit(type, two) {
	var submitPagbank = '';
	var recaptcha = false;

	psKeydown();
	showLoading();

	if (typeof pgb_recaptcha !== 'undefined' && pgb_recaptcha == 1 && 
		typeof pgb_recaptcha_site_key !== 'undefined' && pgb_recaptcha_site_key.length >= 40) {
		var recaptcha = true;
	}

	if (type === 'credit_card') {
		var submitPagbank = document.getElementById('card_pagbank');
		if (two) {
			getEncryptedCard(false);
			getEncryptedCard(true);
			if (recaptcha) {
				await generateRecaptcha(type);
			}
			submitPagbank.submit();
		} else {
			getEncryptedCard(false);
			if (recaptcha) {
				await generateRecaptcha(type);
			}
			submitPagbank.submit();
		}
	} else {
		if (type === 'bankslip') {
			var submitPagbank = document.getElementById('bankslip_pagbank');
		} else if (type === 'pix'){
			var submitPagbank = document.getElementById('pix_pagbank');
		} else if (type === 'wallet'){
			var submitPagbank = document.getElementById('wallet_pagbank');
		} else if (type === 'google_pay'){
			var submitPagbank = document.getElementById('google_pagbank');
		}
		if (recaptcha) {
			await generateRecaptcha(type);
			submitPagbank.submit();
		} else {
			submitPagbank.submit();
		}
	}
}

function psCardCheckout(e) {
	e.preventDefault();
	if (checkTwoOpt) {
		var checkCardOne = psValidateCard(false, true);
		var checkCardTwo = psValidateCard(true, true);
		if (checkCardOne && checkCardTwo) {
			processSubmit('credit_card', true);
		}else{
			return false;
		}
	} else {
		if (psValidateCard(false, true)) {
			processSubmit('credit_card', false);
		}else{
			return false;
		}
	}
}

function psBankslipCheckout(e) {
	e.preventDefault();
    if (psValidateBankslip(true)) {
		processSubmit('bankslip', false);
    }else{
		return false;
	}
}

function psPixCheckout(e) {
	e.preventDefault();
	if (psValidatePix(true)){
		processSubmit('pix', false);
    }else{
		return false;
	}
}

function psWalletCheckout(e) {
	e.preventDefault();
	if (psValidateWallet(true)){
		processSubmit('wallet', false);
    }else{
		return false;
	}
}

function psGoogleCheckout(e) {
	e.preventDefault();
	if (psValidateGoogle(true)){
		processSubmit("google_pay", false);
    }else{
		return false;
	}
}

function psSetInstallment(id) {
    var sel = document.getElementById(id);
	var selName = document.getElementById(id).name;
	var option = sel.options[sel.selectedIndex];
	if (option.value > 0) {
		if (selName === 'card_installment_qty') {
			document.getElementById('card_installments').value = option.value;
		} else if (selName === 'card_installment_qty_two') {
			document.getElementById('card_installments_two').value = option.value;
		} else if (selName === 'google_card_installment_qty') {
			document.getElementById('google_installments').value = option.value;
		}
	}
}

function psValidateCard(dualPay, show) {
	var cardTokenId = document.getElementById('card_token_id');
	var html = '';
	var errorFields = [];
	var okFields = [];
	var addressError = false;
	var isSecondField = dualPay ? '_two' : '';

	if (checkTwoOpt) {
		var minInst = Number(pgb_installments_min_value).toMoney(2, ',', '.');
		var cardOneInput = document.getElementById('card_one_input').value;
		if (moneyToCents(cardOneInput) < moneyToCents(pgb_installments_min_value) || !cardOneInput) {
			html += 'O valor do cartão 1 não pode ser menor do que R$ ' + minInst + '. <br />';
			errorFields.push('card_one_input');
		} else {
			okFields.push('card_one_input');
		}
		var cardTwoInput = document.getElementById('card_two_input').value;
		var cardTwoTab = document.getElementById('card_two_tab');
		cardTwoTab.classList.add('btn_tab_alert');
		if (moneyToCents(cardTwoInput) < moneyToCents(pgb_installments_min_value) || !cardTwoInput) {
			html += 'O valor do cartão 2 não pode ser menor do que R$ ' + minInst + '. <br />';
			errorFields.push('card_two_input');
		} else {
			okFields.push('card_two_input');
		}
	}

	var cardName = document.getElementById('card_name' + isSecondField).value.trim();
	if (cardName.length == 0) {
		html += 'Titular do Cartão não preenchido. <br />';
		errorFields.push('card_name' + isSecondField);
	} else if (cardName.length < 3) {
        html += 'Titular do Cartão é Inválido. <br />';
		errorFields.push('card_name' + isSecondField);
	} else {
		okFields.push('card_name' + isSecondField);
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

	var cardDoc = document.getElementById('card_doc' + isSecondField).value;
	if (cardDoc.length == 0) {
		html += 'CPF/CNPJ não preenchido. <br />';
		errorFields.push('card_doc' + isSecondField);
	} else if (!verifyDoc('card_doc' + isSecondField)) {
		html += 'CPF/CNPJ é inválido. <br />';
		errorFields.push('card_doc' + isSecondField);
	} else {
		okFields.push('card_doc' + isSecondField);
	}

	if (cardTokenId.value > 0 ) {
		if (pgb_msg_console == 1) {
			console.log('cartão tokenizado.');
		}
	}

	if (cardTokenId.value == 0 || dualPay) {	
		var cardNumber = document.getElementById('card_number' + isSecondField).value.replace(/[^0-9]/g, '');
		if (cardNumber.length < 13) {
			html += 'Número do Cartão não preenchido. <br />';
			errorFields.push('card_number' + isSecondField);
		} else {
			okFields.push('card_number' + isSecondField);
		}

		var expMonth = document.getElementById('card_month' + isSecondField).value.replace(/[^0-9]/g, '');
		if (expMonth.length < 1) {
			html += 'Mês do Vencimento do Cartão não preenchido. <br />';
			errorFields.push('card_month' + isSecondField);
		} else {
			okFields.push('card_month' + isSecondField);
		}

		var expYear = document.getElementById('card_year' + isSecondField).value.replace(/[^0-9]/g, '');
		if (expYear.length < 1) {
			html += 'Ano do Vencimento do Cartão não preenchido. <br />';
			errorFields.push('card_year' + isSecondField);
		} else {
			okFields.push('card_year' + isSecondField);
		}

		var brand = document.getElementById('card_brand' + isSecondField).value.trim().toLowerCase();
		var cvv = document.getElementById('card_cvv' + isSecondField).value.replace(/[^0-9]/g, '');
		if (cvv.length < 3) {
			html += 'Código de Segurança do Cartão não preenchido. <br />';
			errorFields.push('card_cvv' + isSecondField);
		} else if (!checkCVV(brand, isSecondField)) {
			html += 'Código de Segurança do Cartão inválido! Por favor, verifique. <br />';
			errorFields.push('card_cvv' + isSecondField);
		} else {
			okFields.push('card_cvv' + isSecondField);
		}
	}

	var installmentsQty = document.getElementById('card_installment_qty' + isSecondField).value.replace(/[^0-9]/g, '');
	if (installmentsQty.length == 0 || parseInt(installmentsQty) < 1) {
		html += 'Quantidade de Parcelas não preenchida. <br />';
		errorFields.push('card_installment_qty' + isSecondField);
	} else {
		okFields.push('card_installment_qty' + isSecondField);
	}

	var invoiceAddress = document.getElementById('card_address_invoice').value.trim();
	if (invoiceAddress.length == 0) {
		html += 'Endereço de Cobrança não preenchido. <br />';
		errorFields.push('card_address_invoice');
		addressError = true;
	} else {
		okFields.push('card_address_invoice');
	}

	var postcodeNumber = document.getElementById('card_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('card_postcode_invoice');
		addressError = true;
	} else {
		okFields.push('card_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('card_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('card_number_invoice');
		addressError = true;
	} else {
		okFields.push('card_number_invoice');
	}

	var invoiceDistrict = document.getElementById('card_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('card_address2_invoice');
		addressError = true;
	} else {
		okFields.push('card_address2_invoice');
	}

	var invoiceCity = document.getElementById('card_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('card_city_invoice');
		addressError = true;
	} else {
		okFields.push('card_city_invoice');
	}

	var invoiceState = document.getElementById('card_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('card_state_invoice');
		addressError = true;
	} else {
		okFields.push('card_state_invoice');
	}

	if (typeof errorFields !== 'undefined' && errorFields.length > 0) {
		var checkErrorTwo = false;
		for (var i = 0; i < errorFields.length; i++) {
			if (pgb_msg_console == 1) {
				console.log(errorFields[i]);
			}
			if (errorFields[i].includes(isSecondField)) {
				checkErrorTwo = true;
			}
			changeFieldClassName(errorFields[i], true);
		}
	}

	if (typeof okFields !== 'undefined' && okFields.length > 0) {
		for (var i = 0; i < okFields.length; i++) {
			changeFieldClassName(okFields[i], false);
		}
	}

	if (html.length > 0) {
		if(dualPay) {
			if(!checkErrorTwo) {
				cardTwoTab.classList.remove('btn_tab_alert');
			}
		}
		if (show) {
			showError(html, 5, 'pagbank_card_error');
		}
		if (addressError) {
			$('#card_address').collapse('show');
		}
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(false);
		}
		return false;
	} else {
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(true);
		}
		return true;
	}
}

function psValidateBankslip(show) {
    var html = '';
    var errorFields = [];
	var okFields = [];
    var addressError = false;

    var bankslipName = document.getElementById('bankslip_name').value.trim();
    if (bankslipName.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('bankslip_name');
	} else if (bankslipName.length < 3) {
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

    var bankslipDoc = document.getElementById('bankslip_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (bankslipDoc.length == 0) {
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
		addressError = true;
	} else {
		okFields.push('bankslip_address_invoice');
	}

	var postcodeNumber = document.getElementById('bankslip_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('bankslip_postcode_invoice');
		addressError = true;
	} else {
		okFields.push('bankslip_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('bankslip_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('bankslip_number_invoice');
		addressError = true;
	} else {
		okFields.push('bankslip_number_invoice');
	}

	var invoiceDistrict = document.getElementById('bankslip_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('bankslip_address2_invoice');
		addressError = true;
	} else {
		okFields.push('bankslip_address2_invoice');
	}

	var invoiceCity = document.getElementById('bankslip_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('bankslip_city_invoice');
		addressError = true;
	} else {
		okFields.push('bankslip_city_invoice');
	}

	var invoiceState = document.getElementById('bankslip_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('bankslip_state_invoice');
		addressError = true;
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
			changeFieldClassName(okFields[i], false);
		}
	}

    if (html.length > 0) {
		if(show) {
        	showError(html, 5, 'pagbank_bankslip_error');
		}
		if (addressError) {
			$('#bankslip_address').collapse('show');
		}
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(false);
		}
        return false;
    } else {
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(true);
		}
        return true;
    }
}

function psValidatePix(show) {
    var html = '';
    var errorFields = [];
	var okFields = [];
	var pixAddressError = false;

    var pixName = document.getElementById('pix_name').value.trim();
    if (pixName.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('pix_name');
    } else if (pixName.length < 3) {
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

    var pixDoc = document.getElementById('pix_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (pixDoc.length == 0) {
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
		pixAddressError = true;
	} else {
		okFields.push('pix_address_invoice');
	}

	var postcodeNumber = document.getElementById('pix_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('pix_postcode_invoice');
		pixAddressError = true;
	} else {
		okFields.push('pix_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('pix_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('pix_number_invoice');
		pixAddressError = true;
	} else {
		okFields.push('pix_number_invoice');
	}

	var invoiceDistrict = document.getElementById('pix_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('pix_address2_invoice');
		pixAddressError = true;
	} else {
		okFields.push('pix_address2_invoice');
	}

	var invoiceCity = document.getElementById('pix_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('pix_city_invoice');
		pixAddressError = true;
	} else {
		okFields.push('pix_city_invoice');
	}

	var invoiceState = document.getElementById('pix_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('pix_state_invoice');
		pixAddressError = true;
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
			changeFieldClassName(okFields[i], false);
		}
	}

    if (html.length > 0) {
		if(show) {
        	showError(html, 5, 'pagbank_pix_error');
		}
		if (pixAddressError) {
			$('#pix_address').collapse('show');
		}
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(false);
		}
        return false;
    } else {
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(true);
		}
        return true;
    }
}

function psValidateWallet(show) {
    var html = '';
    var errorFields = [];
	var okFields = [];
	var walletAddressError = false;

    var walletName = document.getElementById('wallet_name').value.trim();
    if (walletName.length == 0) {
        html += 'Nome/Razão Social é obrigatório. <br />';
		errorFields.push('wallet_name');
    } else if (walletName.length < 3) {
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

    var walletDoc = document.getElementById('wallet_doc').value.replace(/[^A-Za-z0-9]/g, '');
    if (walletDoc.length == 0) {
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
		walletAddressError = true;
	} else {
		okFields.push('wallet_address_invoice');
	}

	var postcodeNumber = document.getElementById('wallet_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('wallet_postcode_invoice');
		walletAddressError = true;
	} else {
		okFields.push('wallet_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('wallet_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('wallet_number_invoice');
		walletAddressError = true;
	} else {
		okFields.push('wallet_number_invoice');
	}

	var invoiceDistrict = document.getElementById('wallet_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('wallet_address2_invoice');
		walletAddressError = true;
	} else {
		okFields.push('wallet_address2_invoice');
	}

	var invoiceCity = document.getElementById('wallet_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('wallet_city_invoice');
		walletAddressError = true;
	} else {
		okFields.push('wallet_city_invoice');
	}

	var invoiceState = document.getElementById('wallet_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('wallet_state_invoice');
		walletAddressError = true;
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
			changeFieldClassName(okFields[i], false);
		}
	}

    if (html.length > 0) {
		if(show) {
        	showError(html, 5, 'pagbank_wallet_error');
		}
		if (walletAddressError) {
			$('#wallet_address').collapse('show');
		}
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(false);
		}
        return false;
    } else {
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(true);
		}
        return true;
    }
}

function psValidateGoogle(show) {
	var html = '';
	var errorFields = [];
	var okFields = [];
	var addressError = false;

	var googleName = document.getElementById('google_name').value.trim();
	if (googleName.length == 0) {
		html += 'Titular do Cartão não preenchido. <br />';
		errorFields.push('google_name');
    } else if (googleName.length < 3) {
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

	var googleDoc = document.getElementById('google_doc').value.replace(/[^A-Za-z0-9]/g, '');
	if (googleDoc.length == 0) {
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
		addressError = true;
	} else {
		okFields.push('google_address_invoice');
	}

	var postcodeNumber = document.getElementById('google_postcode_invoice').value.trim();
	if (postcodeNumber.length < 7) {
		html += 'CEP não preenchido ou inválido. <br />';
		errorFields.push('google_postcode_invoice');
		addressError = true;
	} else {
		okFields.push('google_postcode_invoice');
	}

	var invoiceNumber = document.getElementById('google_number_invoice').value.trim();
	if (invoiceNumber.length == 0) {
		html += 'Número do Endereço não preenchido. <br />';
		errorFields.push('google_number_invoice');
		addressError = true;
	} else {
		okFields.push('google_number_invoice');
	}

	var invoiceDistrict = document.getElementById('google_address2_invoice').value.trim();
	if (invoiceDistrict.length == 0) {
		html += 'Bairro do Endereço não preenchido. <br />';
		errorFields.push('google_address2_invoice');
		addressError = true;
	} else {
		okFields.push('google_address2_invoice');
	}

	var invoiceCity = document.getElementById('google_city_invoice').value.trim();
	if (invoiceCity.length == 0) {
		html += 'Cidade do Endereço não preenchido. <br />';
		errorFields.push('google_city_invoice');
		addressError = true;
	} else {
		okFields.push('google_city_invoice');
	}

	var invoiceState = document.getElementById('google_state_invoice').value;
	if (invoiceState.length == 0) {
		html += 'Estado do Endereço não preenchido. <br />';
		errorFields.push('google_state_invoice');
		addressError = true;
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
			changeFieldClassName(okFields[i], false);
		}
	}

	if (html.length > 0) {
		if(show) {
			showError(html, 5, 'pagbank_google_error');
		}
		if (addressError) {
			$('#google_address').collapse('show');
		}
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(false);
		}
		return false;
	} else {
		if (parseFloat(pgb_ps_version) >= 1.7) {
			checkTos(true);
		}
		return true;
	}
}

var formatMoney = function (value) {
    var valueAsNumber = Number(value);
    return 'R$ ' + valueAsNumber.toMoney(2, ',', '.');
};

Number.prototype.toMoney = function (decimals, decimalSep, thousandsSep) {
    var n = this,
    c = isNaN(decimals) ? 2 : Math.abs(decimals),
    d = decimalSep || '.',
    t = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep,
    sign = (n < 0) ? '-' : '',
    i = parseInt(n = Math.abs(n).toFixed(c)) + '',
    j = ((j = i.length) > 3) ? j % 3 : 0;
    return sign + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
};

function moneyToCents(value) {
  return Math.round(
    Number(
      value
        .toString()
        .replace(/[^\d,.-]/g, '')
        .replace(/\./g, '') 
        .replace(',', '.')
    ) * 100
  );
}

function validateCPF(id) {
    var cpfField = document.getElementById(id);
    var cpf = cpfField.value;
    var error = 0;
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
    if (cpf.length !== 11 || 
		cpf === "00000000000" || 
		cpf === "11111111111" || 
		cpf === "22222222222" || 
		cpf === "33333333333" || 
		cpf === "44444444444" || 
		cpf === "55555555555" || 
		cpf === "66666666666" || 
		cpf === "77777777777" || 
		cpf === "88888888888" || 
		cpf === "99999999999") {
        error = 1;
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
        error = 1;
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
        error = 1;
    }
    if (error == 0) {
		changeFieldClassName(id, false);
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
					changeFieldClassName(id, false);
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

function checkCVV(cardBrand, isSecondField) {
    "use strict";

	if (isSecondField) {
		var cvvField = document.getElementById('card_cvv_two');
	} else {
		var cvvField = document.getElementById('card_cvv');
	}

	var brand;
    if (cardBrand && cardBrand != 'undefined') {
        brand = cardBrand.toLowerCase();
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
			changeFieldClassName('card_cvv', false);
            return true;
        }
    }
}

function validatePhoneNumber(fieldId) {
    "use strict";
	var validAreaCodes = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', 
							'32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49',
							'51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73',
							'74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92',
							'93', '94', '95', '96', '97', '98', '99'];
    var foneField = document.getElementById(fieldId);
    var fone = foneField.value;
    var cardPhone = document.getElementById('card_phone');
    var bankslipPhone = document.getElementById('bankslip_phone');
    var pixPhone = document.getElementById('pix_phone');
	var googlePhone = document.getElementById('google_phone');
	var walletPhone = document.getElementById('wallet_phone');
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

	if (!fone) {
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
        var areaCodeExists = inArray(areaCode, validAreaCodes);
        if (areaCodeExists < 0) {
            if (pgb_msg_console == 1) {
                console.log('DDD não encontrado (' + areaCode + ')');
            }
			changeFieldClassName(fieldId, true);
			showError('DDD não encontrado (' + areaCode + ')', 5, id_error);
            return false;
        } else {
			changeFieldClassName(fieldId, false);
				
			if (cardPhone != null && cardPhone.value != fone) {
				cardPhone.value = fone;
			}
			if (bankslipPhone != null && bankslipPhone.value != fone) {
				bankslipPhone.value = fone;
			}
			if (pixPhone != null && pixPhone.value != fone) {
				pixPhone.value = fone;
			}
			if (googlePhone != null && googlePhone.value != fone) {
				googlePhone.value = fone;
			}
			if (walletPhone != null && walletPhone.value != fone) {
				walletPhone.value = fone;
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
	
	if (!hide || hide === ''){
		if (id == 'installments' || id == 'delete_card') {
			document.getElementById('pagbank_msg').innerHTML = DOMPurify.sanitize('Validando...', { SAFE_FOR_JQUERY: true });
		} else {
			document.getElementById('pagbank_msg').innerHTML = DOMPurify.sanitize('Por favor, aguarde.<br />Processando pagamento...<br /><small>Não feche nem recarregue a página.</small>', { SAFE_FOR_JQUERY: true });
		}
		if (parseFloat(pgb_ps_version) < 1.7) {
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
		document.getElementById('pagbank_process').style.display = 'block';
		document.getElementById('fancy_load').classList.add('loading');
		document.getElementById('fancy_load').style.width = window.innerWidth;
	}else{
		if (parseFloat(pgb_ps_version) < 1.7) {
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
		document.getElementById('pagbank_process').style.display = 'none';
		document.getElementById('fancy_load').classList.remove('loading');
	}
}

function populateCard(brand, isSecondField) {
    "use strict";

	if (isSecondField) {
		isSecondField = '_two';
	} else {
		isSecondField = '';
	}

	if(brand === '' || !brand){
		document.getElementById('credit_icon' + isSecondField).innerHTML = DOMPurify.sanitize('<i class="icon-credit-card material-icons"></i>', { SAFE_FOR_JQUERY: true });
		document.querySelector('#card_container' + isSecondField + ' .mockup_brand' + isSecondField).innerHTML = '';
	} else {
		document.getElementById('card_brand' + isSecondField).value = brand;
		document.getElementById('credit_icon' + isSecondField).innerHTML = DOMPurify.sanitize('<img class="addon-img" src="' + pgb_img_path + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />', { SAFE_FOR_JQUERY: true });
		document.querySelector('#card_container' + isSecondField + ' .mockup_brand' + isSecondField).innerHTML = DOMPurify.sanitize('<img class="addon-img" src="' + pgb_img_path + brand.toLowerCase() + '-mini.png" alt="' + brand + '" />', { SAFE_FOR_JQUERY: true });
	}
}

function toggleCardBack(action, isSecondField) {
    "use strict";

	if (isSecondField) {
		isSecondField = '_two';
	} else {
		isSecondField = '';
	}

    if (action === 'add') {
		document.getElementById('card_container' + isSecondField).classList.add('flip');
		setTimeout(function () {
			document.getElementById('card_container' + isSecondField).classList.remove('flip');
			document.getElementById('card_container' + isSecondField).classList.add('verso');
		}, 200);
        
    } else {
		document.getElementById('card_container' + isSecondField).classList.add('flipback');
		setTimeout(function () {
			document.getElementById('card_container' + isSecondField).classList.remove('flipback', 'verso');
		}, 100);
    }
}

function sendToCard(id, isClass, str, isSecondField) {
    "use strict";
	if (!str || str === '') {
		str = document.getElementById(id).value;
	}

	if (isSecondField) {
		isSecondField = '_two';
	} else {
		isSecondField = '';
	}

    if (str.length > 1) {
		var card_container = document.getElementById('card_container' + isSecondField);
		if (typeof card_container !== 'undefined' && card_container !== null){
			card_container.getElementsByClassName(isClass)[0].innerHTML = DOMPurify.sanitize(str, {SAFE_FOR_JQUERY: true});
			if (isClass === 'mockup_number' || isClass === 'mockup_number_two') {
				document.getElementById('mockup_number_card' + isSecondField).innerHTML = DOMPurify.sanitize(str.replace(/(.{4})/g, '$1 &nbsp;'), {SAFE_FOR_JQUERY: true});
			}
		}
    }
}

function showError(str, t, id) {
    "use strict";
	var controlError = document.getElementById(id);
	if (!controlError) {
		return;
	}

	if (controlError._errorTimeout) {
		clearTimeout(controlError._errorTimeout);
	}

	controlError.innerHTML = DOMPurify.sanitize(str, {SAFE_FOR_JQUERY: true});
	controlError.classList.add('alert', 'alert-danger');
	controlError.style.display = 'block';

	controlError._errorTimeout = setTimeout(function () {
		controlError.innerHTML = '';
		controlError.classList.remove('alert', 'alert-danger');
		controlError.style.display = 'none';
		controlError._errorTimeout = null;
	}, (1000 * t));

	document.getElementById(id).scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
}

function verifyDoc(id) {
    "use strict";
    var cpfCnpj = document.getElementById(id);
    var cardDoc = document.getElementById('card_doc');
    var bankslipDoc = document.getElementById('bankslip_doc');
    var pixDoc = document.getElementById('pix_doc');
	var walletDoc = document.getElementById('wallet_doc');
	var googleDoc = document.getElementById('google_doc');
	var fieldValue = cpfCnpj.value;
    var num = cpfCnpj.value;
	if(cardDoc != null) {
		cardDoc.value = fieldValue;
	}
	if(bankslipDoc != null) {
		bankslipDoc.value = fieldValue;
	}
	if(pixDoc != null) {
		pixDoc.value = fieldValue;
	}
	if(walletDoc != null) {
		walletDoc.value = fieldValue;
	}
	if(googleDoc != null) {
		googleDoc.value = fieldValue;
	}
    if (num.length > 14) {
        mascara(cpfCnpj, cnpjmask);
        if (!validateCNPJ(id)) {
            return false;
        }
        return true;
    } else {
        mascara(cpfCnpj, cpfmask);
        if (!validateCPF(id)) {
            return false;
        }
        return true;
    }
}

function getEncryptedCard(isSecondField) {
	var unindexed_array = $('#card_pagbank').serializeArray();
	var formdata = {};
	var cardData = '';
	$.map(unindexed_array, function(n, i){
		formdata[n.name] = n.value;
	});

	try {
		if (isSecondField) {
			cardData = PagSeguro.encryptCard({
				publicKey: pgb_public_key,
				holder: formdata.card_name_two,
				number: formdata.card_number_two,
				expMonth: formdata.card_month_two,
				expYear: formdata.card_year_two,
				securityCode: formdata.card_cvv_two
			});
			formdata = {};
			document.getElementById('encrypted_card_two').value = cardData.encryptedCard;
			if (pgb_msg_console == 1) {
				console.log(cardData.encryptedCard);
			}
		} else {
			cardData = PagSeguro.encryptCard({
				publicKey: pgb_public_key,
				holder: formdata.card_name,
				number: formdata.card_number,
				expMonth: formdata.card_month,
				expYear: formdata.card_year,
				securityCode: formdata.card_cvv
			});
			formdata = {};
			document.getElementById('encrypted_card').value = cardData.encryptedCard;
			if (pgb_msg_console == 1) {
				console.log(cardData.encryptedCard);
			}
		}
	} catch (error) {
		if (pgb_msg_console == 1) {
			console.log('Houve um erro ao gerar a criptografia.');
			console.error(error);
		}
	}
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
			if (id) {
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
				window.location.reload();
			} else {
				var respString = 'Houve um erro ao apagar o seu cartão. Por favor, tente novamente.';
				var pagbankMsg = document.getElementById('pagbank_msg');
				pagbankMsg.innerHTML = DOMPurify.sanitize(respString, { SAFE_FOR_JQUERY: true });
				ret = false;
				window.onbeforeunload = null;
				setTimeout(function () {
					window.location.reload();
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

function checkCardToken() {
	var savedCards = document.getElementsByClassName('check_token');
	var selectedCard = document.getElementById('selected_card_token');
	var cardTokenId = document.getElementById('card_token_id');
	var checkedEl = null;

	Array.from(savedCards).forEach(function(item) {
		if (item.checked) {
			checkedEl = item;
			item.parentElement.classList.add('checked');
		} else {
			item.parentElement.classList.remove('checked');
		}
	});

	if (checkedEl) {
		var cardName = checkedEl.dataset.name;
		var cardBrand = checkedEl.dataset.brand;
		var cardLastDigits = checkedEl.dataset.lastdigits;
		var cardFirstDigits = checkedEl.dataset.firstdigits;
		var cardMonth = String(checkedEl.dataset.month).padStart(2, '0');
		var cardYear = checkedEl.dataset.year.toString().slice(-2);

		Array.from(document.getElementsByClassName('card_data')).forEach(function(cd) {
			cd.style.display = 'none';
		});
		selectedCard.innerHTML = DOMPurify.sanitize('<p>Você está utilizando o cartão: <br /><b class="text-uppercase">' + cardBrand + '</b> (<b>' + cardFirstDigits + '******' + cardLastDigits + '</b>)</p>', { SAFE_FOR_JQUERY: true });
		sendToCard(false, 'mockup_number', cardFirstDigits + '******' + cardLastDigits);
		sendToCard(false, 'mockup_name', cardName);
		sendToCard(false, 'mockup_expiry_month', cardMonth);
		sendToCard(false, 'mockup_expiry_year', cardYear);
		cardTokenId.value = checkedEl.value;
		document.getElementById('card_name').value = cardName;
		document.getElementById('saved_card').value = 1;

		if (checkTwoOpt) {
			var minInst = Number(pgb_installments_min_value).toMoney(2, ',', '.');
			var cardOneVal = document.getElementById('card_one_input').value;
			if (moneyToCents(cardOneVal) < moneyToCents(pgb_installments_min_value) || !cardOneVal) {
				showError('O valor do cartão 1 não pode ser menor do que R$ ' + minInst, 5, 'pagbank_card_error');
				changeFieldClassName('card_one_input', true);
				resetSavedCard();
			} else {
				psGetInstallments(cardFirstDigits, false, 1, true);
			}
		} else {
			psGetInstallments(cardFirstDigits, false, false, true);
			selectedCard.style.display = 'block';
		}
		document.getElementById('reload_button').style.display = 'block';
	} else {
		resetSavedCard();
	}
}

function deleteCustomerToken(idToken) {
	var confirmation = window.confirm('Tem certeza que deseja apagar este cartão?');
	if (confirmation) {
		sendAjaxCall('deleteToken', {"id_customer_token": idToken}, 'delete_card');
	} else {
		return false;
	}
}

function checkTos(valid) {
    var conditions = document.getElementById('conditions_to_approve[terms-and-conditions]');
    var confirmationButton = document.querySelector("#payment-confirmation button[type='submit']");
    var enabled = false;

    if (valid) {
        enabled = !conditions || conditions.checked;
    } else if (conditions) {
        enabled = conditions.hasAttribute('required') || conditions.checked;
    }

    setTimeout(function() {
        confirmationButton.disabled = !enabled;
        confirmationButton.classList.toggle('disabled', !enabled);
    }, 300);

    return valid && enabled;
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

function changeFieldClassName(field, error) {
	var changeField = document.getElementById(field);
	var parent = changeField.parentElement;
	if (parseFloat(pgb_ps_version) >= 9) {
		var bsVersion = $.fn.modal.Constructor.VERSION;
		var bootstrap5 = false;
		if (typeof bsVersion !== 'undefined') {
			bootstrap5 = parseInt(bsVersion.split('.')[0], 10) >= 5;
		}
		var element = bootstrap5 ? changeField : changeField.parentElement;
		var validClass = bootstrap5 ? 'is-valid' : 'has-success';
		var invalidClass = bootstrap5 ? 'is-invalid' : 'has-danger';
		element.classList.remove(validClass, invalidClass);
		if (error) {
			element.classList.add(invalidClass);
		} else {
			element.classList.add(validClass);
		}
	} else if (parseFloat(pgb_ps_version) >= 1.7) {
		parent.classList.remove('has-success', 'has-danger');
		if (error) {
			parent.classList.add('has-danger');
		} else {
			parent.classList.add('has-success');
		}
	} else {
		var parent = changeField.parentElement;
		if (parent.classList.contains('selector') ||
			parent.classList.contains('input-group')) {
			parent = parent.parentElement;
		}
		parent.classList.remove('form-ok', 'form-error');
		if (error) {
			parent.classList.add('form-error');
		} else {
			parent.classList.add('form-ok');
		}
	}
}
