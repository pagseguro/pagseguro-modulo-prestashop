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

function upgrade_module_1_8_0($module)
{
	$sql_l = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank_logs' AND column_name = 'id_shop' AND table_schema = '"._DB_NAME_."'";
	$logs = Db::getInstance()->getRow($sql_l);
	if (!$logs) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank_logs` ADD `id_shop` int(3) NULL AFTER `id_log`;")){
			return false;
		}
	}
	$sql_ct = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank_customer_token' AND column_name = 'id_shop' AND table_schema = '"._DB_NAME_."'";
	$customer_token = Db::getInstance()->getRow($sql_ct);
	if (!$customer_token) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank_customer_token` ADD `id_shop` int(3) NULL AFTER `id_customer_token`;")){
			return false;
		}
	}
	Configuration::updateValue('PAGBANK_WALLET', '1', false);
	Configuration::updateValue('PAGBANK_WALLET_TIME_LIMIT', 1440, false);
	Configuration::updateValue('PAGBANK_DISCOUNT_WALLET', 0, false);
	return true;
}
