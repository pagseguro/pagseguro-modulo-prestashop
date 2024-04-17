<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Pix, Boleto e Cartão de Crédito
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2024 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2024 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_0($module)
{
	$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank' AND column_name = 'environment' AND table_schema = '"._DB_NAME_."'";
	$environment = Db::getInstance()->getRow($sql);
	if (!$environment) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank` ADD `environment` int(1) NULL AFTER `refund`;")){
			return false;
		}
	}
	Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_TAX', '', false);
	return true;
}


