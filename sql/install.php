<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author	  2011-2024 PrestaBR - https://prestabr.com.br
 * @copyright 1996-2024 PagBank - https://pagseguro.uol.com.br
 * @license	  Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagbank` (
		`id_pagbank`          int(10) NOT NULL AUTO_INCREMENT,
		`id_shop`               int(3) NULL,
		`id_customer`           int(10) NULL,
		`cpf_cnpj`	    	    varchar(21) NULL,
		`id_cart`               int(10) NULL,
		`id_order`              int(10) NULL,
		`reference`        	    varchar(64) NULL,
		`transaction_code`	    varchar(64) NULL,
		`buyer_ip`	    	    varchar(20) NULL,
		`status`                varchar(64) NULL,
		`status_description`    varchar(128) NULL,
		`payment_type` 		    varchar(64) NULL,
		`payment_description` 	varchar(128) NULL,
		`installments`		    int(2) NULL,
		`url`     	    	    varchar(512) NULL,
		`credential`		    varchar(32) NULL,
		`token_code`		    varchar(128) NULL,
  		`refund` 			    decimal(22,2) NULL DEFAULT 0.00,
		`date_add`		        datetime NULL,
		`date_upd`		        datetime NULL,
		PRIMARY KEY(`id_pagbank`),
		INDEX (id_cart)
	) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagbank_logs` (
    `id_log` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NULL,
    `datetime` DATETIME NULL,
    `type` varchar(64) NULL,
    `method` varchar(64) NULL,
    `data` MEDIUMTEXT NULL,
    `response` MEDIUMTEXT NULL,
    `url` varchar(128) NULL,
    `cron` int(11) NULL DEFAULT 0,
    PRIMARY KEY (`id_log`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagbank_customer_token` (
    `id_customer_token` int(11) NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) NOT NULL,
    `card_name` varchar(128) NOT NULL,
    `card_brand` varchar(32) NOT NULL,
    `card_first_digits` int(6) NOT NULL,
    `card_last_digits` int(4) NOT NULL,
	`card_month` varchar(2) NOT NULL,
	`card_year` int(4) NOT NULL,
    `card_token` varchar(512) NOT NULL,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_customer_token`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagbank_api_credentials` (
    `id_api_credential` int(11) NOT NULL AUTO_INCREMENT,
    `environment` int(1) NOT NULL,
    `app` varchar(16) NOT NULL,
    `credit_tax` varchar(128) NULL,
    `bankslip_tax` varchar(16) NULL,
    `pix_tax` varchar(16) NULL,
    `client_id` varchar(64) NOT NULL,
    `cipher_text` varchar(500) NOT NULL,
    `date_add` DATETIME NOT NULL,
    PRIMARY KEY (`id_api_credential`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'INSERT INTO `' . _DB_PREFIX_ . 'pagbank_api_credentials` (
    `id_api_credential`,
    `environment`,
	`app`,
	`credit_tax`,
	`bankslip_tax`,
	`pix_tax`,
    `client_id`,
    `cipher_text`,
    `date_add`) VALUES 
	(NULL, 1, "TAX", "", "", "", "8c89570d-8c81-4b31-a28b-f3de5149fbe7", "AbpS+wx53NR10p8gs69gEAqx6XOowG3xga1ZvINyDj4BMbo5gG1dFQJvFtwmxxUJ79Ci2anWHBly4b7y+8uP8O1RnPMoNTSCvw/9OCOp9CMVRp3lQ0YoLf0ludERBblLYFjnADfiFNOpIGfabPiDOlIJQMJcDMDGfygNv0b1HL9GhD9rHkO4w4c4EOOCOAoaZS/hnYuBFmtaKBWm2NG6EB5VscvgcNEwXabsMOBXpUa0n5N1yMsN5p35/MUvlA2WQKpNwInXxXJ9aRA76M+ZyS6EquNehOy8/etIO1Vq1QZXOJTvMX9qJZhZ7m8NpabKl4AcTX2VXSDLYXiGDjRJBA==", CURRENT_TIMESTAMP), 
	(NULL, 1, "D14", "À Vista: 3.85% + tarifa de R$ 0.40, Parcelado: 3.49% + tarifa de R$ 0.40, Antecipação: 2.99%", "R$ 2.99", "0.99%", "514bf19a-5f69-4292-b5bf-56aa3cddf7f6", "RwqHn5mnEcPx89jqRFZM7MoHoc00iJhMP5ByEZzctA47RpqMeqMyd14GGiCn8q5+0lc3HXFFXWSnLV+uRh43a1ACehUS/5oCPEnJUokCITnmORKVZr1nLkw1IotpUrY7ejb/jFavAN4YdU8WJSwDzODt8dvvGU/sIxXMKcrCC65AVyybNlLcYXNDvT5/diAp+ZJtKPI1MKLyDp6Sdvf1X8EX7KbuizXfJJMJi8i3Cd9g5GioF7CP7pwgdI7FFyJx4QlC//FTOSFS5/c03SeNr/LZYuVEPVpwZZ4LuEhMdqe75e9YEfkp/1RLXpgBrzwQX11OVOW6HMAchOcIXWOx2Q==", CURRENT_TIMESTAMP), 
	(NULL, 1, "D30", "À Vista: 3.10% + tarifa de R$ 0.40, Parcelado: 2.99% + tarifa de R$ 0.40, Antecipação: 2.99%", "R$ 2.99", "0.99%", "30b9cc3f-40c7-4b28-bb35-41bc3ddc4343", "Zh5S8087xpM6TFAEzEAoSaN6qR+RvUq1WaCcTNf6GUyxKAunrnBW3rooEOgk1i0JfCO+LJ+OCnN82PJBl1B0rbaRmt2UhZrn6bjEF2OibnBON1+0lbaKyvwCECgOf619uJzm9n12t0n67AA0Gc2og13FCX56cVVZK+6RKQ/3rroBjL1v73mYDyKsTsXXB5DDnJED/878XZ0w3W7+FAAN4e2KaHz/dQ3B57t6x1/NrPF9IY4xAVwzwqKlHxfVN9v7zv2xvaAdpN0RuOiAI6lwvGXGAJKBAz/yw/uYPFZZTZVZ5q0aCGd+zD0TjZWQZhRMchMwzYTRPZuGSySXnMoW/A==", CURRENT_TIMESTAMP), 
	(NULL, 0, "TAX", "", "", "", "d637cb2f-fce5-4157-9344-1dd7aebc50c5", "dY72sfj9+p+47D0XZkwcUm8QyJRRQqkdwyDUnQq1uthNu13CwqVBFPM7jdv9UI0cfcn6eobDL/1LNZv+aagCW4+LRtKw/jk3LQlLr7LysleyLyBRSahHOcnZBf3TumhbPtj+wSJRcSE6qsbDo5LCFyiSN8rC0u2BwMx23SrwrYdctbWLjYAEhvXHZbFtkbiWvWyT6EGpSLQrKj51vB58IRDdv8v4DEB9QNnPdPif0lLdIGIbJEoAPJDIPS4jdhWbMuXfLO7g9acZXGOUOLG1PyDSdBiSR3QSxLjnwhBeJNKDBApMULCaUTfJZWjERdID6nUiKx+OD5jw2nBihj4mPA==", CURRENT_TIMESTAMP), 
	(NULL, 0, "D14", "À Vista: 3.85% + tarifa de R$ 0.40, Parcelado: 3.89% + tarifa de R$ 0.40, Antecipação: 2.99%", "R$ 2.99", "0.99%", "c0eeae0b-013f-430c-b0e4-3d34649de037", "MVySoivm6dlbVOlOJV1pAKGNBfIyEUEF/STTHng/QWgOnJ9pw2mVKwcS/iVRgYhiYjUESkwWL16qY26EcmPsjvw+oThv7qNsZIeAY4b4WwU93Ab36X82eWy1c27CwravNVYp04SgODrQhVWenJpII/TExBVUWYaSejW3o5C7eDEZ+BvlRtRW/dLyGlXECMW2AviDZiyr5z87OEXIDbTIro9oL5i5vk6mfA7WFfwbuTHkoVee0+3bJ3pUypijOfrl/ZkEBVWWeaHDOshiTH8al7Xrk6qq0DaVNDDF6bPxHXXaIEfCP+P2NdQjjLqO86DCgdVYqE/Y8sPamo+urU3NMg==", CURRENT_TIMESTAMP), 
	(NULL, 0, "D30", "À Vista: 3.10% + tarifa de R$ 0.40, Parcelado: 2.99% + tarifa de R$ 0.40, Antecipação: 2.99%", "R$ 2.99", "0.99%", "fa76c5ee-1e60-41b6-994c-2f70032559eb", "bRB2XQYL9dh4K3f7XD3bqhHJwzzaLYSJUphD4f9EDmCkm0+rMZ8iPsQmNSuwdlfrYttM/D7rujHFR1w4imW73MKzi7VmupCohRxcrOSApATyXXYzT/vIsgAGhVh2rYd/KsJy/LJnx7+axL8YcWsZJYE3HxBxcqF3+y6xYRw4ewTcNNE/1WbPiJA97J80a/r48OrUXFnm5Klt2C3fGx7lGFanRrfH5JbIu7Y8ymZ9iTTlTwFBiu34A5jv3dE/SeS+aRiMOI7a/t0qN8xdT98w8C0fADsNxxcKBlhCcFtjdBkC7KyZGGCT7LciqpFLDwdfzaxPLPph/hIwCZ6i7UTMZg==", CURRENT_TIMESTAMP);';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
