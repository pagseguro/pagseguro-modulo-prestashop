<?php

include_once dirname(__FILE__) . '/../../init.php';
include_once dirname(__FILE__) . '/../../config/config.inc.php';
include_once dirname(__FILE__) . '/pagseguro.php';
include_once dirname(__FILE__) . '/features/modules/pagsegurofactoryinstallmodule.php';
include_once dirname(__FILE__) . '/features/modules/Interfaces/ConfigurableInterface.php';
include_once dirname(__FILE__) . '/features/modules/Configurations/AbstractConfiguration.php';
include_once dirname(__FILE__) . '/features/modules/Configurations/PagSeguroPrestaShop16.php';
include_once dirname(__FILE__) . '/features/payment/pagseguropaymentorderprestashop.php';
include_once dirname(__FILE__) . '/features/util/encryptionIdPagSeguro.php';
include_once dirname(__FILE__) . '/features/library/vendor/autoload.php';
include_once dirname(__FILE__) . '/controllers/front/direct.php';

