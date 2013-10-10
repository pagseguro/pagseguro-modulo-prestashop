<?php
include_once ('../PagSeguroLibrary/PagSeguroLibrary.php');
include_once ('pagseguro_controller.php');
include_once ('../backward_compatibility/Context.php');

class PagSeguroController_14 extends PagSeguroController
{

    public function configPayment($params)
    {
        global $smarty;
        
        if (! $this->payment_module->active) {
            die('fail');
            return;
        }
        
        $smarty->assign(array(
            'version_module' => _PS_VERSION_,
            'action_url' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/pagseguro/controllers/front/payment.php',
            'image' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
            'this_path' => __PS_BASE_URI__ . 'modules/pagseguro/',
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . v . 'modules/' . $this->payment_module->name . '/'
        ));
        
        return $this->payment_module->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/hook/payment.tpl');
    }

    public function configReturnPayment($params)
    {
        global $smarty;
        
        if (! $this->payment_module->active) {
            return;
        }
        
        if (! Tools::isEmpty($params['objOrder']) && $params['objOrder']->module === $this->payment_modulename) {
            
            $smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['objOrder']->total_paid_real, $this->context->currency->id, false),
                'status' => 'ok',
                'id_order' => (int) $params['objOrder']->id
            ));
            
            if (isset($params['objOrder']->reference) && ! empty($params['objOrder']->reference)) {
                $smarty->assign('reference', $params['objOrder']->reference);
            }
        } else
            $smarty->assign('status', 'failed');
        
        return $this->payment_module->display(__PS_BASE_URI__, 'modules/pagseguro/views/templates/hook/payment_return.tpl');
    }

    public function doInstall()
    {
        if (! PagSeguroLibrary::getVersion() < '2.1.8') {
            if (! $this->validatePagSeguroRequirements())
                return false;
        }
        
        if (! $this->generatePagSeguroOrderStatus()) {
            return false;
        }
        
        /* For 1.4.3 and less compatibility */
        $updateConfig = array(
            'PS_OS_CHEQUE' => 1,
            'PS_OS_PAYMENT' => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING' => 4,
            'PS_OS_DELIVERED' => 5,
            'PS_OS_CANCELED' => 6,
            'PS_OS_REFUND' => 7,
            'PS_OS_ERROR' => 8,
            'PS_OS_OUTOFSTOCK' => 9,
            'PS_OS_BANKWIRE' => 10,
            'PS_OS_PAYPAL' => 11,
            'PS_OS_WS_PAYMENT' => 12
        );
        
        foreach ($updateConfig as $u => $v) {
            if (! Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0)
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                else
                    Configuration::updateValue($u, $v);
            }
        }
        
        return $this->createTables();
    }

    public function doUnistall()
    {
        return true;
    }

    public function setPaymetnModule($module)
    {
        $this->payment_module = $module;
        $this->payment_module->context = Context::getContext();
    }
}

?>