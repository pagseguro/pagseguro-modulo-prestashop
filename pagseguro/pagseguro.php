<?php
/**
 * 2007-2015 PrestaShop
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
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

require_once dirname(__FILE__).'/Loader.php';

if (!defined('_PS_VERSION_'))
    exit();

if (function_exists('__autoload')) {
    spl_autoload_register('__autoload');
}

class PagSeguro extends PaymentModule {

    /**
     * @var PagSeguroPS15|PagSeguroPS1501ToPS1503|PagSeguroPS16|PagSeguroPS1601|PagSeguroPS17
     */
    private $modulo;
    /**
     * @var array
     */
    protected $errors = array();

    /**
     * @var
     */
    public $context;

    /**
     * @var string
     */
    private $pageId = '1';

    /**
     * PagSeguro constructor.
     */
    public function __construct() {
        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '2.2.0';
        $this->author = 'PagSeguro Internet LTDA.';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        \PagSeguro\Library::initialize();
        \PagSeguro\Configuration\Configure::setCharset(Configuration::get('PAGSEGURO_CHARSET'));
        \PagSeguro\Configuration\Configure::setLog(
            Configuration::get('PAGSEGURO_LOG_ACTIVE'),
            _PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION')
        );
        \PagSeguro\Library::cmsVersion()->setName("'prestashop-v.'")->setRelease(_PS_VERSION_);
        \PagSeguro\Library::moduleVersion()->setName('prestashop-v.')->setRelease($this->version);
        \PagSeguro\Configuration\Configure::setAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));

        parent::__construct();

        $this->displayName = $this->l('PagSeguro');
        $this->description = $this->l('Receba pagamentos por cartão de crédito, transferência bancária e boleto');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo?');

        if (version_compare(_PS_VERSION_, '1.5.0.2', '<')) {
            include_once (dirname(__FILE__) . '/backward_compatibility/backward.php');
        }

        //Configura o ambiente pra lib
        if ($this->getPrestaShopEnvironment())
            \PagSeguro\Configuration\Configure::setEnvironment($this->getPrestaShopEnvironment());

        $this->setContext();
        $this->modulo = PagSeguroFactoryInstallModule::createModule(_PS_VERSION_);
    }

    public function getEnvironment()
    {
        \PagSeguro\Configuration\Configure::setEnvironment($this->getPrestaShopEnvironment());
        return \PagSeguro\Configuration\Configure::getEnvironment();
    }

    public function getPagSeguroCredentials()
    {
        return \PagSeguro\Configuration\Configure::getAccountCredentials();
    }

    /**
     * @return bool
     */
    public function isLightboxCheckoutType()
    {
        if (Configuration::get('PAGSEGURO_CHECKOUT')) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function install() {
        if (version_compare(\PagSeguro\Library::libraryVersion(), '2.1.8', '<=')) {
            if (!$this->validatePagSeguroRequirements()) {
                return false;
            }
        }

        if (!$this->validatePagSeguroId()) {
            $this->_errors[] = 'Erro ao validar PagSeguro ID.';
            return false;
        }
        if (!$this->validateOrderMessage()) {
            $this->_errors[] = 'Erro ao validar Mensagem de Pedido.';
            return false;
        }
        if (!$this->generatePagSeguroOrderStatus()) {
            $this->_errors[] = 'Erro ao gerar Status de Pedidos PagSeguro.';
            return false;
        }
        if (!$this->createTables()) {
            $this->_errors[] = 'Erro ao criar tabelas de suporte para o módulo.';
            return false;
        }
        if (!$this->modulo->installConfiguration()) {
            $this->_errors[] = 'Erro ao instalar configurações PagSeguro.';
            return false;
        }

        if (!parent::install() or
            !$this->registerHook('paymentReturn') or
            !$this->registerHook('header')) {
            $this->_errors[] = 'Erro ao instalar e registrar hooks (header e paymentReturn).';
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (!$this->registerHook('paymentOptions')) {
                $this->_errors[] = 'Erro ao registrar hook (paymentOptions).';
                return false;
            }
        } else {
            if (!$this->registerHook('payment')) {
                $this->_errors[] = 'Erro ao registrar hook (payment).';
                return false;
            }
        }

        if (! Configuration::updateValue('PAGSEGURO_EMAIL', '') or
            ! Configuration::updateValue('PAGSEGURO_TOKEN', '') or
            ! Configuration::updateValue('PAGSEGURO_ENVIRONMENT', '') or
            ! Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '') or
            ! Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', '') or
            ! Configuration::updateValue('PAGSEGURO_CHARSET', \PagSeguro\Configuration\Configure::getCharset()->getEncoding()) or
            ! Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', \PagSeguro\Configuration\Configure::getLog()->getActive()) or
            ! Configuration::updateValue('PAGSEGURO_RECOVERY_ACTIVE', false) or
            ! Configuration::updateValue('PAGSEGURO_CHECKOUT', false) or
            ! Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', \PagSeguro\Configuration\Configure::getLog()->getActive()) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_CREDITCARD', false) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_CREDITCARD_VL', "00.00") or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_BOLETO', false) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_BOLETO_VL', "00.00") or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_EFT', false) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_EFT_VL', "00.00") or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_DEPOSIT', false) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_DEPOSIT_VL', "00.00") or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_BALANCE', false) or
            ! Configuration::updateValue('PAGSEGURO_DISCOUNT_BALANCE_VL', "00.00")
        ) {
            $this->_errors[] = 'Erro ao iniciar as configurações padrões do módulo.';
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall() {

        if (!$this->uninstallOrderMessage()) {
            return false;
        }
        if (!$this->modulo->uninstallConfiguration()) {
            return false;
        }
        if (!Configuration::deleteByName('PAGSEGURO_EMAIL')
            or ! Configuration::deleteByName('PAGSEGURO_TOKEN')
            or ! Configuration::deleteByName('PAGSEGURO_URL_REDIRECT')
            or ! Configuration::deleteByName('PAGSEGURO_NOTIFICATION_URL')
            or ! Configuration::deleteByName('PAGSEGURO_CHARSET')
            or ! Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE')
            or ! Configuration::deleteByName('PAGSEGURO_RECOVERY_ACTIVE')
            or ! Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION')
            or ! Configuration::deleteByName('PS_OS_PAGSEGURO')
            or ! Configuration::deleteByName('PAGSEGURO_CHECKOUT')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_CREDITCARD')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_CREDITCARD_VL')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_BOLETO')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_BOLETO_VL')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_EFT')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_EFT_VL')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_DEPOSIT')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_DEPOSIT_VL')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_BALANCE')
            or ! Configuration::deleteByName('PAGSEGURO_DISCOUNT_BALANCE_VL')
            or ! parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getNotificationUrl() {
        return $this->modulo->getNotificationUrl();
    }

    /**
     * @return string
     */
    public function getDefaultRedirectionUrl() {
        return $this->modulo->getDefaultRedirectionUrl();
    }

    /**
     * @return string
     */
    public function getJsBehavior() {
        return $this->modulo->getJsBehaviors();
    }

    /**
     * @return string
     */
    public function getCssDisplay() {
        return $this->modulo->getCssDisplay();
    }

    /**
     * @param string $value
     * @return int
     */
    public static function returnIdCurrency($value = 'BRL') {
        $sql = 'SELECT `id_currency`
        FROM `' . _DB_PREFIX_ . 'currency`
        WHERE `deleted` = 0
        AND `iso_code` = "' . $value . '"';

        $id_currency = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        return empty($id_currency) ? 0 : $id_currency[0]['id_currency'];
    }

    /**
     * @param $params
     */
    public function hookPayment($params) {

        if (!$this->active) {
            return;
        }

        $this->modulo->paymentConfiguration($params);

        if (version_compare(_PS_VERSION_, '1.6.0.1', '<'))
            $bootstrap = true;
        else
            $bootstrap = false;

        $this->addToView('version', $bootstrap);

        return $this->context->smarty->fetch('module:pagseguro/views/templates/hook/payment.tpl');
    }

    public function hookPaymentOptions($params) {

        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        // apenas checkout padrão PagSeguro
        return [
            $this->getExternalPaymentOption()
        ];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function hookPaymentReturn($params) {
        $this->modulo->returnPaymentConfiguration($params);
        return $this->context->smarty->fetch('module:pagseguro/views/templates/hook/payment_return.tpl');
    }

    public function hookHeader($params)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<'))
        {
            $this->context->controller->addJS("https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js");
            $this->context->controller->addJS("https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.4.1/jquery-migrate.min.js");
        }
    }

    public function checkCurrency($cart) {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Prestashop 1.7 external payment option
     *
     * @return PaymentOption
     */
    public function getExternalPaymentOption() {
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Pague com PagSeguro'))
           ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
           ->setAdditionalInformation($this->context->smarty->fetch('module:pagseguro/views/templates/front/payment_infos.tpl'))
           ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'));
        return $externalOption;
    }

    /**
     * PrestaShop getContent function steps:
     *
     *  1) Validate and save post confuguration data
     *  2) Route and show current virtual page
     *
     */
    public function getContent() {
        $this->verifyPost();
        return $this->pageRouter();
    }

    /**
     *   Validate and save post confuguration data
     */
    private function verifyPost() {
        if ($this->postValidation()) {
            $this->savePostData();
        }
    }

    /**
     * Shorthand to assign on Smarty
     */
    private function addToView($key, $value) {
        if ($this->context->smarty) {
            $this->context->smarty->assign($key, $value);
        }
    }

    /**
     * Realize post validations according with PagSeguro standards
     * case any inconsistence, return false and assign to view context on $errors array
     * @return bool
     */
    private function postValidation() {

        $valid = true;
        $errors = Array();

        if (Tools::isSubmit('pagseguroModuleSubmit')) {

            /** E-mail validation */
            $email = Tools::getValue('pagseguroEmail');

            if (empty($email)) {
                if (Tools::strlen($email) > 60) {
                    $errors[] = $this->invalidFieldSizeMessage('E-MAIL');
                } elseif (!Validate::isEmail($email)) {
                    $errors[] = $this->invalidMailMessage('E-MAIL');
                }
            }

            /** Token validation */
            $token = Tools::getValue('pagseguroToken');
            if (empty($token) && Tools::strlen($token) != 32) {
                $errors[] = $this->invalidFieldSizeMessage('TOKEN');
            }

            /** Credentials validation */
            if (! $errors) {
                if (! $this->validateCredentials()) {
                    $errors[] = $this->invalidCredentails();
                }
            }

            /** URL redirect validation */
            $redirectUrl = Tools::getValue('pagseguroRedirectUrl');
            if ($redirectUrl && !filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $errors[] = $this->invalidUrlMessage('URL DE REDIRECIONAMENTO');
            }

            /** Notification url validation */
            $notificationUrl = Tools::getValue('pagseguroNotificationUrl');
            if ($notificationUrl && !filter_var($notificationUrl, FILTER_VALIDATE_URL)) {
                $errors[] = $this->invalidUrlMessage('URL DE NOTIFICAÇÃO');
            }

            /** Charset validation */
            $charset = Tools::getValue('pagseguroCharset');
            if ($charset && !array_key_exists($charset, Util::getCharsetOptions())) {
                $errors[] = $this->invalidValueMessage('CHARSET');
            }

            /** Log validation */
            $logActive = Tools::getValue('pagseguroLogActive');
            if ($logActive && !array_key_exists($logActive, Util::getActive())) {
                $errors[] = $this->invalidValueMessage('LOG');
            }

            /** Recovery validation */
            $recoveryActive = Tools::getValue('pagseguroRecoveryActive');
            if ($recoveryActive && !array_key_exists($recoveryActive, Util::getActive())) {
                $errors[] = $this->invalidValueMessage('Listar transações abandonadas');
            }

            /** Discount credit card validation */
            $discountCreditCard = Tools::getValue('pagseguroDiscountCreditCardInput');
            if ($discountCreditCard && is_bool($discountCreditCard)) {
                $errors[] = $this->invalidValueMessage("Desconto para cartão de crédito.");
            }
            $discountCreditCardValue = Tools::getValue('pagseguroDiscountCreditCardDiscountInput');
            if ($discountCreditCardValue && is_float($discountCreditCardValue)) {
                $errors[] = $this->invalidValueMessage("Valor do desconto para cartão de crédito.");
            }

            /** Discount boleto validation */
            $discountBoleto = Tools::getValue('pagseguroDiscountBoletoInput');
            if ($discountBoleto && is_bool($discountBoleto)) {
                $errors[] = $this->invalidValueMessage("Desconto para boleto.");
            }
            $discountBoletoValue = Tools::getValue('pagseguroDiscountBoletoDiscountInput');
            if ($discountBoletoValue && is_float($discountBoletoValue)) {
                $errors[] = $this->invalidValueMessage("Valor do desconto para boleto.");
            }

            /** Discount EFT validation */
            $discountEFT = Tools::getValue('pagseguroDiscountEFTInput');
            if ($discountEFT && is_bool($discountEFT)) {
                $errors[] = $this->invalidValueMessage("Desconto para débito online.");
            }
            $discountEFTValue = Tools::getValue('pagseguroDiscountEFTDiscountInput');
            if ($discountBoletoValue && is_float($discountEFTValue)) {
                $errors[] = $this->invalidValueMessage("Valor do desconto para débito online");
            }

            /** Discount deposit validation */
            $discountDeposit = Tools::getValue('pagseguroDiscountDepositInput');
            if ($discountDeposit && is_bool($discountDeposit)) {
                $errors[] = $this->invalidValueMessage("Desconto para depósito");
            }
            $discountDepositValue = Tools::getValue('pagseguroDiscountDepositDiscountInput');
            if ($discountDepositValue && is_float($discountDepositValue)) {
                $errors[] = $this->invalidValueMessage("Valor do desconto para depósito");
            }

            /** Discount balance validation */
            $discountBalance = Tools::getValue('pagseguroDiscountDepositInput');
            if ($discountBalance && is_bool($discountBalance)) {
                $errors[] = $this->invalidValueMessage("Desconto para saldo PagSeguro");
            }
            $discountBalanceValue = Tools::getValue('pagseguroDiscountDepositDiscountInput');
            if ($discountBalanceValue && is_float($discountBalanceValue)) {
                $errors[] = $this->invalidValueMessage("Valor do desconto para o saldo PagSeguro.");
            }

            if (count($errors) > 0) {
                $valid = false;
            }
        }

        $this->addToView('errors', $errors);
        return $valid;
    }

    /**
     *   Validation error messages
     * @return string
     */
    private function missedCurrencyMessage() {
        return sprintf($this->l('Verifique se a moeda REAL está instalada e ativada.'));
    }

    /**
     * @param $field
     * @return string
     */
    private function invalidMailMessage($field) {
        return sprintf($this->l('O campo %s deve ser conter um email válido.'), $field);
    }

    /**
     * @param $field
     * @return string
     */
    private function invalidFieldSizeMessage($field) {
        return sprintf($this->l('O campo %s está com um tamanho inválido'), $field);
    }

    /**
     * @param $field
     * @return string
     */
    private function invalidValueMessage($field) {
        return sprintf($this->l('O campo %s contém um valor inválido.'), $field);
    }

    /**
     * @param $field
     * @return string
     */
    private function invalidUrlMessage($field) {
        return sprintf($this->l('O campo %s deve conter uma url válida.'), $field);
    }

    /**
     * @param $field
     * @return string
     */
    private function invalidCredentails() {
        return $this->l('Certifique-se de que o e-mail e token são válidos.');
    }

    /**
     * Valitates a PagSeguro credentials
     * @return bool
     */
    private function validateCredentials()
    {
        if ($this->checkCredentials() instanceof \PagSeguro\Parsers\Transaction\Search\Date\Response)
            return true;
        return false;
    }

    /**
     * Check in PagSeguro webservice if this credentials are valid.
     * @return string
     */
    private function checkCredentials()
    {

        \PagSeguro\Configuration\Configure::setEnvironment(Tools::getValue('pagseguroEnvironment'));

        date_default_timezone_set("America/Sao_Paulo");
        $date = new DateTime("Now");
        $date->sub(new DateInterval('PT10M'));

        $options = [
            'initial_date' => $date->format('Y-m-d\TH:i:s'),
            'page' => 1, //Optional
            'max_per_page' => 1, //Optional
        ];

        try {
            return \PagSeguro\Services\Transactions\Search\Date::search(
                new \PagSeguro\Domains\AccountCredentials(Tools::getValue('pagseguroEmail'), Tools::getValue('pagseguroToken')),
                $options
            );
//            return PagSeguroTransactionSearchService::searchByDate(
//                new PagSeguroAccountCredentials(Tools::getValue('pagseguroEmail'),Tools::getValue('pagseguroToken')),
//                1,
//                1,
//
//            );
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Save configuration post config data
     */
    private function updateConfigData()
    {
        $updateData = Array(
            'pagseguroEmail' => 'PAGSEGURO_EMAIL',
            'pagseguroToken' => 'PAGSEGURO_TOKEN',
            'pagseguroEnvironment' => 'PAGSEGURO_ENVIRONMENT',
            'pagseguroRedirectUrl' => 'PAGSEGURO_URL_REDIRECT',
            'pagseguroNotificationUrl' => 'PAGSEGURO_NOTIFICATION_URL',
            'pagseguroCharset' => 'PAGSEGURO_CHARSET',
            'pagseguroCheckout' => 'PAGSEGURO_CHECKOUT',
            'pagseguroLogActive' => 'PAGSEGURO_LOG_ACTIVE',
            'pagseguroLogFileLocation' => 'PAGSEGURO_LOG_FILELOCATION',
            'pagseguroRecoveryActive' => 'PAGSEGURO_RECOVERY_ACTIVE',
        );

        foreach ($updateData as $postIndex => $configIndex) {
            if (Tools::getValue($postIndex)) {
                Configuration::updateValue($configIndex, Tools::getValue($postIndex));
            }
            if (Tools::getValue($postIndex) == "0") {
                Configuration::updateValue($configIndex, Tools::getValue($postIndex));
            }
        }
    }

    /**
     * Save configuration post for discount data
     */
    private function updateDiscountData()
    {
        $updateDiscount = Array(
            'pagseguroDiscountCreditCardInput' => Array(
                "key" => 'PAGSEGURO_DISCOUNT_CREDITCARD',
                "value" => Array(
                    'pagseguroDiscountCreditCardDiscountInput' => 'PAGSEGURO_DISCOUNT_CREDITCARD_VL'
                )),
            'pagseguroDiscountBoletoInput' =>  Array(
                "key" => 'PAGSEGURO_DISCOUNT_BOLETO',
                "value"  => Array(
                    'pagseguroDiscountBoletoDiscountInput' => 'PAGSEGURO_DISCOUNT_BOLETO_VL'
                )),
            'pagseguroDiscountEftInput' => Array(
                "key" => 'PAGSEGURO_DISCOUNT_EFT',
                "value" => Array(
                    'pagseguroDiscountEftDiscountInput' => 'PAGSEGURO_DISCOUNT_EFT_VL'
                )),
            'pagseguroDiscountDepositInput' => Array(
                "key" => 'PAGSEGURO_DISCOUNT_DEPOSIT',
                "value" => Array(
                    'pagseguroDiscountDepositDiscountInput' => 'PAGSEGURO_DISCOUNT_DEPOSIT_VL'
                )),
            'pagseguroDiscountBalanceInput' => Array(
                "key" => 'PAGSEGURO_DISCOUNT_BALANCE',
                "value" => Array(
                    'pagseguroDiscountBalanceDiscountInput' => 'PAGSEGURO_DISCOUNT_BALANCE_VL'
                )),
        );

        foreach ($updateDiscount as $postIndex => $configIndex) {

            if (Tools::getValue($postIndex)) {
                Configuration::updateValue($configIndex["key"], Tools::getValue($postIndex));
                Configuration::updateValue($configIndex["value"][key($configIndex["value"])], Tools::getValue(key($configIndex["value"])));
            } else {
                Configuration::updateValue($configIndex["key"], Tools::getValue($postIndex));
                Configuration::updateValue($configIndex["value"][key($configIndex["value"])], "00.00");
            }
        }
    }

    /**
     * Save configuration post for log data
     */
    private function updateLogData()
    {
        /** Verify if log file exists, case not try create */
        if (Tools::getValue('pagseguroLogActive')) {
            $this->verifyLogFile(Tools::getValue('pagseguro_log_dir'));
        }
    }

    /**
     * Save configuration post data
     */
    private function savePostData() {
        if (Tools::isSubmit('pagseguroModuleSubmit')) {
            $this->updateConfigData();
            $this->updateDiscountData();
            $this->updateLogData();
            $this->addToView('success', true);
            //$this->verifyEnvironment();
        }
    }

    /**
     *
     */
    private function prepareAdminToken() {
        $adminToken = Tools::getAdminTokenLite('AdminOrders');
        $this->addToView('adminToken', $adminToken);
        $this->addToView('urlAdminOrder', $_SERVER['SCRIPT_NAME'] . '?tab=AdminOrders');
    }

    /**
     *
     */
    private function applyDefaultViewData() {
        $this->addToView('module_dir', _PS_MODULE_DIR_ . 'pagseguro/');
        $this->addToView('moduleVersion', $this->version);
        $this->addToView('action_post', Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']));
        $this->addToView('cssFileVersion', $this->getCssDisplay());
        $this->prepareAdminToken();
    }

    /**
     * Route virtual page
     * @return mixed
     */
    private function pageRouter() {

        $this->applyDefaultViewData();

        $pages = array(
            'config' => array(
                'id' => 1,
                'title' => $this->l('Configuração'),
                'content' => $this->getConfigurationPageHtml(),
                'hasForm' => true,
                'selected' => ($this->pageId == '1'),
                'hasChild' => false
            ),
            'transactions' => array (
                'id' => 2,
                'title' => $this->l('Transações'),
                'content' => array(

                    'abandoned' => array(
                        'id' => 4,
                        'title' => $this->l('Abandonadas'),
                        'content' => $this->getAbandonedPageHtml(),
                        'hasForm' => false,
                        'selected' => ($this->pageId == '4'),
                        'hasChild' => false
                    ),
                    'cancel' => array(
                        'id' => 6,
                        'title' => $this->l('Cancelamento'),
                        'content' => $this->getCancelPageHtml(),
                        'hasForm' => false,
                        'selected' => ($this->pageId == '6'),
                        'hasChild' => false
                    ),
                    'conciliation' => array(
                        'id' => 3,
                        'title' => $this->l('Conciliação'),
                        'content' => $this->getConciliationPageHtml(),
                        'hasForm' => false,
                        'selected' => ($this->pageId == '3'),
                        'hasChild' => false
                    ),
                    'refund' => array(
                        'id' => 5,
                        'title' => $this->l('Estorno'),
                        'content' => $this->getRefundPageHtml(),
                        'hasForm' => false,
                        'selected' => ($this->pageId == '5'),
                        'hasChild' => false
                    )
                ),
                'hasForm' => false,
                'icon' => true,
                'selected' => ($this->pageId == '2'),
                'hasChild' => true
            ),
            'requirements' => array(
                'id' => 7,
                'title' => $this->l('Requisitos'),
                'content' => $this->getRequirementsPageHtml(),
                'hasForm' => false,
                'selected' => ($this->pageId == '7'),
                'hasChild' => false
            )
        );

        $this->addToView('pages', $pages);

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/main.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', 'views/templates/admin/main.tpl');
    }

    /**
     * @return mixed
     */
    private function getConfigurationPageHtml() {

        $this->addToView('email', Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')));
        $this->addToView('token', Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')));
        $this->addToView('environment', Tools::safeOutput(Configuration::get('PAGSEGURO_ENVIRONMENT')));
        $this->addToView('notificationUrl', $this->getNotificationUrl());
        $this->addToView('redirectUrl', $this->getDefaultRedirectionUrl());

        $charsetOptions = Util::getCharsetOptions();
        $this->addToView('charsetKeys', array_keys($charsetOptions));
        $this->addToView('charsetValues', array_values($charsetOptions));
        $this->addToView('charsetSelected', Configuration::get('PAGSEGURO_CHARSET'));

        $checkoutOptions = Util::getTypeCheckout();
        $this->addToView('checkoutKeys', array_keys($checkoutOptions));
        $this->addToView('checkoutValues', array_values($checkoutOptions));
        $this->addToView('checkoutSelected', Configuration::get('PAGSEGURO_CHECKOUT'));

        $activeOptions = Util::getActive();
        $this->addToView('logActiveKeys', array_keys($activeOptions));
        $this->addToView('logActiveValues', array_values($activeOptions));
        $this->addToView('logActiveSelected', Configuration::get('PAGSEGURO_LOG_ACTIVE'));
        $this->addToView('logFileLocation', Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')));

        $this->addToView('recoveryActiveKeys', array_keys($activeOptions));
        $this->addToView('recoveryActiveValues', array_values($activeOptions));
        $this->addToView('recoveryActiveSelected', Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));

        $this->addToView('discountCreditCard', Configuration::get('PAGSEGURO_DISCOUNT_CREDITCARD'));
        $this->addToView('discountCreditCardValue', Configuration::get('PAGSEGURO_DISCOUNT_CREDITCARD_VL'));
        $this->addToView('discountBoleto', Configuration::get('PAGSEGURO_DISCOUNT_BOLETO'));
        $this->addToView('discountBoletoValue', Configuration::get('PAGSEGURO_DISCOUNT_BOLETO_VL'));
        $this->addToView('discountEFT', Configuration::get('PAGSEGURO_DISCOUNT_EFT'));
        $this->addToView('discountEFTValue', Configuration::get('PAGSEGURO_DISCOUNT_EFT_VL'));
        $this->addToView('discountDeposit', Configuration::get('PAGSEGURO_DISCOUNT_DEPOSIT'));
        $this->addToView('discountDepositValue', Configuration::get('PAGSEGURO_DISCOUNT_DEPOSIT_VL'));
        $this->addToView('discountBalance', Configuration::get('PAGSEGURO_DISCOUNT_BALANCE'));
        $this->addToView('discountBalanceValue', Configuration::get('PAGSEGURO_DISCOUNT_BALANCE_VL'));

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/settings.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/settings.tpl');
    }

    /**
     * @return mixed
     */
    private function getConciliationPageHtml() {

        if (Configuration::get('PAGSEGURO_EMAIL') && Configuration::get('PAGSEGURO_TOKEN')) {
            $this->addToView('hasCredentials', true);
            $conciliationSearch = Util::getDaysSearch();
            $this->addToView('conciliationSearchKeys', array_keys($conciliationSearch));
            $this->addToView('conciliationSearchValues', array_values($conciliationSearch));
        }

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/conciliation.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/conciliation.tpl');
    }

    /**
     * @return mixed
     */
    private function getAbandonedPageHtml() {

        $recoveryActive = Configuration::get('PAGSEGURO_RECOVERY_ACTIVE');

        if ($recoveryActive) {

            $this->addToView('recoveryActive', true);

            if (Configuration::get('PAGSEGURO_EMAIL') && Configuration::get('PAGSEGURO_TOKEN')) {
                $this->addToView('hasCredentials', true);
                $daysToRecoveryOptions = Util::getDaysRecovery();
                $this->addToView('daysToRecoveryKeys', array_values($daysToRecoveryOptions));
                $this->addToView('daysToRecoveryValues', array_values($daysToRecoveryOptions));
            }
        }

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/abandoned.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/abandoned.tpl');
    }

    /**
     * @return mixed
     */
    private function getRefundPageHtml() {

        if (Configuration::get('PAGSEGURO_EMAIL') && Configuration::get('PAGSEGURO_TOKEN')) {
            $this->addToView('hasCredentials', true);
            $search = Util::getDaysSearch();
            $this->addToView('searchKeys', array_keys($search));
            $this->addToView('searchValues', array_values($search));
        }

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/refund.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/refund.tpl');
    }

    /**
     * @return mixed
     */
    private function getCancelPageHtml() {

        if (Configuration::get('PAGSEGURO_EMAIL') && Configuration::get('PAGSEGURO_TOKEN')) {
            $this->addToView('hasCredentials', true);
            $search = Util::getDaysSearch();
            $this->addToView('searchKeys', array_keys($search));
            $this->addToView('searchValues', array_values($search));
        }

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/cancel.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/cancel.tpl');
    }

    /**
     * @return mixed
     */
    private function getRequirementsPageHtml() {

        $requirements = array();

//        $validation = PagSeguroConfig::validateRequirements();
//        foreach ($validation as $key => $value) {
//            if (Tools::strlen($value) == 0) {
//                $requirements[$key][0] = true;
//                $requirements[$key][1] = null;
//            } else {
//                $requirements[$key][0] = false;
//                $requirements[$key][1] = $value;
//            }
//        }


        try {
            \PagSeguro\Library::validate();
        } catch (Exception $exception) {
            //@todo get the exception and send to requirements
        }

        $currency = self::returnIdCurrency();

        /** Currency validation */
        if ($currency) {
            $requirements['moeda'][0] = true;
            $requirements['moeda'][1] = "Moeda REAL instalada.";
        } else {
            $requirements['moeda'][0] = false;
            $requirements['moeda'][1] = $this->missedCurrencyMessage();
        }

//        $requirements['curl'][1] = (is_null($requirements['curl'][1]) ? "Biblioteca cURL instalada." : $requirements['curl'][1]);
//        $requirements['dom'][1] = (is_null($requirements['dom'][1]) ? "DOM XML instalado." : $requirements['dom'][1]);
//        $requirements['spl'][1] = (is_null($requirements['spl'][1]) ? "Biblioteca padrão do PHP(SPL) instalada." : $requirements['spl'][1]);
//        $requirements['version'][1] = (is_null($requirements['version'][1]) ? "Versão do PHP superior à 5.3.3." : $requirements['version'][1]);

//        $this->addToView('requirements', $requirements);

        // FAIL ON ADMIN CONFIG PAGE - PS 1.7 smarty->fetch()
        // return $this->context->smarty->fetch('module:pagseguro/views/templates/admin/requirements.tpl');
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/admin/requirements.tpl');
    }

    /**
     *
     */
    private function setContext() {
        $this->context = Context::getContext();
    }

    /**
     * @return bool
     */
    private function validatePagSeguroRequirements() {
        $condional = true;

        foreach (\PagSeguro\Library::validate() as $value) {
            if (!Tools::isEmpty($value)) {
                $condional = false;
                $this->errors[] = Tools::displayError($value);
            }
        }

        if (!$condional) {
            $this->html = $this->displayError(implode('<br />', $this->errors));
        }

        return $condional;
    }

    /**
     * @return bool
     */
    private function createTables() {

        $sql = '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagseguro_order` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `id_transaction` varchar(255) NOT NULL,
                `id_order` int(10) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8  auto_increment=1;
        ';

        if (Db::getInstance()->Execute($sql)) {
            return $this->alterTables();
        }

        return false;
    }

    /**
     * @return bool
     */
    private function alterTables() {

        $hasColumn = $this->_hasColumn();
        if ($hasColumn) {
            if ($this->alterTable($hasColumn))
                return true;
        }
        return false;
    }

    /**
     * @param $hasColumn
     * @param $hasNullable
     * @return bool
     */
    private function alterTable($hasColumn, $hasNullable = null)
    {
        try {
            $this->addColumns($hasColumn);
            $this->addNullable();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $hasColumn
     * @return bool
     */
    private function addColumns($hasColumn)
    {
        if ( (! (int)$hasColumn[0]['hasSendRecovery'])
            AND (! (int)$hasColumn[0]['hasEnvironment'])) {
            return Db::getInstance()->Execute('
                ALTER TABLE `' . _DB_PREFIX_ . 'pagseguro_order`
                ADD COLUMN `send_recovery` int(10) unsigned NOT NULL default 0,
                ADD COLUMN `environment` varchar(50) NULL;
            ');
        }
        if ((! (int)$hasColumn[0]['hasSendRecovery'])
            AND ((int)$hasColumn[0]['hasEnvironment'])) {
            return Db::getInstance()->Execute('
                ALTER TABLE `' . _DB_PREFIX_ . 'pagseguro_order`
                ADD COLUMN `send_recovery` int(10) unsigned NOT NULL default 0;
            ');
        }
        if (((int)$hasColumn[0]['hasSendRecovery'])
            AND (!(int)$hasColumn[0]['hasEnvironment'])) {
            return Db::getInstance()->Execute('
                ALTER TABLE `' . _DB_PREFIX_ . 'pagseguro_order`
                ADD COLUMN `environment` varchar(50) NULL;
            ');
        }
        return true;
    }

    /**
     * @return mixed
     */
    private function _hasColumn()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            "SELECT * FROM (
                    SELECT COUNT(*) AS hasSendRecovery
                        FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE column_name = 'send_recovery'
                            AND table_name = '". _DB_PREFIX_."pagseguro_order'
                            AND table_schema = '". _DB_NAME_."') as tb_send_recovery
                                INNER JOIN (
                                    SELECT COUNT(*) AS hasEnvironment
                                        FROM INFORMATION_SCHEMA.COLUMNS
                                            WHERE column_name = 'environment'
                                            AND table_name = '". _DB_PREFIX_."pagseguro_order'
                                            AND table_schema = '". _DB_NAME_."') as tb_environment;");

    }

    /**
     * @return bool
     */
    private function addNullable()
    {
        $_hasNullable = $this->_hasNullable();
        if ($_hasNullable[0]["is_nullable"] == "NO") {
            return Db::getInstance()->Execute('
            ALTER TABLE `' . _DB_PREFIX_ . 'pagseguro_order`
                CHANGE COLUMN `id_transaction` `id_transaction` VARCHAR(255) NULL;
            ');
        }
        return false;
    }

    /**
     * @return mixed
     */
    private function _hasNullable()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            "SELECT is_nullable FROM INFORMATION_SCHEMA.COLUMNS
                WHERE column_name = 'id_transaction'
                AND table_name = '". _DB_PREFIX_."pagseguro_order'
                AND table_schema = '". _DB_NAME_."';");
    }

    /**
     * @return bool
     */
    private function validatePagSeguroId() {
        $id = Configuration::get('PAGSEGURO_ID');
        if (empty($id)) {
            $id = EncryptionIdPagSeguro::idRandomGenerator();
            return Configuration::updateValue('PAGSEGURO_ID', $id);
        }
        return true;
    }

    /**
     * @return mixed
     */
    private function validateOrderMessage() {

        $orderMensagem = new OrderMessage();

        foreach (Language::getLanguages(false) as $language) {
            $orderMensagem->name[(int) $language['id_lang']] = "cart recovery pagseguro";
            $orderMensagem->message[(int) $language['id_lang']] = "Verificamos que você não concluiu sua compra. Clique no link abaixo para dar prosseguimento.";
        }

        $orderMensagem->date_add = date('now');
        $orderMensagem->save();

        return Configuration::updateValue('PAGSEGURO_MESSAGE_ORDER_ID', $orderMensagem->id);
    }

    /**
     * @return bool
     */
    private function uninstallOrderMessage() {

        $orders = array();
        $sql = "SELECT `id_order_message` as id FROM `" . _DB_PREFIX_ . "order_message_lang` WHERE `name` = 'cart recovery pagseguro'";
        $result = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        if ($result) {

            $bool = false;
            foreach ($result as $order_message) {

                if (!$bool) {

                    $orders[] = $order_message['id'];
                    $bool = true;
                } else {

                    if (array_search($order_message['id'], $orders) === false) {
                        $orders[] = $order_message['id'];
                    }
                }
            }

            for ($i = 0; $i < count($orders); $i++) {

                $sql = "DELETE FROM `" . _DB_PREFIX_ . "order_message` WHERE `id_order_message` = '" . $orders[$i] . "'";
                Db::getInstance()->execute($sql);
            }

            for ($i = 0; $i < count($result); $i++) {
                $id = $result[$i]['id'];
                $sql = "DELETE FROM `" . _DB_PREFIX_ . "order_message_lang` WHERE `id_order_message` = '" . $id . "'";
                Db::getInstance()->execute($sql);
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function generatePagSeguroOrderStatus() {

        $orders_added = true;
        $name_state = null;
        $image = _PS_ROOT_DIR_ . '/modules/pagseguro/logo.gif';

        foreach (Util::getCustomOrderStatusPagSeguro() as $key => $statusPagSeguro) {

            $order_state = new OrderState();
            $order_state->module_name = 'pagseguro';
            $order_state->send_email = $statusPagSeguro['send_email'];
            $order_state->color = '#95D061';
            $order_state->hidden = $statusPagSeguro['hidden'];
            $order_state->delivery = $statusPagSeguro['delivery'];
            $order_state->logable = $statusPagSeguro['logable'];
            $order_state->invoice = $statusPagSeguro['invoice'];

            if (version_compare(_PS_VERSION_, '1.5', '>')) {
                $order_state->unremovable = $statusPagSeguro['unremovable'];
                $order_state->shipped = $statusPagSeguro['shipped'];
                $order_state->paid = $statusPagSeguro['paid'];
            }

            $order_state->name = array();
            $order_state->template = array();
            $continue = false;

            foreach (Language::getLanguages(false) as $language) {

                $list_states = $this->findOrderStates($language['id_lang']);

                $continue = $this->checkIfOrderStatusExists(
                    $language['id_lang'], $statusPagSeguro['name'], $list_states
                );

                if ($continue) {
                    $order_state->name[(int) $language['id_lang']] = $statusPagSeguro['name'];
                    $order_state->template[$language['id_lang']] = $statusPagSeguro['template'];
                }

                if ($key == 'WAITING_PAYMENT' or $key == 'IN_ANALYSIS') {

                    $this->copyMailTo($statusPagSeguro['template'], $language['iso_code'], 'html');
                    $this->copyMailTo($statusPagSeguro['template'], $language['iso_code'], 'txt');
                }
            }

            if ($continue) {

                if ($order_state->add()) {

                    $file = _PS_ROOT_DIR_ . '/img/os/' . (int) $order_state->id . '.gif';
                    copy($image, $file);
                }
            }

            if ($key == 'INITIATED') {
                $name_state = $statusPagSeguro['name'];
            }
        }

        Configuration::updateValue('PS_OS_PAGSEGURO', $this->returnIdOrderByStatusPagSeguro($name_state));

        return $orders_added;
    }

    /**
     * @param $name
     * @param $lang
     * @param $ext
     */
    private function copyMailTo($name, $lang, $ext) {

        $template = _PS_MAIL_DIR_ . $lang . '/' . $name . '.' . $ext;

        if (!file_exists($template)) {

            $templateToCopy = _PS_ROOT_DIR_ . '/modules/pagseguro/mails/' . $name . '.' . $ext;
            copy($templateToCopy, $template);
        }
    }

    /**
     * @param $lang_id
     * @return mixed
     */
    private function findOrderStates($lang_id) {

        $sql = 'SELECT DISTINCT osl.`id_lang`, osl.`name`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' .
            _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`)
            WHERE osl.`id_lang` = ' . "$lang_id" . ' AND osl.`name` in ("Iniciado","Aguardando pagamento",
            "Em análise", "Paga","Disponível","Em disputa","Devolvida","Cancelada") AND os.`id_order_state` <> 6';

        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
    }

    /**
     * @param $nome_status
     * @return mixed
     */
    private function returnIdOrderByStatusPagSeguro($nome_status) {

        $isDeleted = version_compare(_PS_VERSION_, '1.5', '<') ? '' : 'WHERE deleted = 0';

        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .
            pSQL($nome_status) . '\')' . $isDeleted;

        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        return $id_order_state[0]['id_order_state'];
    }

    /**
     * @param $id_lang
     * @param $status_name
     * @param $list_states
     * @return bool
     */
    private function checkIfOrderStatusExists($id_lang, $status_name, $list_states) {

        if (Tools::isEmpty($list_states) or empty($list_states) or ! isset($list_states)) {
            return true;
        }

        $save = true;
        foreach ($list_states as $state) {

            if ($state['id_lang'] == $id_lang && $state['name'] == $status_name) {
                $save = false;
                break;
            }
        }

        return $save;
    }

    /**
     * Verify if PagSeguro log file exists.
     * Case log file not exists, try create
     * else create PagSeguro.log into PagseguroLibrary folder into module
     * @param $file
     */
    private function verifyLogFile($file) {
        $file = _PS_ROOT_DIR_ . $file;
        try {
            if (is_file($file)) {
                $handle = @fopen($file, 'a');
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function _whichVersion() {
        if (version_compare(_PS_VERSION_, '1.7.0.0', ">=")) {
            $version = '7';
        } else if (version_compare(_PS_VERSION_, '1.6.0.1', ">=") && version_compare(_PS_VERSION_, '1.7.0.0', "<")) {
            $version = '6';
        } else if (version_compare(_PS_VERSION_, '1.5.0.1', "<")) {
            $version = '4';
        } else {
            $version = '5';
        }
        return $version;
    }

    /**
     * @return mixed
     */
    private function getPrestaShopEnvironment()
    {
        return Configuration::get('PAGSEGURO_ENVIRONMENT');
    }
}
