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

function upgrade_module_2_0_0($module)
{
    if (_PS_VERSION_ >= '9.0.0') {
        $module->registerHook('displayDashboardTop');
    }
	$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank_logs' AND column_name = 'url' AND table_schema = '"._DB_NAME_."'";
	$url = Db::getInstance()->getRow($sql);
	if ($url) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank_logs` MODIFY `url` VARCHAR(196);")){
			return false;
		}
	}
	return true;
}
