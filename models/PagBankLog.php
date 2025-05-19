<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
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

class PagBankLog extends ObjectModel
{
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'pagbank_logs',
        'primary' => 'id_log',
        'multilang' => false,
        'fields' => array(
            'id_cart'       => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'datetime'      => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'type'          => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'method'        => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'data'          => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'response'      => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'url'           => array('type' => self::TYPE_STRING, 'validate' => 'isUrl'),
            'cron'          => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
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
        $results = $db->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_log . ';');

        return json_decode(json_encode($results));
    }

    public function insert($data)
    {
        if ($data) {
            Db::getInstance()->insert('pagbank_logs', $data);
        }
    }

    /*
	 * Apaga Logs do banco de dados
	 */
    public function delete()
    {
        $id_log = Tools::getValue('id_log');
        $ps_logs_box = Tools::getValue('pagbank_logsBox');
        if (!$id_log && !$ps_logs_box) {
            return;
        }
        if (isset($ps_logs_box) && !is_array($ps_logs_box)) {
            $ps_logs_box = array($ps_logs_box);
        }
        $del_query = '';
        if (isset($id_log) && !empty($id_log)) {
            $del_query = 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_log . ';';
        } else {
            foreach ($ps_logs_box as $id_logbox) {
                $del_query .= 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_logbox . ';';
            }
        }
        if (!Db::getInstance()->execute($del_query)) {
            return false;
        }
        return true;
    }
}
