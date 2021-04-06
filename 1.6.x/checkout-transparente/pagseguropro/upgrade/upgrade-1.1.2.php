<?php
/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_2($module)
{
	$sql_c = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagseguropro' AND column_name = 'credencial' AND table_schema = '"._DB_NAME_."'";
	$credencial = Db::getInstance()->getRow($sql_c);
	if (!$credencial) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagseguropro` ADD `credencial` varchar(32) NULL AFTER `url`;")){
			return false;
		}
	}
	$sql_t = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagseguropro' AND column_name = 'token_codigo' AND table_schema = '"._DB_NAME_."'";
	$token_codigo = Db::getInstance()->getRow($sql_t);
	if (!$token_codigo) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagseguropro` ADD `token_codigo` varchar(128) NULL AFTER `credencial`;")){
			return false;
		}
	}
	$sql_r = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagseguropro' AND column_name = 'referencia' AND table_schema = '"._DB_NAME_."'";
	$referencia = Db::getInstance()->getRow($sql_r);
	if (!$referencia) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagseguropro` ADD `referencia` varchar(128) NULL AFTER `id_order`;")){
			return false;
		}
	}
	$sql_l = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '"._DB_PREFIX_."pagseguropro_logs' AND column_name = 'id_cart' AND table_schema = '"._DB_NAME_."'";
	$idcart = Db::getInstance()->getRow($sql_l);
	if (!$idcart) {
		if (!Db::getInstance()->execute("ALTER TABLE `"._DB_PREFIX_."pagseguropro_logs` ADD `id_cart` int(11) NULL AFTER `id_log`;")){
			return false;
		}
	}
	Configuration::updateValue('PAGSEGUROPRO_ADDRESS_REQUIRED', 1, false);
	return true;
}

