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

$useSSL = true;
$ssl = true;

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/pagbank.php');

$pagbank = new PagBank();
 
$payload = file_get_contents('php://input');

if (!isset($payload) || empty($payload) || $payload == 'NULL') {
	die('JSON não localizado');
} else {
	$transaction = json_decode($payload);
	$id_cart = $pagbank->getIdCart($transaction->reference_id);

	if (!isset($id_cart) || empty($id_cart)) {
		die('ID do carrinho não localizado');
	}

	$id_order = Order::getOrderByCartId($id_cart);

	if (!isset($id_order) || empty($id_order)) {
		die('ID do pedido não localizado');
	}
}

if ((bool)Configuration::get('PAGBANK_FULL_LOG') !== false) {
	$pagbank->saveLog('success', 'callback', $id_cart, '', (string)$payload, 'Notificação Recebida');
}

$current_status = isset($transaction->charges) ? (string)$transaction->charges[0]->status : false;

if (!$pagbank->updateOrderStatus($id_cart, $current_status, $id_order, date("Y-m-d H:i:s"))) {
	echo "Erro ao atualizar o status do pedido";
} else {
	echo "Status do pedido atualizado com sucesso!";
}
