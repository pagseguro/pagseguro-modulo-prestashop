<?php
/**
 * 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
include_once dirname(__FILE__).'/../../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../../init.php';
include_once dirname(__FILE__).'/../../pagseguro.php';
include_once dirname(__FILE__).'/../../backward_compatibility/backward.php';
include_once dirname(__FILE__).'/../../features/payment/pagseguropaymentorderprestashop.php';
include_once dirname(__FILE__).'/../../Loader.php';

$useSSL = true;

global $options;

$showView = new BWDisplay();

$pagseguro = new PagSeguro();

$context = Context::getContext();

if (! $context->cookie->isLogged(true)) {
    Tools::redirect('authentication.php?back=order.php');
}

function init()
{
    try {
        setOptions(filter_var($_POST['amount']), filter_var($_POST['brand']));

        $installments = \PagSeguro\Services\Installment::create(
            \PagSeguro\Configuration\Configure::getAccountCredentials(),
            getOptions()
        );
        echo Tools::jsonEncode(
            array(
                'success' => true,
                'payload' => [
                    'data' => output($installments->getInstallments(), false)
                ]
            )
        );
        exit;
    } catch (\Exception $exception) {
        throw $exception;
    }
}

/**
 * Getter of the options attribute
 * @return array
 */
function getOptions() {
    global $options;
    return $options;
}
/**
 * Setter the options attribute
 * @param mixed $amount
 * @param string $brand
 */
function setOptions($amount, $brand) {
    global $options;
    $options = [
        'amount' => $amount,
        'card_brand' => $brand
    ];
}

/**
 * Return a formated output of installments
 *
 * @param array $installments
 * @param bool $maxInstallments
 * @return array
 */
function output($installments, $maxInstallment)
{
    return ($maxInstallment) ?
        formatOutput(getMaxInstallment($installments)) :
        formatOutput($installments);
}

/**
 * Format the installment to the be show in the view
 * @param  array $installments
 * @return array
 */
function formatOutput($installments)
{
    $response = getOptions();
    foreach($installments as $installment) {
        $response['installments'][] = formatInstallments($installment);
    }
    return $response;
}

/**
 * Format a installment for output
 *
 * @param $installment
 * @return array
 */
function formatInstallments($installment)
{
    return [
        'quantity' => $installment->getQuantity(),
        'amount' => $installment->getAmount(),
        'totalAmount' => round($installment->getTotalAmount(), 2),
        'text' => str_replace('.', ',', getInstallmentText($installment))
    ];
}

/**
 * Mount the text message of the installment
 * @param  object $installment
 * @return string
 */
function getInstallmentText($installment)
{
    return sprintf(
        "%s x de R$ %.2f %s juros",
        $installment->getQuantity(),
        $installment->getAmount(),
        getInterestFreeText($installment->getInterestFree()));
}

/**
 * Get the string relative to if it is an interest free or not
 * @param string $insterestFree
 * @return string
 */
function getInterestFreeText($insterestFree)
{
    return ($insterestFree == 'true') ? 'sem' : 'com';
}

/**
 * Get the bigger installments list in the installments
 * @param array $installments
 * @return array
 */
function getMaxInstallment($installments)
{
    $final = $current = ['brand' => '', 'start' => 0, 'final' => 0, 'quantity' => 0];
    foreach ($installments as $key => $installment) {
        if ($current['brand'] !== $installment->getCardBrand()) {
            $current['brand'] = $installment->getCardBrand();
            $current['start'] = $key;
        }
        $current['quantity'] = $installment->getQuantity();
        $current['end'] = $key;
        if ($current['quantity'] > $final['quantity']) {
            $final = $current;
        }
    }

    return array_slice(
        $installments,
        $final['start'],
        $final['end'] - $final['start'] + 1
    );
}


init();