<?php


namespace Kronos\Tests\Log\Factory\Writer;


use Kronos\Log\Exception\RequiredSetting;
use Kronos\Log\Factory\Writer\FluentdWriterFactory;
use Kronos\Log\Writer\FluentdWriter;
use PHPUnit\Framework\Attributes\Test;

class FluentdWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    protected FluentdWriterFactory $factory;

    public function setUp(): void
    {
        $this->factory = new FluentdWriterFactory();
    }

    #[Test]
    public function settingsValid_buildFromArray_writerIsCreatedSuccessfully()
    {
        $validParams = [
            'hostname' => 'fluentd',
            'tag' => 'php.application',
        ];

        $retVal = $this->factory->createFromArray($validParams);

        $this->assertInstanceOf(FluentdWriter::class, $retVal);
    }

    #[Test]
    public function hostnameNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['tag' => 'php.application'];

        $this->expectException(RequiredSetting::class);

        $this->factory->createFromArray($params);
    }

    #[Test]
    public function tagNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['hostname' => 'fluentd'];

        $this->expectException(RequiredSetting::class);

        $this->factory->createFromArray($params);
    }

    #[Test]
    public function hostnameSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenHostname = 'fluentd';
        $params = [
            'hostname' => $givenHostname,
            'tag' => 'php.application'
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertSame($givenHostname, $retVal->getHostname());
    }

    #[Test]
    public function tagSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenTag = 'fluentd';
        $params = [
            'hostname' => 'php.application',
            'tag' => $givenTag
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertSame($givenTag, $retVal->getTag());
    }

    #[Test]
    public function portSet_buildFromArray_portIsSetInWriter()
    {
        $givenPort = 24220;
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'port' => $givenPort
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertSame($givenPort, $retVal->getPort());
    }

    #[Test]
    public function portUnset_buildFromArray_portIsSetToDefaults()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertSame(24224, $retVal->getPort());
    }

    #[Test]
    public function applicationSet_buildFromArray_applicationIsSetInWriter()
    {
        $givenApplication = 'testapp';
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'application' => $givenApplication,
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertSame($givenApplication, $retVal->getApplication());
    }

    #[Test]
    public function applicationUnset_buildFromArray_applicationIsSetToNullInWriter()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertNull($retVal->getApplication());
    }

    #[Test]
    public function wrapContextInMetaUnset_buildFromArray_willWrapContextInMetaReturnsFalse()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertFalse($retVal->willWrapContextInMeta());
    }

    #[Test]
    public function wrapContextInMetaSetToFalse_buildFromArray_willWrapContextInMetaReturnsFalse()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'wrapContextInMeta' => 'false',
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertFalse($retVal->willWrapContextInMeta());
    }

    #[Test]
    public function wrapContextInMetaSetToTrue_buildFromArray_willWrapContextInMetaReturnsTrue()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'wrapContextInMeta' => 'true',
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertTrue($retVal->willWrapContextInMeta());
    }

    #[Test]
    public function fluentBitToTrue_buildFromArray_getFluentBitReturnsTrue()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'fluentBit' => true
        ];

        $retVal = $this->factory->createFromArray($params);

        $this->assertTrue($retVal->getFluentBit());
    }
}
