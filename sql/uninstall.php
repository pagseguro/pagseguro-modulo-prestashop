<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
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

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pagbank`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pagbank_logs`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pagbank_customer_token`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pagbank_api_credentials`';
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
