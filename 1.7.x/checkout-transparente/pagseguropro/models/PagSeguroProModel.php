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

if (!defined('_PS_VERSION_')) {
    exit;
}

class PagSeguroProModel extends ObjectModel
{
    /**
     * @see ObjectModel::$definition
     */
	public static $definition = array(
		'table' => 'pagseguropro',
		'primary' => 'id_pagseguro',
		'multilang' => false,
		'fields' => array(
			'cod_cliente' 	=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'cpf_cnpj' 		=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'id_cart' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_order' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'referencia' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'cod_transacao' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'buyer_ip'	 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'status'	 	=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'desc_status' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'pagto' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'desc_pagto' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'parcelas' 		=>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'url' 			=>	array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
			'credencial' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'token_codigo' 	=>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),

			'data_pedido' =>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'data_atu' =>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		)
	);

	/*
	 * Busca dados do log no Banco de Dados
	 */
	public function getOrderData($id_op,$field)
	{
		$result =Db::getInstance()->getRow('
			SELECT * FROM `'._DB_PREFIX_.'pagseguropro`
			WHERE `'.$field.'` = "'.$id_op.'"
			ORDER BY `id_pagseguro` DESC
		');
		return $result;
	}
	
    public function insert($data)
    {
        if ($data) {
            Db::getInstance()->insert('pagseguropro', $data);
        }
    }

    /*
	 * Apaga Logs do banco de dados
	 */
    public function delete()
    {
        $id_pagseguro = Tools::getValue('id_pagseguro');
        $ps_Box = Tools::getValue('pagseguroproBox');
        if (!$id_pagseguro && !$ps_Box) {
            return;
		}
        if (isset($ps_Box) && !is_array($ps_Box)) {
            $ps_Box = array($ps_Box);
		}
        $del_query = '';
        if (isset($id_pagseguro) && !empty($id_pagseguro)) {
            $del_query = 'DELETE FROM `'._DB_PREFIX_.'pagseguropro` WHERE `id_pagseguro` = '.$id_pagseguro.';';
        }else{
            foreach ($ps_Box as $id_box)
            {
                $del_query .= 'DELETE FROM `'._DB_PREFIX_.'pagseguropro` WHERE `id_pagseguro` = '.$id_box.';';
            }
        }
        if (!Db::getInstance()->execute($del_query)) {
            return false;
        }
        return true;
    }

}
