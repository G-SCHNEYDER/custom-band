<?php
/**
* 2007-2023 PrestaShop
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
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Custom_Band extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'custom_band';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'G_SCHNEYDER';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bandeau d\information customisable');
        $this->description = $this->l('Affichage de bandeaux d\'informations customisables sur votre boutique');

        $this->confirmUninstall = $this->l('Etes-vous sûr de vouloir supprimer ce module ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }


    public function install()
    {
        Configuration::updateValue('CUSTOM_BAND__LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayTop');
            Configuration::updateValue('CUSTOM_BAND_TEXT', '');
            Configuration::updateValue('CUSTOM_BAND_BG_COLOR', '');
            Configuration::updateValue('CUSTOM_BAND_TEXT_COLOR', '');
            Configuration::updateValue('CUSTOM_BAND_AFFICHAGE_KDO', '');
    }

    public function uninstall()
    {
        Configuration::deleteByName('CUSTOM_BAND_AFFICHAGE_KDO');
        Configuration::deleteByName('CUSTOM_BAND_TEXT');
        Configuration::deleteByName('CUSTOM_BAND_BG_COLOR');
        Configuration::deleteByName('CUSTOM_BAND_TEXT_COLOR');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitcustom_band_Module')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign([
            'module_dir' => $this->_path,
        ]);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration page.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitcustom_band_Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Configuration du bandeau'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 8,
                        'type' => 'text',
                        'desc' => $this->l('Entrez le texte de la bannière'),
                        'name' => 'CUSTOM_BAND_TEXT',
                        'label' => $this->l('Texte du bandeau'),
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'CUSTOM_BAND_TEXT_COLOR',
                        'desc' => $this->l('Sélectionnez la couleur de votre texte'),
                        'label' => $this->l('Couleur de texte'),
                    ),
                    array(
                        'type' => 'color',
                        'name' => 'CUSTOM_BAND_BG_COLOR',
                        'desc' => $this->l('Sélectionnez la couleur de la bannière'),
                        'label' => $this->l('Couleur de fond'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activer l\'émoji cadeau'),
                        'name' => 'CUSTOM_BAND_AFFICHAGE_KDO',
                        'is_bool' => true,
                        'desc' => $this->l('Permet l\'activation de l\'affichage d\'un émoji cadeau à la fin de la phrase'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activer le défilement du texte'),
                        'name' => 'CUSTOM_BAND_DEFILEMENT',
                        'is_bool' => true,
                        'desc' => $this->l('Permet l\'activation d\'un effet de défilement horizontal du texte'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CUSTOM_BAND_AFFICHAGE_KDO' => Configuration::get('CUSTOM_BAND_AFFICHAGE_KDO', true),
            'CUSTOM_BAND_DEFILEMENT' => Configuration::get('CUSTOM_BAND_DEFILEMENT', true),
            'CUSTOM_BAND_TEXT' => Configuration::get('CUSTOM_BAND_TEXT'),
            'CUSTOM_BAND_BG_COLOR' => Configuration::get('CUSTOM_BAND_BG_COLOR', null),
            'CUSTOM_BAND_TEXT_COLOR' => Configuration::get('CUSTOM_BAND_TEXT_COLOR', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayTop()
    {
        $this->context->smarty->assign([
            'banner_allow_kdo' => Configuration::get('CUSTOM_BAND_AFFICHAGE_KDO'),
            'banner_color' => Configuration::get('CUSTOM_BAND_BG_COLOR'),
            'banner_message' => Configuration::get('CUSTOM_BAND_TEXT'),
            'banner_text_color' => Configuration::get('CUSTOM_BAND_TEXT_COLOR'),
            'banner_defilement' => Configuration::get('CUSTOM_BAND_DEFILEMENT'),
        ]);

        return $this->context->smarty->fetch($this->local_path.'views/templates/front/bandeau.tpl');
    }

    /**
     * Function for displaying a widget
     * Example of widget {widget name='mymodule' text_to_display='Welcome new customer'}
     */
    public function renderWidget($hookName, array $configuration)
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch('module:'.$this->name.'/views/templates/front/bandeau.tpl');
    }

    /**
     * Function to fill the widget variables
     */
    public function getWidgetVariables($hookName, array $configuration)
    {
        $myParamKey = $configuration['text_to_display'] ?? null;
        
        return [
            'my_var1' => 'my_var1_value',
            'my_var2' => 'my_var2_value',
            'my_var_n' => 'my_var_n_value',
            'my_dynamic_var_by_param' => $this->getMyDynamicVarByParamKey($myParamKey),
        ];
    }
}
