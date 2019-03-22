<?php


namespace Kronos\Tests\Log\Builder\Strategy;


use Kronos\Log\Builder\Strategy\Fluentd;
use Kronos\Log\Exception\RequiredSetting;

class FluentdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Fluentd
     */
    protected $strategy;

    public function setUp(): void
    {
        $this->strategy = new Fluentd();
    }

    public function test_settingsValid_buildFromArray_writerIsCreatedSuccessfully()
    {
        $validParams = [
            'hostname' => 'fluentd',
            'tag' => 'php.application',
        ];

        $retVal = $this->strategy->buildFromArray($validParams);

        $this->assertInstanceOf(\Kronos\Log\Writer\Fluentd::class, $retVal);
    }

    public function test_hostnameNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['tag' => 'php.application'];

        $this->expectException(RequiredSetting::class);

        $this->strategy->buildFromArray($params);
    }

    public function test_tagNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['hostname' => 'fluentd'];

        $this->expectException(RequiredSetting::class);

        $this->strategy->buildFromArray($params);
    }

    public function test_hostnameSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenHostname = 'fluentd';
        $params = [
            'hostname' => $givenHostname,
            'tag' => 'php.application'
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenHostname, $retVal->getHostname());
    }

    public function test_tagSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenTag = 'fluentd';
        $params = [
            'hostname' => 'php.application',
            'tag' => $givenTag
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenTag, $retVal->getTag());
    }

    public function test_portSet_buildFromArray_portIsSetInWriter()
    {
        $givenPort = 24220;
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'port' => $givenPort
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenPort, $retVal->getPort());
    }

    public function test_portUnset_buildFromArray_portIsSetToDefaults()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame(24224, $retVal->getPort());
    }

    public function test_applicationSet_buildFromArray_applicationIsSetInWriter()
    {
        $givenApplication = 'testapp';
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'application' => $givenApplication,
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenApplication, $retVal->getApplication());
    }

    public function test_applicationUnset_buildFromArray_applicationIsSetToNullInWriter()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertNull($retVal->getApplication());
    }

    public function test_wrapContextInMetaUnset_buildFromArray_willWrapContextInMetaReturnsFalse()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertFalse($retVal->willWrapContextInMeta());
    }

    public function test_wrapContextInMetaSetToFalse_buildFromArray_willWrapContextInMetaReturnsFalse()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'wrapContextInMeta' => 'false',
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertFalse($retVal->willWrapContextInMeta());
    }

    public function test_wrapContextInMetaSetToTrue_buildFromArray_willWrapContextInMetaReturnsTrue()
    {
        $params = [
            'hostname' => 'php.application',
            'tag' => 'php.application',
            'wrapContextInMeta' => 'true',
        ];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertTrue($retVal->willWrapContextInMeta());
    }
}
