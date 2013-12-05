<?php
/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once (dirname(__FILE__) . '/PagSeguroLibrary/PagSeguroLibrary.php');
include_once (dirname(__FILE__) . '/module_configuration/pagseguro_modulo_util.php');
include_once (dirname(__FILE__) . '/module_configuration/pagseguro_modulo_14.php');
include_once (dirname(__FILE__) . '/module_configuration/pagseguro_modulo_15.php');

if (! defined('_PS_VERSION_')) {
    exit();
}

class PagSeguro extends PaymentModule
{

    private $modulo;

    protected $errors = array();

    private $html;

    public $context;

    public function __construct()
    {
        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '1.6';
        $this->author = 'PagSeguro Internet LTDA.';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        parent::__construct();
        
        $this->displayName = $this->l('PagSeguro');
        $this->description = $this->l('Receba pagamentos por cartão de crédito, transferência bancária e boleto.');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo ?');
        
        if (version_compare(_PS_VERSION_, '1.5.0.3', '<=')) {
            include_once (dirname(__FILE__) . '/backward_compatibility/backward.php');
        }
        
        $this->setModulo(
            version_compare(_PS_VERSION_, '1.5.0.3', '<') ?
            new PagSeguroModulo14() :
            new PagSeguroModulo15()
        );
    }

    /**
     * Perform instalation of PagSeguro module
     *
     * @return boolean
     */
    public function install()
    {
        if (version_compare(PagSeguroLibrary::getVersion(), '2.1.8', '<=')) {
            if (! $this->validatePagSeguroRequirements()) {
                return false;
            }
        }
        
        if (! $this->generatePagSeguroOrderStatus()) {
            return false;
        }
        
        if (! $this->createTables()) {
            return false;
        }
        
        if (! $this->getModulo()->install()) {
            return false;
        }
        
        if (! parent::install()
        or ! $this->registerHook('payment')
        or ! $this->registerHook('paymentReturn')
        or ! Configuration::updateValue('PAGSEGURO_EMAIL', '')
        or ! Configuration::updateValue('PAGSEGURO_TOKEN', '')
        or ! Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '')
        or ! Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', '')
        or ! Configuration::updateValue('PAGSEGURO_CHARSET', PagSeguroConfig::getData('application', 'charset'))
        or ! Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', PagSeguroConfig::getData('log', 'active'))
        or ! Configuration::updateValue(
            'PAGSEGURO_LOG_FILELOCATION',
            PagSeguroConfig::getData('log', 'fileLocation')
        )
        ) {
                return false;
        }
        return true;
    }

    /**
     * Perform uninstalation of PagSeguro module
     *
     * @return boolean
     */
    public function uninstall()
    {
        if (! $this->getModulo()->uninstall()) {
            return false;
        }
        
        if (! Configuration::deleteByName('PAGSEGURO_EMAIL')
        or ! Configuration::deleteByName('PAGSEGURO_TOKEN')
        or ! Configuration::deleteByName('PAGSEGURO_URL_REDIRECT')
        or ! Configuration::deleteByName('PAGSEGURO_NOTIFICATION_URL')
        or ! Configuration::deleteByName('PAGSEGURO_CHARSET')
        or ! Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE')
        or ! Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION')
        or ! Configuration::deleteByName('PS_OS_PAGSEGURO')
        or ! parent::uninstall()) {
            return false;
        }
        
        return true;
    }

    /**
     * Gets configuration view content
     *
     * @return string
     */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            $this->postValidation();
            
            if (! count($this->errors)) {
                $this->postProcess();
            } else {
                foreach ($this->errors as $error) {
                    $this->html .= '<div class="alert error">' . $error . '</div>';
                }
            }
        }
        
        $currency = self::returnIdCurrency();
        
        /* Currency validation */
        if (! $currency) {
            $this->html .= '<div class="alert warn">' . $this->missedCurrencyMessage() . '</div>';
        }
        
        $this->html .= $this->displayForm();
        
        return $this->html;
    }

    private function displayForm()
    {
        global $smarty;
        
        $smarty->assign('module_dir', _PS_MODULE_DIR_ . 'pagseguro/');
        $smarty->assign('action_post', Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']));
        $smarty->assign('email_user', Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')));
        $smarty->assign('token_user', Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')));
        $smarty->assign('redirect_url', $this->getDefaultRedirectionUrl());
        $smarty->assign('notification_url', $this->getNotificationUrl());
        $smarty->assign('charset_options', PagSeguroModuloUtil::getCharsetOptions());
        $smarty->assign(
            'charset_selected',
            array_search(
                Configuration::get('PAGSEGURO_CHARSET'),
                PagSeguroModuloUtil::getCharsetOptions()
            )
        );
        $smarty->assign('active_log', PagSeguroModuloUtil::getActiveLog());
        $smarty->assign('log_selected', Configuration::get('PAGSEGURO_LOG_ACTIVE'));
        $smarty->assign('diretorio_log', Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')));
        $smarty->assign('checkActiveSlide', Tools::safeOutput($this->checkActiveSlide()));
        $smarty->assign('css_version', $this->getCssDisplay());
        $smarty->assign('js_behavior_version', $this->getJsBehavior());
        return $this->display(__PS_BASE_URI__ . 'modules/pagseguro', 'admin_pagseguro.tpl');
    }

    /**
     * Realize post validations according with PagSeguro standards
     * case any inconsistence, an item is added to $_postErrors
     */
    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            $email = Tools::getValue('pagseguro_email');
            $token = Tools::getValue('pagseguro_token');
            $pagseguro_url_redirect = Tools::getValue('pagseguro_url_redirect');
            $pagseguro_notification_url = Tools::getValue('pagseguro_notification_url');
            $charset = Tools::getValue('pagseguro_charset');
            $pagseguro_log = Tools::getValue('pagseguro_log');
            
            /* E-mail validation */
            if (! $email) {
                $this->errors[] = $this->errorMessage('E-MAIL');
            } elseif (Tools::strlen($email) > 60) {
                $this->errors[] = $this->invalidFieldSizeMessage('E-MAIL');
            } elseif (! Validate::isEmail($email)) {
                $this->errors[] = $this->invalidMailMessage('E-MAIL');
            }
            
            /* Token validation */
            if (! $token) {
                $this->errors[] = $this->errorMessage('TOKEN');
            } elseif (strlen($token) != 32) {
                $this->errors[] = $this->invalidFieldSizeMessage('TOKEN');
            }
            
            /* URL redirect validation */
            if ($pagseguro_url_redirect && !
            filter_var($pagseguro_url_redirect, FILTER_VALIDATE_URL)) {
                $this->errors[] = $this->invalidUrl('URL DE REDIRECIONAMENTO');
            }
            
            /* Notification url validation */
            if ($pagseguro_notification_url && !
            filter_var($pagseguro_notification_url, FILTER_VALIDATE_URL)) {
                $this->errors[] = $this->invalidUrl('URL DE NOTIFICAÇÃO');
            }
            
            /* Charset validation */
            if (! array_key_exists($charset, PagSeguroModuloUtil::getCharsetOptions())) {
                $this->errors[] = $this->invalidValue('CHARSET');
            }
            
            /* Log validation */
            if (! array_key_exists($pagseguro_log, PagSeguroModuloUtil::getActiveLog())) {
                $this->errors[] = $this->invalidValue('LOG');
            }
        }
    }

    /**
     * Realize PagSeguro database keys values
     */
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            $charsets = PagSeguroModuloUtil::getCharsetOptions();
            
            Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguro_email'));
            Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguro_token'));
            Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguro_url_redirect'));
            Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', Tools::getValue('pagseguro_notification_url'));
            Configuration::updateValue('PAGSEGURO_CHARSET', $charsets[Tools::getValue('pagseguro_charset')]);
            Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguro_log'));
            Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguro_log_dir'));
            
            /* Verify if log file exists, case not try create */
            if (Tools::getValue('pagseguro_log')) {
                $this->verifyLogFile(Tools::getValue('pagseguro_log_dir'));
            }
        }
        $this->html .= '<div class="conf confirm">' . $this->l('Dados atualizados com sucesso') . '</div>';
    }

    /**
     * Create error messages
     *
     * @param String $field            
     * @return String
     */
    private function errorMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve ser informado.'), $field);
    }

    /**
     * Create error currency messages
     *
     * @return String
     */
    private function missedCurrencyMessage()
    {
        return sprintf(
            $this->l(
                'Verifique se a moeda <strong>REAL</strong> esta instalada e ativada.
                Para importar a moeda vá em Localização e importe "Brazil" no Pacote de Localização,
                após isso, vá em localização, moedas, e habilite o <strong>REAL</strong>.
                <br>O PagSeguro aceita apenas BRL (Real) como moeda de pagamento.'
            )
        );
    }

    /**
     * Create invalid mail messages
     *
     * @param String $field            
     * @return String
     */
    private function invalidMailMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve ser conter um email válido.'), $field);
    }

    /**
     * Create invalid field size messages
     *
     * @param String $field            
     * @return String
     */
    private function invalidFieldSizeMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> está com um tamanho inválido'), $field);
    }

    /**
     * Create invalid value messages
     *
     * @param String $field            
     * @return String
     */
    private function invalidValue($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> contém um valor inválido.'), $field);
    }

    /**
     * Create invalid url messages
     *
     * @param String $field            
     * @return String
     */
    private function invalidUrl($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve conter uma url válida.'), $field);
    }

    private function checkActiveSlide()
    {
        return Tools::getValue('activeslide') ? Tools::getValue('activeslide') : 1;
    }

    /**
     * Return Id currency (Standard value is BRL)
     *
     * @param type $value            
     * @return type
     */
    public static function returnIdCurrency($value = 'BRL')
    {
        $sql = 'SELECT `id_currency`
        FROM `' . _DB_PREFIX_ . 'currency`
        WHERE `deleted` = 0 
        AND `iso_code` = "' . $value . '"';
        
        $id_currency = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        return empty($id_currency) ? 0 : $id_currency[0]['id_currency'];
    }

    /**
     * Perform Payment hook
     *
     * @param array $params            
     * @return string
     */
    public function hookPayment($params)
    {
        if (! $this->active) {
            return;
        }
        
        return $this->getModulo()->hookPayment($params);
    }

    /**
     * Perform Payment Return hook
     *
     * @param array $params            
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        return $this->getModulo()->hookPaymentReturn($params);
    }

    private function validatePagSeguroRequirements()
    {
        $condional = true;
        
        foreach (PagSeguroConfig::validateRequirements() as $value) {
            if (! Tools::isEmpty($value)) {
                $condional = false;
                $this->errors[] = Tools::displayError($value);
            }
        }
        
        if (! $condional) {
            $this->html = $this->displayError(implode('<br />', $this->errors));
        }
        
        return $condional;
    }

    private function createTables()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagseguro_order` (
            `id` int(11) unsigned NOT NULL auto_increment,
            `id_transaction` varchar(255) NOT NULL,
            `id_order` int(10) unsigned NOT NULL ,
            PRIMARY KEY  (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ .
             ' DEFAULT CHARSET=utf8  auto_increment=1;';
        
        if (! Db::getInstance()->Execute($sql)) {
            return false;
        }
        return true;
    }

    private function generatePagSeguroOrderStatus()
    {
        $orders_added = true;
        $name_state = null;
        $image = _PS_ROOT_DIR_ . '/modules/pagseguro/logo.gif';
        
        foreach (PagSeguroModuloUtil::getCustomOrderStatusPagSeguro() as $key => $statusPagSeguro) {
            
            $order_state = new OrderState();
            $order_state->module_name = 'pagseguro';
            $order_state->send_email = $statusPagSeguro['send_email'];
            $order_state->color = '#95D061';
            $order_state->hidden = $statusPagSeguro['hidden'];
            $order_state->delivery = $statusPagSeguro['delivery'];
            $order_state->logable = $statusPagSeguro['logable'];
            $order_state->invoice = $statusPagSeguro['invoice'];
            
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
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
                    $language['id_lang'],
                    $statusPagSeguro['name'],
                    $list_states
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
            
            if ($key == 'WAITING_PAYMENT') {
                $name_state = $statusPagSeguro['name'];
            }
        }
        
        Configuration::updateValue('PS_OS_PAGSEGURO', $this->returnIdOrderByStatusPagSeguro($name_state));
        
        return $orders_added;
    }

    private function copyMailTo($name, $lang, $ext)
    {
        $template = _PS_MAIL_DIR_ . $lang . '/' . $name . '.' . $ext;
        
        if (! file_exists($template)) {
            $templateToCopy = _PS_ROOT_DIR_ . '/modules/pagseguro/mails/' . $name . '.' . $ext;
            copy($templateToCopy, $template);
        }
    }

    private function findOrderStates($lang_id)
    {
        $sql = 'SELECT DISTINCT osl.`id_lang`, osl.`name`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' .
             _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`)
            WHERE osl.`id_lang` = ' . "$lang_id" . ' AND osl.`name` in ("Iniciado","Aguardando pagamento",
            "Em análise", "Paga","Disponível","Em disputa","Devolvida","Cancelada") AND os.`id_order_state` <> 6';
        
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
    }

    private function returnIdOrderByStatusPagSeguro($nome_status)
    {
        $isDeleted = version_compare(_PS_VERSION_, '1.5.0.3', '<=') ? '' : 'WHERE deleted = 0';
        
        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .
             pSQL($nome_status) . '\')' . $isDeleted;
        
        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        
        return $id_order_state[0]['id_order_state'];
    }

    /**
     * Check if PagSeguro order status already exists on database
     *
     * @param String $status            
     * @return boolean
     */
    private function checkIfOrderStatusExists($id_lang, $status_name, $list_states)
    {
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

    public function getNotificationUrl()
    {
        return $this->getModulo()->getNotificationUrl();
    }

    /**
     * Gets a default redirection url
     *
     * @return string
     */
    public function getDefaultRedirectionUrl()
    {
        return $this->getModulo()->getDefaultRedirectionUrl();
    }

    public function getJsBehavior()
    {
        return $this->getModulo()->getJsBehavior();
    }

    public function getCssDisplay()
    {
        return $this->getModulo()->getCssDisplay();
    }

    public function getModulo()
    {
        return $this->modulo;
    }

    public function setModulo(PaymentModule $modulo)
    {
        $this->modulo = $modulo;
    }

    /**
     * Verify if PagSeguro log file exists.
     * Case log file not exists, try create
     * else create PagSeguro.log into PagseguroLibrary folder into module
     */
    private function verifyLogFile($file)
    {
        try {
            $f = @fopen(_PS_ROOT_DIR_ . $file, 'a');
            fclose($f);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
