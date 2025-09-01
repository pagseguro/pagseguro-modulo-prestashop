<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_9_0($module)
{
    Configuration::updateValue('PAGBANK_GOOGLE_PAY', 0, false);
    Configuration::updateValue('PAGBANK_DISCOUNT_GOOGLE', 0, false);
    Configuration::updateValue('PAGBANK_GOOGLE_MERCHANT_ID', '', false);
    Configuration::updateValue('PAGBANK_GOOGLE_ENVIRONMENT', 0, false);
    Configuration::updateValue('PAGBANK_GOOGLE_ORDER_DEMO', 1, false);
    Configuration::updateValue('PAGBANK_ACCOUNT_ID', '', false);
    Configuration::updateValue('PAGBANK_ACCOUNT_ID_SANDBOX', '', false);
	return true;
}
