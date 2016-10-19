<?php


//namespace PagSeguroModule\Controllers;

class PagSeguroDirectModuleFrontController extends ModuleFrontController
{

    public $ssl = true;
    public $context;

    public function initContent()
    {
        parent::initContent();
        $payment = new PagSeguroPaymentOrderPrestashop();
        $payment->setVariablesPaymentExecutionView();

        $environment = \PagSeguro\Configuration\Configure::getEnvironment();

        $this->context = Context::getContext();
        $this->context->smarty->clearAssign('hide_left_column');
        $this->context->smarty->clearAssign('display_column_left');
        $this->context->smarty->assign('hide_left_column', 1);
        $this->context->smarty->assign('display_column_left', 0);
        $this->context->smarty->assign('environment', $environment);

        if (version_compare(_PS_VERSION_, '1.5.0.1', '>=')) {
            $this->context->smarty->clearAssign('width_center_column');
            $this->context->smarty->assign('width_center_column', '100%');
        }

        if ($environment == 'sandbox') {
            $this->context->smarty->assign('pagseguro_direct_js', 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js');
        } else {
            $this->context->smarty->assign('pagseguro_direct_js', 'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js');
        }

        $session = \PagSeguro\Services\Session::create(
            \PagSeguro\Configuration\Configure::getAccountCredentials()
        );

        $this->context->smarty->assign('pagseguro_session', $session->getResult());

        $year = idate("Y");
        $maxYear = $year + 20;

        $this->context->smarty->assign('cc_years', $year);
        $this->context->smarty->assign('cc_max_years', $maxYear);

        $url = "index.php?fc=module&module=pagseguro&controller=error";
        $this->context->smarty->assign('errurl', $url);

        $this->setTemplate('payment-direct.tpl');
    }

}