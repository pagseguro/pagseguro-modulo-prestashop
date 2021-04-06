<?php
/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0($module)
{
	$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagseguropro' AND column_name = 'refund' AND table_schema = '"._DB_NAME_."'";
	$refund = Db::getInstance()->getRow($sql);
	if (!$refund) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagseguropro` ADD `refund` decimal(20,2) NULL DEFAULT 0.00 AFTER `url`;")){
			return false;
		}
	}
    Configuration::updateValue('PAGSEGUROPRO_TIPO_PARCELA_MINIMA', '1', false);
	return true;
}

