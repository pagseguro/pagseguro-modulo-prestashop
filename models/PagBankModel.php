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

if (!defined('_PS_VERSION_')) {
	exit;
}

class PagBankModel extends ObjectModel
{
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pagbank',
		'primary' => 'id_pagbank',
		'multilang' => false,
		'fields' => array(
			'id_customer' 			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'cpf_cnpj' 				=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'id_cart' 				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_order' 				=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'reference' 			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'transaction_code' 		=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'buyer_ip'	 			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'status'	 			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'status_description'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'payment_type'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'payment_description'	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'installments'			=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'url' 					=>	array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
			'credential' 			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'token_code' 			=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'date_add'	 			=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' 				=>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		)
	);

	/*
	 * Busca dados do log no Banco de Dados
	 */
	public function getOrderData($id_op, $field)
	{
		$result = Db::getInstance()->getRow('
			SELECT * FROM `' . _DB_PREFIX_ . 'pagbank`
			WHERE `' . $field . '` = "' . $id_op . '"
			ORDER BY `id_pagbank` DESC
		');
		return $result;
	}

	public function insert($data)
	{
		if ($data) {
			Db::getInstance()->insert('pagbank', $data);
		}
	}

	/*
	 * Apaga Logs do banco de dados
	 */
	public function delete()
	{
		$id_pagbank = Tools::getValue('id_pagbank');
		$ps_box = Tools::getValue('pagbankBox');
		if (!$id_pagbank && !$ps_box) {
			return;
		}
		if (isset($ps_box) && !is_array($ps_box)) {
			$ps_box = array($ps_box);
		}
		$del_query = '';
		if (isset($id_pagbank) && !empty($id_pagbank)) {
			$del_query = 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank` WHERE `id_pagbank` = ' . $id_pagbank . ';';
		} else {
			foreach ($ps_box as $id_box) {
				$del_query .= 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank` WHERE `id_pagbank` = ' . $id_box . ';';
			}
		}
		if (!Db::getInstance()->execute($del_query)) {
			return false;
		}
		return true;
	}
}
