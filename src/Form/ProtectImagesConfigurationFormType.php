<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

namespace Marsaldev\Module\MsdProtectImages\Form;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;

class ProtectImagesConfigurationFormType extends TranslatorAwareType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('msd_protect_images_enabled', SwitchType::class, [
                'label' => $this->trans('Enable or disable the protection of source images', 'Modules.Msd_protectimages.Admin'),
            ]);
    }

}