<?php
include_once ('pagseguro_controller.php');

class PagSeguroController_15 extends PagSeguroController
{

    public function configPayment($params)
    {
        global $smarty;
        
        if (! $this->payment_module->active) {
            return;
        }
        
        $smarty->assign(array(
            'version_module' => _PS_VERSION_,
            'action_url' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=pagseguro&controller=payment',
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
        
        return $this->createTables();
    }

    public function doUnistall()
    {
        return true;
    }

    public function setPaymetnModule($module)
    {
        $this->payment_module = $module;
    }
}

?>
