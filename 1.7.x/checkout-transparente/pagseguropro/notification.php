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

/* SSL Management */
$useSSL = true;
$ssl = true;

//define('_PS_MODE_DEV_', true);
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/pagseguropro.php');

// Instancia a classe do módulo
$pagseguro = new PagSeguroPro();
$context = Context::getContext();
$id_lang = $context->language->id;

// Parâmetros recebidos na notificação    
$type = Tools::getValue('notificationType');
$code = Tools::getValue('notificationCode');

if (!isset($type) || empty($type) || !isset($code) || empty($code)) {
	$pagseguro->saveLog('error', 'callback', NULL, 'Parâmetros ausentes', '');
	die('Parâmetros ausentes');
}
if ((bool)Configuration::get('PAGSEGUROPRO_FULL_LOG') !== false) {
	$pagseguro->saveLog('notificacao', 'callback', NULL, $type, $code);
}
if ($type != 'transaction') {
	$pagseguro->saveLog('error', 'callback', NULL, $type, $code);
	die('Notificação extra');
}

// Pega dados da notificação no PagSeguro
$transaction = $pagseguro->getNotification($code);
//Tools::p($transaction);

$id_cart = (int)$pagseguro->getIdCart($transaction->reference); 
$id_order = Order::getOrderByCartId($id_cart);
$order = new Order($id_order);
//Tools::p($order);

$dados = array(
	'cod_transacao' => (string)$transaction->code,
	'id_cart' => $id_cart, 
	'status' => (int)$transaction->status,
	'desc_status' => $pagseguro->parseStatus((int)$transaction->status),
	'pagto' => (int)$transaction->paymentMethod->type,
	'desc_pagto' => $pagseguro->parseTipoPagamento((int)$transaction->paymentMethod->code),
	'url' => isset($transaction->paymentLink) && $transaction->paymentLink != '' ? (string)$transaction->paymentLink : false,
	'data_atu' => date("Y-m-d H:i:s", strtotime($transaction->lastEventDate))
);
//Tools::p($dados);

//Atualiza Status do pedido 
if (!$pagseguro->updateOrderStatus($id_cart, (int)$transaction->status, (int)$id_order, date("Y-m-d H:i:s", strtotime($transaction->lastEventDate)))){
	$pagseguro->saveLog('error', 'callback', $id_cart, $code, $transaction->status, 'Erro ao atualizar o status do pedido');
	Tools::dieObject('Erro ao atualizar o status do pedido');
}else{
    if (!$pagseguro->updatePagSeguroData($dados)){
    	$pagseguro->saveLog('error', 'callback', $id_cart, $code, json_encode($transaction), 'erro ao atualizar o pedido no banco');
    	Tools::dieObject('Erro ao atualizar o pedido no banco');
    }else{
        Tools::dieObject('Pedido atualizado no banco com sucesso!');
    }
    Tools::dieObject('Status do pedido atualizado com sucesso!');
}
