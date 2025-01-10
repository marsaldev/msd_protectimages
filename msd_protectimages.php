<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Msd_ProtectImages extends Module
{

    public function __construct()
    {
        $this->name = 'msd_protectimages';
        $this->author = 'Marco Salvatore';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans(
                'Protect source product images',
                [],
                'Modules.Msd_protectimages.Admin'
            ).' - by Marco Salvatore (marsaldev)';
        $this->confirmUninstall = $this->trans(
            'Are you sure you want to uninstall this module?',
            [],
            'Modules.Msd_protectimages.Admin'
        );

        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => _PS_VERSION_];
    }

    public function getContent()
    {
        $route = $this->get('router')->generate('admin_msdprotectimages_configuration_controller');
        Tools::redirectAdmin($route);
    }

}