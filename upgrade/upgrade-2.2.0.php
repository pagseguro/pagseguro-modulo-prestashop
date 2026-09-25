<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_0($module)
{
	$sql_inst_two = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank' AND column_name = 'installments_two' AND table_schema = '"._DB_NAME_."'";
	$installments_two = Db::getInstance()->getRow($sql_inst_two);
	if (!$installments_two) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank` ADD `installments_two` int(2) NULL AFTER `installments`;")){
			return false;
		}
	}
	$sql_nsu_two = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank' AND column_name = 'nsu_two' AND table_schema = '"._DB_NAME_."'";
	$nsu_two = Db::getInstance()->getRow($sql_nsu_two);
	if (!$nsu_two) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank` ADD `nsu_two` VARCHAR(32) NULL AFTER `nsu`;")){
			return false;
		}
	}
    Configuration::updateValue('PAGBANK_TWO_CREDIT_CARD', 0, false);
	Configuration::updateValue('PAGBANK_TWO_CREDIT_CARD_INST', 0, false);
    Configuration::updateValue('PAGBANK_RECAPTCHA', 0, false);
    Configuration::updateValue('PAGBANK_RACAPTCHA_CRITERIA', 'MEDIUM', false);
    Configuration::updateValue('PAGBANK_RECAPTCHA_SITE_KEY', '', false);
    Configuration::updateValue('PAGBANK_RECAPTCHA_API_KEY', '', false);
    Configuration::updateValue('PAGBANK_RECAPTCHA_URL', '', false);
	return true;
}
