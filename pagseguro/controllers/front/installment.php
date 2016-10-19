<?php


//namespace PagSeguroModule\Controllers;

class PagSeguroInstallmentModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $context;

    public function init()
    {
        try {
            $this->setOptions(filter_var($_POST['amount']), filter_var($_POST['brand']));

            $installments = \PagSeguro\Services\Installment::create(
                \PagSeguro\Configuration\Configure::getAccountCredentials(),
                $this->getOptions()
            );
            echo Tools::jsonEncode(
                array(
                    'success' => true,
                    'payload' => [
                        'data' => $this->output($installments->getInstallments(), false)
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
    private function getOptions() {
        return $this->options;
    }
    /**
     * Setter the options attribute
     * @param mixed $amount
     * @param string $brand
     */
    private function setOptions($amount, $brand) {
        $this->options = [
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
    private function output($installments, $maxInstallment)
    {
        return ($maxInstallment) ?
            $this->formatOutput($this->getMaxInstallment($installments)) :
            $this->formatOutput($installments);
    }

    /**
     * Format the installment to the be show in the view
     * @param  array $installments
     * @return array
     */
    private function formatOutput($installments)
    {
        $response = $this->getOptions();
        foreach($installments as $installment) {
            $response['installments'][] = $this->formatInstallments($installment);
        }
        return $response;
    }

    /**
     * Format a installment for output
     *
     * @param $installment
     * @return array
     */
    private function formatInstallments($installment)
    {
        return [
            'quantity' => $installment->getQuantity(),
            'amount' => $installment->getAmount(),
            'totalAmount' => round($installment->getTotalAmount(), 2),
            'text' => str_replace('.', ',', $this->getInstallmentText($installment))
        ];
    }

    /**
     * Mount the text message of the installment
     * @param  object $installment
     * @return string
     */
    private function getInstallmentText($installment)
    {
        return sprintf(
            "%s x de R$ %.2f %s juros",
            $installment->getQuantity(),
            $installment->getAmount(),
            $this->getInterestFreeText($installment->getInterestFree()));
    }

    /**
     * Get the string relative to if it is an interest free or not
     * @param string $insterestFree
     * @return string
     */
    private function getInterestFreeText($insterestFree)
    {
        return ($insterestFree == 'true') ? 'sem' : 'com';
    }

    /**
     * Get the bigger installments list in the installments
     * @param array $installments
     * @return array
     */
    private function getMaxInstallment($installments)
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

}