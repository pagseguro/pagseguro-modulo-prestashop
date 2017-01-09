<?php


//namespace PagSeguroModule\Controllers;

class PagSeguroSuccessModuleFrontController extends ModuleFrontController
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
        $this->context->smarty->assign('environment', $environment);
        if (version_compare(_PS_VERSION_, '1.5.0.1', '>='))
//            $this->context->smarty->assign('width_center_column', '80%');

        $url = "index.php?fc=module&module=pagseguro&controller=error";
        $this->context->smarty->assign('errurl', $url);

        $this->setTemplate('order-confirmation.tpl');
    }

}