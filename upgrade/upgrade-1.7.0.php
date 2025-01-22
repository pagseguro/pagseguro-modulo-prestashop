<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto, Pix e super app PagBank
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_7_0($module)
{
    $order_state = new OrderState();
    $order_state->name = array();
    $order_state->module_name = $module->name;
    $order_state->template = array();
    foreach (Language::getLanguages() as $key => $language) {
        $order_state->name[$language['id_lang']] = 'PagBank - Pagamento Autorizado';
    }
    $order_state->send_email = false;
    $order_state->invoice = false;
    $order_state->color = '#6495ED';
    $order_state->unremovable = true;
    $order_state->logable = false;
    $order_state->delivery = false;
    $order_state->hidden = false;
    $current_dir = dirname(__FILE__);
    if ($order_state->add()) {
        @copy(_PS_MODULE_DIR_ . '/' . $module->name . '/logo.gif', _PS_IMG_DIR_ . 'os/' . $order_state->id . '.gif');
    }
    Configuration::updateValue('_PS_OS_PAGBANK_3', $order_state->id);
    Configuration::updateValue('PAGBANK_AUTHORIZED', $order_state->id, false);
    Configuration::updateValue('PAGBANK_PAID', _PS_OS_PAYMENT_, false);
    Configuration::updateValue('PAGBANK_CAPTURE_METHOD', 1, false);
    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."pagbank_api_credentials` SET `credit_tax` = 'De 1x a 12x: 3,50% (sem tarifa), Antecipação: 2.99%', `bankslip_tax`= 'R$ 2.00', `pix_tax` = '0.40%' WHERE `app` = 'D14';");
    Db::getInstance()->execute("UPDATE `"._DB_PREFIX_."pagbank_api_credentials` SET `credit_tax` = 'De 1x a 12x: 2,50% (sem tarifa), Antecipação: 2.99%', `bankslip_tax`= 'R$ 2.00', `pix_tax` = '0.40%' WHERE `app` = 'D30';");
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagbank' AND column_name = 'capture' AND table_schema = '"._DB_NAME_."'";
	$capture = Db::getInstance()->getRow($sql);
	if (!$capture) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagbank` ADD `capture` int(1) NULL AFTER `credential`;")){
			return false;
		}
	}
	return true;
}
