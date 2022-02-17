<?php
/*
 * 2011-2022 PrestaBR
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

class PagSeguroProLog extends ObjectModel
{
    /**
     * @see ObjectModel::$definition
     */
	public static $definition = array(
		'table' => 'pagseguropro_logs',
		'primary' => 'id_log',
		'multilang' => false,
		'fields' => array(
			'id_cart' =>	array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'datetime' =>	array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'type' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'method' =>	array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
			'url' =>	array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
		)
	);

	/*
	 * Busca dados do log no Banco de Dados
	 */
	public function getLog($id_log)
	{
		if (!$id_log) {
			$id_log = Tools::getValue('id_log');
		}
        $db = Db::getInstance();
		$results = $db->getRow('SELECT * FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_log.';');

		return json_decode(json_encode($results));
	}

    public function insert($data)
    {
        if ($data) {
            Db::getInstance()->insert('pagseguropro_logs', $data);
        }
    }

    /*
	 * Apaga Logs do banco de dados
	 */
    public function delete()
    {
        $id_log = Tools::getValue('id_log');
        $ps_logsBox = Tools::getValue('pagseguropro_logsBox');
        if (!$id_log && !$ps_logsBox) {
            return;
		}
        if (isset($ps_logsBox) && !is_array($ps_logsBox)) {
            $ps_logsBox = array($ps_logsBox);
		}
        $del_query = '';
        if (isset($id_log) && !empty($id_log)) {
            $del_query = 'DELETE FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_log.';';
        }else{
            foreach ($ps_logsBox as $id_logbox)
            {
                $del_query .= 'DELETE FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_logbox.';';
            }
        }
        if (!Db::getInstance()->execute($del_query)) {
            return false;
        }
        return true;
    }

}
