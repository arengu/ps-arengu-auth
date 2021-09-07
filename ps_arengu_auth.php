<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\Module\Arengu\Auth\PrivateKey;
use PrestaShop\Module\Arengu\Auth\Utils;

class ps_arengu_auth extends Module
{
    public $utils;

    public $apiKey;
    public $jwtSecret;

    public $JWT_EXPIRY = 300;
    public $JWT_ALG = 'HS256';
    public $JWT_NAMESPACE = 'https://jwt.arengu.com';

    public function __construct()
    {
        $this->name = 'ps_arengu_auth';
        $this->tab = 'front_office_features';
        $this->version = '{{ VERSION }}';
        $this->author = 'Arengu';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.2.0'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Arengu Auth');
        $this->description = $this->l('Enable custom signup, login and passwordless endpoints to interact with your store authentication system from Arengu.');

        $this->confirmUninstall = $this->l(
            'Are you sure you want to uninstall?'
        );

        $this->utils = new Utils();

        $this->apiKey = new PrivateKey('ARENGU_API_KEY');
        $this->jwtSecret = new PrivateKey('ARENGU_JWT_SECRET');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->apiKey->renew() || !$this->jwtSecret->renew()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->apiKey->delete();
        $this->jwtSecret->delete();

        return true;
    }

    public function getContent()
    {
        $renewApiKey = Tools::isSubmit('renew_api_key');
        $renweJwtSecret = Tools::isSubmit('renew_jwt_secret');

        if ($renewApiKey || $renweJwtSecret) {
            if ($renewApiKey) {
                $this->apiKey->renew();
            }

            if ($renweJwtSecret) {
                $this->jwtSecret->renew();
            }

            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminModules') .
                "&configure={$this->name}"
            );
        }

        return $this->renderForm();
    }

    private function renderForm()
    {
        $output = '';

        $fields[]['form'] = [
            'legend' => [
                'title' => $this->l('API settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Base URL:'),
                    'name' => 'field_base_url',
                    'readonly' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('API key:'),
                    'name' => 'field_current_api_key',
                    'readonly' => true,
                ],
            ],
            'warning' => $this->l('This key protects the endpoints from misuse.') .
                ' <b>' . $this->l('It allows to impersonate any customer, so you must keep it secret.') . '</b> ' .
                $this->l('Renewing it will invalidate all the previous ones.') .
                ' <b>' . $this->l('Make sure you know what you are doing.') . '</b>',
            'submit' => [
                'title' => $this->l('Renew API key'),
                'class' => 'btn btn-danger pull-right',
                'icon' => 'icon-warning-sign',
                'name' => 'renew_api_key',
            ],
        ];

        $fields[]['form'] = [
            'legend' => [
                'title' => $this->l('JWT settings'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('JWT secret:'),
                    'name' => 'field_current_jwt_secret',
                    'readonly' => true,
                ],
            ],
            'warning' => $this->l('This secret is used to generate and verify login tokens.') .
            ' <b>' . $this->l('It allows to impersonate any customer, so you must keep it secret.') . '</b> ' .
            $this->l('Renewing it will invalidate all the previous ones.') .
            ' <b>' . $this->l('Make sure you know what you are doing.') . '</b>',
            'submit' => [
                'title' => $this->l('Renew JWT secret'),
                'class' => 'btn btn-danger pull-right',
                'icon' => 'icon-warning-sign',
                'name' => 'renew_jwt_secret',
            ],
        ];

        $helper = new HelperForm();

        $helper->fields_value = [
            'field_current_api_key' => $this->apiKey->get(),
            'field_current_jwt_secret' => $this->jwtSecret->get(),
            'field_base_url' => Tools::getShopDomainSsl(true),
        ];

        $output .= $helper->generateForm($fields);

        $output .=
            '<script>
                $(function() {
                    $("button[name=renew_api_key],button[name=renew_jwt_secret]").click(function(e) {
                        confirm("' .
                            $this->l('Are you sure you want to renew this key? This is not reversible and will invalidate previous keys.') .
                        '") || e.preventDefault();
                    });
                });
            </script>';

        return $output;
    }
}
