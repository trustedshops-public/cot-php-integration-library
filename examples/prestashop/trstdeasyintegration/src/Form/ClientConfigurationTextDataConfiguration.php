<?php

declare(strict_types=1);

namespace TRSTDExampleIntegration\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

/**
 * Configuration is used to save data to configuration table and retrieve from it.
 */
final class ClientConfigurationTextDataConfiguration implements DataConfigurationInterface
{
    public const CONFIG_KEY_TS_ID = 'COT_TS_ID';
    public const CONFIG_KEY_CLIENT_SECRET = 'COT_CLIENT_SECRET';
    public const CONFIG_MAXLENGTH = 64;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $return = [];

        $return[static::CONFIG_KEY_TS_ID] = $this->configuration->get(static::CONFIG_KEY_TS_ID);
        $return[static::CONFIG_KEY_CLIENT_SECRET] = $this->configuration->get(static::CONFIG_KEY_CLIENT_SECRET);
        $return[static::CONFIG_KEY_DEMO_VIEW] = $this->configuration->get(static::CONFIG_KEY_DEMO_VIEW);

        return $return;
    }

    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if ($this->validateConfiguration($configuration)) {
            if (strlen($configuration[static::CONFIG_KEY_TS_ID]) <= static::CONFIG_MAXLENGTH) {
                $this->configuration->set(static::CONFIG_KEY_TS_ID, $configuration[static::CONFIG_KEY_TS_ID]);
            } else {
                $errors[] = 'CONFIG_KEY_TS_ID value is too long';
            }

            if (strlen($configuration[static::CONFIG_KEY_CLIENT_SECRET]) <= static::CONFIG_MAXLENGTH) {
                $this->configuration->set(static::CONFIG_KEY_CLIENT_SECRET, $configuration[static::CONFIG_KEY_CLIENT_SECRET]);
            } else {
                $errors[] = 'CONFIG_KEY_CLIENT_SECRET value is too long';
            }
        }

        /* Errors are returned here. */
        return $errors;
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        return isset($configuration[static::CONFIG_KEY_TS_ID]) && isset($configuration[static::CONFIG_KEY_CLIENT_SECRET]);
    }
}
