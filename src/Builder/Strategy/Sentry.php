<?php

namespace Kronos\Log\Builder\Strategy;

use Kronos\Log\Builder\Strategy;
use Kronos\Log\Exception\InvalidSetting;
use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer As WriterFactory;
use Override;
use SebastianBergmann\GlobalState\RuntimeException;
use Sentry\Client;
use Sentry\ClientInterface;

class Sentry extends AbstractWriter
{

    const CLIENT = 'client';

    const KEY = 'key';
    const PROJECT_ID = 'projectId';
    const OPTIONS = 'options';

    private WriterFactory $factory;

    public function __construct(?WriterFactory $factory = null)
    {
        $this->factory = is_null($factory) ? new WriterFactory() : $factory;
    }

    /**
     * @param array $settings
     * @return \Kronos\Log\Writer\Sentry
     * @throws InvalidSetting
     * @throws RequiredSetting
     */
    #[Override]
    public function buildFromArray(array $settings)
    {
        if (isset($settings[self::CLIENT]) && $settings[self::CLIENT]) {
            if ($settings[self::CLIENT] instanceof ClientInterface) {
                $writer = $this->factory->createSentryWriter($settings[self::CLIENT]);
            } else {
                throw new InvalidSetting(self::CLIENT . ' setting must be an instance of Sentry Client, instance of ' . get_class($settings[self::CLIENT]) . ' given');
            }
        } elseif (isset($settings[self::KEY])) {
            if (!isset($settings[self::PROJECT_ID])) {
                throw new RequiredSetting(self::PROJECT_ID . ' setting is required with ' . self::KEY);
            } else {
                $writer = $this->factory->createSentryWriterAndSentryClient(
                    $settings[self::KEY],
                    $settings[self::PROJECT_ID],
                    $this->getOptions($settings)
                );
            }
        } else {
            throw new RequiredSetting(self::CLIENT . ' setting or ' . self::KEY . ' setting must given');
        }

        $this->setCommonSettings($writer, $settings);

        return $writer;
    }

    /**
     * @param array $settings
     * @return array
     */
    private function getOptions(array $settings)
    {
        return isset($settings[self::OPTIONS]) ? $settings[self::OPTIONS] : [];
    }
}
