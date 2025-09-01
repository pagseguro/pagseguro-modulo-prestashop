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

class PagBankNotifyModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        header('Content-Type: application/json; charset=utf-8', true);
    }

	public function initContent()
    {
        $payload = Tools::file_get_contents('php://input');

        if (!isset($payload) || empty($payload) || $payload == 'NULL') {
            die('JSON não localizado.');
        } else {
            $pagbank = new PagBank();
            $transaction = json_decode($payload);
            $transaction_code = $pagbank->getOrderData($transaction->id, 'transaction_code');

            if (!isset($transaction_code) || empty($transaction_code)) {
                die('Transação não localizada.');
            }

            $id_cart = $transaction_code['id_cart'];
            $id_order = $transaction_code['id_order'];
        }

        if ((bool)Configuration::get('PAGBANK_FULL_LOG') !== false) {
            $pagbank->saveLog('success', 'callback', $id_cart, '', (string)$payload, 'Notificação Recebida');
        }

        $current_status = isset($transaction->charges) ? (string)$transaction->charges[0]->status : false;

        if (!$pagbank->updateOrderStatus($current_status, $id_order, date("Y-m-d H:i:s"), true)) {
            die('Status do pedido não atualizado.');
        } else {
            die('Status do pedido atualizado com sucesso!');
        }
	}
}
