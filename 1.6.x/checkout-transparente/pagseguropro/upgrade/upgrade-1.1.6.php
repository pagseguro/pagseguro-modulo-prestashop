<?php
/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_6($module)
{
    Configuration::updateValue('PAGSEGUROPRO_ESTORNADO', _PS_OS_REFUND_, false);
	return true;
}

