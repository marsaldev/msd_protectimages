<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

namespace Marsaldev\Module\MsdProtectImages\Form;

use Marsaldev\Module\MsdProtectImages\Tool\ManageHtaccess;
use PrestaShop\PrestaShop\Core\Cache\Clearer\CacheClearerInterface;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProtectImagesConfigurationDataConfiguration implements DataConfigurationInterface
{

    public const MSD_PROTECT_IMAGES_ENABLED = 'MSD_PROTECT_IMAGES_ENABLED';

    private $configuration;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /** @var ManageHtaccess */
    private $manage_htaccess;

    public function __construct(ConfigurationInterface $configuration, TranslatorInterface $translator, CacheClearerInterface $cacheClearer)
    {
        $this->configuration = $configuration;
        $this->translator = $translator;
        $this->manage_htaccess = new ManageHtaccess($cacheClearer);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): array
    {
        $return = [];

        $return['msd_protect_images_enabled'] = $this->configuration->get(static::MSD_PROTECT_IMAGES_ENABLED);

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if ($this->validateConfiguration($configuration)) {
            $this->configuration->set(static::MSD_PROTECT_IMAGES_ENABLED, $configuration['msd_protect_images_enabled']);
            if ($this->configuration->get(static::MSD_PROTECT_IMAGES_ENABLED)) {
                $this->manage_htaccess->generateApacheRules();
            } else {
                if (!$this->manage_htaccess->clearApacheRules()) {
                    $errors[] = $this->translator->trans(
                        'There was an error removing the htaccess rules, please check it manually.', [],
                        'Modules.Msd_protectimages.Admin'
                    );
                }
            }
        } else {
            $errors[] = $this->translator->trans(
                'There was an error saving the setting',
                [],
                'Modules.Msd_protectimages.Admin'
            );
        }

        /* Errors are returned here. */

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function validateConfiguration(array $configuration): bool
    {
        return isset($configuration['msd_protect_images_enabled']);
    }

}