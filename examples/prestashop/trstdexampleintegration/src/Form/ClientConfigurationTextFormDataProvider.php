<?php

declare(strict_types=1);

namespace TRSTDExampleIntegration\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

/**
 * Provider is responsible for providing form data, in this case, it is returned from the configuration component.
 *
 * Class ClientConfigurationTextFormDataProvider
 */
class ClientConfigurationTextFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var DataConfigurationInterface
     */
    private $clientConfigurationTextDataConfiguration;

    public function __construct(DataConfigurationInterface $clientConfigurationTextDataConfiguration)
    {
        $this->clientConfigurationTextDataConfiguration = $clientConfigurationTextDataConfiguration;
    }

    public function getData(): array
    {
        return $this->clientConfigurationTextDataConfiguration->getConfiguration();
    }

    public function setData(array $data): array
    {
        return $this->clientConfigurationTextDataConfiguration->updateConfiguration($data);
    }
}
