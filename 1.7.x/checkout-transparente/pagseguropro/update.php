<?php
/*
 * 2019 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
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
if($acao=="app")
{
	$tipo = Tools::getValue('tipo');
	$notificationCode = Tools::getValue('notificationCode');
	$authorization = $pagseguro->getAppAuthorization($notificationCode, strtolower($tipo));
	$authcode = (string)$authorization['resp_xml']->code;
	if (Configuration::updateValue('PAGSEGUROPRO_AUTHCODE_'.strtoupper($tipo), $authcode, false)) {
		//Adiciona Log de Autorização da Aplicação
		$log_message = 'Foi gerada uma nova Autorização de Aplicação para o PagSeguro ('.$tipo.') - Código de Notificação: '.$notificationCode.'';
		PrestaShopLogger::addLog($log_message, 2);
		$appMsg = $pagseguro->l('Aplicação Autorizada com sucesso!');
	}else{
		$appMsg = $pagseguro->l('Erro ao cadastrar autorização!');
	}
	$moduleRedirect = Configuration::get('PAGSEGUROPRO_ADM').'/'.$context->link->getAdminLink("AdminModules", false).'&token='.Tools::getAdminTokenLite("AdminModules").'&configure=pagseguropro&tab_module=payments_gateways&module_name=pagseguropro&app_msg='.$appMsg;
	Tools::p($appMsg);
	echo '<script type="text/javascript">window.opener.location.reload(false);</script>';
}
if($acao=="getAuthorization")
{
	
}
if($acao=="getAppCode")
{
	$appCode = $pagseguro->getAppCode($tipo);
	Tools::p((string)$appCode['resp_xml']->code);
}
