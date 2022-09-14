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

function upgrade_module_1_5_0($module)
{
    Configuration::updateValue('PAGSEGUROPRO_CARTAO', '1', false);
    Configuration::updateValue('PAGSEGUROPRO_BOLETO', '1', false);
    Configuration::updateValue('PAGSEGUROPRO_TRANSF', '1', false);
	return true;
}

