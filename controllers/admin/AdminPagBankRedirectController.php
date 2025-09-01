<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class AdminPagBankRedirectController extends ModuleAdminController
{
	public function initContent()
	{
		$url  = 'index.php?controller=AdminModules&configure=pagbank&tab_module=payments_gateways&module_name=pagbank';
		$url .= '&token=' . Tools::getAdminTokenLite('AdminModules');
		Tools::redirectAdmin($url);
	}
}
