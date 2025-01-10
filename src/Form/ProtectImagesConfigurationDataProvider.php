<?php
/**
 * @author Marco Salvatore (marsaldev)
 * @license MIT License
 */

namespace Marsaldev\Module\MsdProtectImages\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class ProtectImagesConfigurationDataProvider implements FormDataProviderInterface
{
    /** @var \PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface  */
    private $protectImagesConfigurationDataConfiguration;

    public function __construct(DataConfigurationInterface $protectImagesConfigurationDataConfiguration)
    {
        $this->protectImagesConfigurationDataConfiguration = $protectImagesConfigurationDataConfiguration;
    }

    public function getData(): array
    {
        return $this->protectImagesConfigurationDataConfiguration->getConfiguration();
    }

    public function setData(array $data): array
    {
        return $this->protectImagesConfigurationDataConfiguration->updateConfiguration($data);
    }
}