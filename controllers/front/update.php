<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2026 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2026 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

class PagBankUpdateModuleFrontController extends ModuleFrontController
{
	public function init()
    {
		parent::init();
        header('Content-Type: application/json; charset=utf-8', true);
    }

	public function initContent()
    {
		$context = Context::getContext();
		$isLogged = $context->customer->isLogged();
		$cart_id = (int)$context->cart->id;
		$id_lang = (int)$context->language->id;
		$action = Tools::getValue("action");
		$token = Tools::getValue("token");
		$token_ps = $this->module->getTokenHash();
		$token_check = '';
		$token_valid = false;
        if (is_string($token) && $token !== '' && is_string($token_ps) && $token_ps !== '' && hash_equals($token_ps, $token)) {
            $token_valid = true;
        }
		$request_valid = false;
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			$request_valid = true;
		}
		if (Tools::getIsset('state')) {
			$code = Tools::getValue('code');
			$state = urldecode(Tools::getValue('state'));
			$state_array = explode('/', $state);
			$admin_dir = $state_array[0];
			$action = $state_array[1];
			$app = $state_array[2];
			$token_check = $state_array[3];
			$token_session = Configuration::get('PAGBANK_PS_SESSION');
		}
		if (empty($action)) {
			die('Ação não definida.');
		}

		if ($action === 'installments' && $request_valid && $isLogged) {
			$value = Tools::getValue("value");
			$bin = Tools::getValue("credit_card_bin");
			$api_response = $this->module->callGetInstallments($value, $bin, $cart_id);
		} elseif ($action === 'registerUser') {
			if (is_string($token_check) && $token_check !== '' && is_string($token_ps) && $token_ps !== '' && hash_equals($token_ps, $token_check)) {
				if(is_string($code) && $code !== '') {
					$authorization = $this->module->getAppAuthorization($code, $app);
					if ($authorization) {
						$app_msg = 'Usuário cadastrado com sucesso na aplicação ' . $app;
						if (_PS_VERSION_ >= '9.0.0') {
							$module_admin = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . $admin_dir . '/index.php/improve/modules/manage/action/configure/pagbank?tab_module=payments_gateways&token=' . $token_session . '&app_msg=' . $app_msg;
						} else {
							$module_admin = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . $admin_dir . '/index.php?controller=AdminModules&configure=pagbank&tab_module=payments_gateways&module_name=pagbank&token=' . $token_session . '&app_msg=' . $app_msg;
						}
						Tools::redirectAdmin($module_admin);
					} else {
						die('Não registrado.');
					}
				} else {
					die('Código não recebido.');
				}
			} else {
				die('Token Inválido.');
			}
		} elseif ($action === 'deleteToken' && $request_valid && $isLogged) {
			$id_customer_token = Tools::getValue('id_customer_token');
			$api_response = $this->module->deleteCustomerToken($id_customer_token);
		} elseif ($action === 'checkOrder' && $request_valid && $isLogged) {
			$id_order = Tools::getValue('id_order');
			$order = new Order((int)$id_order);
			$api_response = $order->getHistory($id_lang);
		} elseif ($action === 'cancelNotPaidPix' && $token_valid) {
			$awaiting_payment = Configuration::get('PAGBANK_AWAITING_PAYMENT');
			$payment_deadline = Configuration::get('PAGBANK_PIX_TIME_LIMIT');
			$queryStr = 'SELECT `id_order`, `id_cart` FROM `' . _DB_PREFIX_ . 'orders` WHERE `payment` like "PIX%" AND `current_state` = ' . (int)$awaiting_payment . ' AND `date_add` < DATE_SUB(NOW(),INTERVAL ' . $payment_deadline . ' MINUTE)';
			$ordersToCancel = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryStr);

			if (!empty($ordersToCancel)) {
				$cancelStatus = Configuration::get('PAGBANK_CANCELED');
				$history = new OrderHistory();
				foreach ($ordersToCancel as $order) {
					$history->id_order = (int)$order['id_order'];
					$history->changeIdOrderState((int)$cancelStatus, (int)$order['id_order']);
					if ($history->addWithemail(true)) {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' atualizado na loja';
						$this->module->saveLog('success', 'Cancela Pedido Pix Não Pago', $order['id_cart'], '', 'Status do pedido atualizado na loja.', false, 1);
					} else {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' não atualizado na loja';
						$this->module->saveLog('error', 'Cancela Pedido Pix Não Pago', $order['id_cart'], '', 'Status do pedido não atualizado na loja.', false, 1);
					}
				}
			} else {
				$api_response = 'Sem pedidos para cancelar!';
			}
		} elseif ($action === 'cancelNotPaidBankslip' && $token_valid) {
			$awaiting_payment = Configuration::get('PAGBANK_AWAITING_PAYMENT');
			$payment_deadline = Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT');
			$queryStr = 'SELECT `id_order`, `id_cart` FROM `' . _DB_PREFIX_ . 'orders` WHERE `payment` like "BOLETO%" AND `current_state` = ' . (int)$awaiting_payment . ' AND `date_add` < DATE_SUB(NOW(),INTERVAL ' . $payment_deadline . ' DAY) AND DAYOFWEEK(NOW()) NOT IN (1,7)';
			$ordersToCancel = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryStr);

			if (!empty($ordersToCancel)) {
				$cancelStatus = Configuration::get('PAGBANK_CANCELED');
				$history = new OrderHistory();
				foreach ($ordersToCancel as $order) {
					$history->id_order = (int)$order['id_order'];
					$history->changeIdOrderState((int)$cancelStatus, (int)$order['id_order']);
					if ($history->addWithemail(true)) {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' atualizado na loja';
						$this->module->saveLog('success', 'Cancela Pedido Boleto Não Pago', $order['id_cart'], '', 'Status do pedido atualizado na loja.', false, 1);
					} else {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' não atualizado na loja';
						$this->module->saveLog('error', 'Cancela Pedido Boleto Não Pago', $order['id_cart'], '', 'Status do pedido não atualizado na loja.', false, 1);
					}
				}
			} else {
				$api_response = 'Sem pedidos para cancelar!';
			}
		} elseif ($action === 'cancelNotPaidWallet' && $token_valid) {
			$awaiting_payment = Configuration::get('PAGBANK_AWAITING_PAYMENT');
			$payment_deadline = Configuration::get('PAGBANK_WALLET_TIME_LIMIT');
			$queryStr = 'SELECT `id_order`, `id_cart` FROM `' . _DB_PREFIX_ . 'orders` WHERE `payment` = "Pagar com PagBank" AND `current_state` = ' . (int)$awaiting_payment . ' AND `date_add` < DATE_SUB(NOW(),INTERVAL ' . $payment_deadline . ' MINUTE)';
			$ordersToCancel = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryStr);

			if (!empty($ordersToCancel)) {
				$cancelStatus = Configuration::get('PAGBANK_CANCELED');
				$history = new OrderHistory();
				foreach ($ordersToCancel as $order) {
					$history->id_order = (int)$order['id_order'];
					$history->changeIdOrderState((int)$cancelStatus, (int)$order['id_order']);
					if ($history->addWithemail(true)) {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' atualizado na loja';
						$this->module->saveLog('success', 'Cancela Pedido Carteira Digital Não Pago', $order['id_cart'], '', 'Status do pedido atualizado na loja.', false, 1);
					} else {
						$api_response = 'Status do pedido ' . $order['id_order'] . ' não atualizado na loja';
						$this->module->saveLog('error', 'Cancela Pedido Carteira Digital Não Pago', $order['id_cart'], '', 'Status do pedido não atualizado na loja.', false, 1);
					}
				}
			} else {
				$api_response = 'Sem pedidos para cancelar!';
			}
		}

		if (isset($api_response) && $api_response) {
			echo json_encode($api_response, JSON_PRETTY_PRINT);
			exit();
		} else {
			header('Location: ../');
			exit();
		}
	}
}
