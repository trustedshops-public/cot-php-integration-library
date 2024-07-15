<?php

declare(strict_types=1);

use TRSTD\COT\Client;
use TRSTDExampleIntegration\COT\COTAuthDBStorage;
use TRSTDExampleIntegration\COT\COTAuthRepository;
use TRSTDExampleIntegration\Form\ClientConfigurationTextDataConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class trstdexampleintegration extends Module
{
    private $cotAuthRepository;

    private $cotAuthClient;

    public function __construct()
    {
        $this->name = 'trstdcotintegration';
        $this->author = 'Trusted Shops AG';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.7.0', 'max' => _PS_VERSION_];

        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('TRSTD COT Integration');
        $this->description = $this->l('Example module for TRSTD COT Integration.');

        $this->cotAuthRepository = new COTAuthRepository();

        // Prepare required parameters for the COT Auth client
        $tsId = Configuration::get(ClientConfigurationTextDataConfiguration::CONFIG_KEY_TS_ID);
        $clientId = 'trstd-switch-' . $tsId;
        $clientSecret = Configuration::get(ClientConfigurationTextDataConfiguration::CONFIG_KEY_CLIENT_SECRET);

        // Initialize the COT Auth client
        $this->cotAuthClient = new Client($tsId, $clientId, $clientSecret, new COTAuthDBStorage());
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->registerHooks($this)) {
            return false;
        }

        $this->cotAuthRepository->install();

        return true;
    }

    public function uninstall()
    {
        $this->cotAuthRepository->uninstall();

        return parent::uninstall();
    }

    public function getContent()
    {
        $route = $this->get('router')->generate('client_configuration_form');
        Tools::redirectAdmin($route);
    }

    public function hookDisplayFooter(array $params)
    {
        // Handle the OAuth2 callback
        $this->cotAuthClient->handleCallback();

        // Get the anonymous consumer data
        $anonymousConsumerData = $this->cotAuthClient->getAnonymousConsumerData();

        $this->smarty->assign([
            'anonymousConsumerData' => $anonymousConsumerData
        ]);

        return $this->fetch('module:trstdexampleintegration/views/templates/front/consumerdata.tpl');
    }

    /**
     * Register hooks for the module.
     *
     * @param Module $module
     *
     * @return bool
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'displayFooter',
        ];

        return (bool) $module->registerHook($hooks);
    }
}
