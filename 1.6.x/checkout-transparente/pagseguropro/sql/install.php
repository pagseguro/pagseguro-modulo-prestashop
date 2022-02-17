<?php
/*
 * 2011-2022 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' ._DB_PREFIX_. 'pagseguropro` (
		`id_pagseguro`      int(10) NOT NULL AUTO_INCREMENT,
		`id_shop`           int(3) NULL,
		`cod_cliente`       int(10) NULL,
		`cpf_cnpj`	    	varchar(21) NULL,
		`id_cart`           int(10) NULL,
		`id_order`          int(10) NULL,
		`referencia`        varchar(50) NULL,
		`cod_transacao`     varchar(50) NULL,
		`buyer_ip`	    	varchar(20) NULL,
		`status`            int(10) NULL,
		`desc_status`       varchar(40) NULL,
		`pagto`             int(10) NULL,
		`desc_pagto`        varchar(40) NULL,
		`parcelas`          int(2) NULL,
		`url`     	    	varchar(512) NULL,
  		`refund` 			decimal(20,2) NULL DEFAULT 0.00,
		`credencial`        varchar(32) NULL,
		`token_codigo`	    varchar(128) NULL,
		`data_pedido`       datetime NULL,
		`data_atu`	    	datetime NULL,
		PRIMARY KEY(`id_pagseguro`),
		INDEX (id_cart)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pagseguropro_logs` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NULL,
    `datetime` DATETIME NULL,
    `type` varchar(64) NULL,
    `method` varchar(64) NULL,
    `data` MEDIUMTEXT NULL,
    `response` MEDIUMTEXT NULL,
    `url` varchar(128) NULL,
    PRIMARY KEY (`id_log`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
