<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

namespace Marsaldev\Module\MsdProtectImages\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends FrameworkBundleAdminController
{
    public function configurationAction(Request $request)
    {
        $configurationFormDataHandler = $this->get(
            'marsaldev.module.protectimages.form.protect_images_form_data_handler'
        );
        $configurationForm = $configurationFormDataHandler->getForm();
        $configurationForm->handleRequest($request);

        if ($configurationForm->isSubmitted() && $configurationForm->isValid()) {
            /** You can return array of errors in form handler and they can be displayed to user with flashErrors */
            $errors = $configurationFormDataHandler->save($configurationForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('admin_msdprotectimages_configuration_controller');
            }

            $this->flashErrors($errors);
        }

        return $this->render(
            '@Modules/msd_protectimages/views/templates/admin/controller/admin_configuration.html.twig',
            [
                'protectimagesConfigurationForm' => $configurationForm->createView(),
                'layoutTitle' => $this->trans('Protect product source images', 'Modules.Msd_protectimages.Admin'),
            ]
        );
    }

}