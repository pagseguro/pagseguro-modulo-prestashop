<?php

/**
 * @since 1.5.2
 */

class PagSeguroOrderStatusTranslation
{
    private static $order_status = array(
	'INITIATED' => array('br' => 'Iniciado', 'en' => 'Initiated'),
        'WAITING_PAYMENT' => array('br' => 'Aguardando pagamento', 'en' => 'Waiting payment'),
        'IN_ANALYSIS' => array('br' => 'Em análise', 'en' => 'In analysis'),
        'PAID' => array('br' => 'Paga', 'en' => 'Paid'),
        'AVAILABLE' => array('br' => 'Disponível', 'en' => 'Available'),
        'IN_DISPUTE' => array('br' => 'Em disputa', 'en' => 'In dispute'),
        'REFUNDED' => array('br' => 'Devolvida', 'en' => 'Refunded'),
        'CANCELLED' => array('br' => 'Cancelada', 'en' => 'Cancelled'));
    
    /**
     * Return current translation for infomed status and language iso code
     * @param string $status
     * @param string $lang_iso_code
     * @return string
     */
    public static function getStatusTranslation($status, $lang_iso_code = 'br')
    {
        if (isset(self::$order_status[$status][$lang_iso_code]))
            return self::$order_status[$status][$lang_iso_code];
        
	/* Default return in English */
        return self::$order_status[$status]['en'];
    }
}
