<?php
/**
 * 2018 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente 
 *
 */

$ssl = true;
$useSSL = true;

//define('_PS_MODE_DEV_', true);
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__)."/pagseguropro.php");

// Instancia a classe do módulo
$pagseguro=new PagSeguroPro();

$context = Context::getContext();
$id_lang = $context->language->id;
$cart = $context->cart;
$customer = $context->customer;

$acao = Tools::getValue("acao");
if(!Tools::getIsset("acao")) {
	exit("Ação não definida");
}

//Cria Sessão no PagSeguro
if($acao=="session")
{
	$return = $pagseguro->getSessionId();
	echo $return;
}
