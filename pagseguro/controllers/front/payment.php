<?php

class PagSeguroPaymentModuleFrontController extends ModuleFrontController
{    
	public $ssl = true;

	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

		$this->context->smarty->assign(array(
			'image' => $this->module->getPathUri().'assets/images/logops_86x49.png',
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->module->getCurrency((int) $cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'isocode' => $this->context->language->iso_code,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'));

		$this->setTemplate('payment_execution.tpl');
	}
}
