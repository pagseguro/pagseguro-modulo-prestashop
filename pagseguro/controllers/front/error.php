<?php

include_once(dirname(__FILE__) . '../../../../../config/config.inc.php');

if (version_compare(_PS_VERSION_, '1.5', '<')) {
    include_once(dirname(__FILE__) . '../../../../../header.php');
    include_once(dirname(__FILE__) . '../../../backward_compatibility/backward.php');
}
include_once(dirname(__FILE__) . '../../../pagseguro.php');

$pag_seguro = new PagSeguro();

redirectToErroPage();

if (version_compare(_PS_VERSION_, '1.5', '<')) {
    include_once (dirname(__FILE__) . '../../../../../footer.php');
}

function redirectToErroPage() 
{
    global $smarty, $pag_seguro;

    if (version_compare(_PS_VERSION_, '1.5', '<')) {
        $pag_seguro->display_column_left = false;
        $smarty->assign('version', _PS_VERSION_);
        echo $pag_seguro->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/front/error.tpl');
    } else {
		$controller = new FrontController();
		$controller->init();
		$controller->initContent();
		$controller->setMedia();
		$controller->display_column_left = false;
		
		$controller->initHeader();
		$controller->displayHeader();
		 		
        $smarty->assign('version', _PS_VERSION_);
        echo $pag_seguro->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/front/error.tpl');
        
		$controller->initFooter();
		$controller->displayFooter();
    }
}