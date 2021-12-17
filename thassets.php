<?php
/**
 * 2006-2021 THECON SRL
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * YOU ARE NOT ALLOWED TO REDISTRIBUTE OR RESELL THIS FILE OR ANY OTHER FILE
 * USED BY THIS MODULE.
 *
 * @author    THECON SRL <contact@thecon.ro>
 * @copyright 2006-2021 THECON SRL
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Thassets extends Module
{
    public function __construct()
    {
        $this->name = 'thassets';
        $this->version = '1.0.1';
        $this->author = 'Presta Maniacs';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Load CSS and JS Files on Different Pages');
        $this->description = $this->l('Don\'t let CSS or JS rules, which are only required for certain pages, load on all pages of your store.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        if (!$this->registerHooks()) {
            return false;
        }
        return true;
    }

    public function registerHooks()
    {
        if (!$this->registerHook('actionFrontControllerSetMedia') ||
            !$this->registerHook('actionAdminControllerSetMedia')) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::deleteByName($key);
        }
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $message = '';

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitThassetsModule')) == true) {
            $this->postProcess();

            if (count($this->_errors)) {
                $message = $this->displayError($this->_errors);
            } else {
                $message = $this->displayConfirmation($this->l('Successfully saved!'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$message.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitThassetsModule';
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

    public function hookActionFrontControllerSetMedia()
    {
        if (!Configuration::get('THASSETS_LIVE_MODE')) {
            return false;
        }

        if (Configuration::get('THASSETS_CSS_ALL') == 1) {
            $this->context->controller->addCSS($this->_path . 'views/css/all.css');
        }
        if (Configuration::get('THASSETS_JS_ALL') == 1) {
            $this->context->controller->addJS($this->_path . 'views/js/all.js');
        }

        if ('index' === $this->context->controller->php_self) {
            if (Configuration::get('THASSETS_CSS_HOME') == 1) {
                $this->context->controller->addCSS($this->_path . 'views/css/home.css');
            }
            if (Configuration::get('THASSETS_JS_HOME') == 1) {
                $this->context->controller->addJS($this->_path . 'views/js/home.js');
            }
        } elseif ('product' === $this->context->controller->php_self) {
            if (Configuration::get('THASSETS_CSS_PRODUCT') == 1) {
                $this->context->controller->addCSS($this->_path . 'views/css/product.css');
            }
            if (Configuration::get('THASSETS_JS_PRODUCT') == 1) {
                $this->context->controller->addJS($this->_path . 'views/js/product.js');
            }
        } elseif ('category' === $this->context->controller->php_self) {
            if (Configuration::get('THASSETS_CSS_CATEGORY') == 1) {
                $this->context->controller->addCSS($this->_path . 'views/css/category.css');
            }
            if (Configuration::get('THASSETS_JS_CATEGORY') == 1) {
                $this->context->controller->addJS($this->_path . 'views/js/category.js');
            }
        } elseif ('cms' === $this->context->controller->php_self) {
            if (Configuration::get('THASSETS_CSS_CMS') == 1) {
                $this->context->controller->addCSS($this->_path . 'views/css/cms.css');
            }
            if (Configuration::get('THASSETS_JS_CMS') == 1) {
                $this->context->controller->addJS($this->_path . 'views/js/cms.js');
            }
        }
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Module:'),
                        'name' => 'THASSETS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
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
                        'type' => 'th_title',
                        'label' => '',
                        'name' => $this->l('Load CSS Files on:'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('All Pages:'),
                        'name' => 'THASSETS_CSS_ALL',
                        'is_bool' => true,
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
                        'label' => $this->l('Homepage:'),
                        'name' => 'THASSETS_CSS_HOME',
                        'is_bool' => true,
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
                        'label' => $this->l('Product Page:'),
                        'name' => 'THASSETS_CSS_PRODUCT',
                        'is_bool' => true,
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
                        'label' => $this->l('Category Page:'),
                        'name' => 'THASSETS_CSS_CATEGORY',
                        'is_bool' => true,
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
                        'label' => $this->l('CMS Page:'),
                        'name' => 'THASSETS_CSS_CMS',
                        'is_bool' => true,
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
                        'type' => 'th_title',
                        'label' => '',
                        'name' => $this->l('Load JS Files on:'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('All Pages:'),
                        'name' => 'THASSETS_JS_ALL',
                        'is_bool' => true,
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
                        'label' => $this->l('Homepage:'),
                        'name' => 'THASSETS_JS_HOME',
                        'is_bool' => true,
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
                        'label' => $this->l('Product Page:'),
                        'name' => 'THASSETS_JS_PRODUCT',
                        'is_bool' => true,
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
                        'label' => $this->l('Category Page:'),
                        'name' => 'THASSETS_JS_CATEGORY',
                        'is_bool' => true,
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
                        'label' => $this->l('CMS Page:'),
                        'name' => 'THASSETS_JS_CMS',
                        'is_bool' => true,
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

    protected function getConfigFormValues()
    {
        $values =  array(
            'THASSETS_LIVE_MODE' => Tools::getValue('THASSETS_LIVE_MODE', Configuration::get('THASSETS_LIVE_MODE')),
            'THASSETS_CSS_ALL' => Tools::getValue('THASSETS_CSS_ALL', Configuration::get('THASSETS_CSS_ALL')),
            'THASSETS_CSS_HOME' => Tools::getValue('THASSETS_CSS_HOME', Configuration::get('THASSETS_CSS_HOME')),
            'THASSETS_CSS_PRODUCT' => Tools::getValue('THASSETS_CSS_PRODUCT', Configuration::get('THASSETS_CSS_PRODUCT')),
            'THASSETS_CSS_CATEGORY' => Tools::getValue('THASSETS_CSS_CATEGORY', Configuration::get('THASSETS_CSS_CATEGORY')),
            'THASSETS_CSS_CMS' => Tools::getValue('THASSETS_CSS_CMS', Configuration::get('THASSETS_CSS_CMS')),
            'THASSETS_JS_ALL' => Tools::getValue('THASSETS_JS_ALL', Configuration::get('THASSETS_JS_ALL')),
            'THASSETS_JS_HOME' => Tools::getValue('THASSETS_JS_HOME', Configuration::get('THASSETS_JS_HOME')),
            'THASSETS_JS_PRODUCT' => Tools::getValue('THASSETS_JS_PRODUCT', Configuration::get('THASSETS_JS_PRODUCT')),
            'THASSETS_JS_CATEGORY' => Tools::getValue('THASSETS_JS_CATEGORY', Configuration::get('THASSETS_JS_CATEGORY')),
            'THASSETS_JS_CMS' => Tools::getValue('THASSETS_JS_CMS', Configuration::get('THASSETS_JS_CMS')),
        );

        return $values;
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function getPsVersion()
    {
        $full_version = _PS_VERSION_;
        return explode(".", $full_version)[1];
    }
}
