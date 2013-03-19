<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * @since 1.5.2
 */

class PagSeguroOrderStatusTranslation{

    private static $order_status = array(
        'INITIATED' => array(
            'br' => 'Iniciado',
            'en' => 'Initiated'
        ),
        'WAITING_PAYMENT' => array(
            'br' => 'Aguardando pagamento',
            'en' => 'Waiting payment'
        ),
        'IN_ANALYSIS' => array(
            'br' => 'Em análise',
            'en' => 'In analysis'
        ),
        'PAID' => array(
            'br' => 'Paga',
            'en' => 'Paid'
        ),
        'AVAILABLE' => array(
            'br' => 'Disponível',
            'en' => 'Available'
        ),
        'IN_DISPUTE' => array(
            'br' => 'Em disputa',
            'en' => 'In dispute'
        ),
        'REFUNDED' => array(
            'br' => 'Devolvida',
            'en' => 'Refunded'
        ),
        'CANCELLED' => array(
            'br' => 'Cancelada',
            'en' => 'Cancelled'
        )
    );
    
    /**
     * Return current translation for infomed status and language iso code
     * @param string $status
     * @param string $lang_iso_code
     * @return string
     */
    public static function getStatusTranslation($status, $lang_iso_code = 'br'){
        
        if (isset(self::$order_status[$status][$lang_iso_code]))
            return self::$order_status[$status][$lang_iso_code];
        
        // default return in english
        return self::$order_status[$status]['en'];
    }
}

?>
