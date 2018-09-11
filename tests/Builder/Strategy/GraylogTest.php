<?php


namespace Kronos\Tests\Log\Builder\Strategy;


use Kronos\Log\Builder\Strategy\Graylog;
use Kronos\Log\Exception\RequiredSetting;

class GraylogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Graylog
     */
    protected $strategy;

    public function setUp()
    {
        $this->strategy = new Graylog();
    }

    public function test_settingsValid_buildFromArray_writerIsCreatedSuccessfully()
    {
        $validParams = [
            'hostname' => '127.0.0.1',
            'chunkSize' => 8096,
        ];

        $retVal = $this->strategy->buildFromArray($validParams);

        $this->assertInstanceOf(\Kronos\Log\Writer\Graylog::class, $retVal);
    }

    public function test_hostnameNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['chunkSize' => 8096];

        $this->expectException(RequiredSetting::class);

        $this->strategy->buildFromArray($params);
    }

    public function test_chunkSizeNotSet_buildFromArray_throwsRequiredSettingException()
    {
        $params = ['hostname' => '127.0.0.1'];

        $this->expectException(RequiredSetting::class);

        $this->strategy->buildFromArray($params);
    }

    public function test_hostnameSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenHostname = '4.4.2.2';
        $params = ['chunkSize' => 8096, 'hostname' => $givenHostname];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenHostname, $retVal->getHostname());
    }

    public function test_chunkSizeSet_buildFromArray_hostnameIsSetInWriter()
    {
        $givenChunkSize = 8096;
        $params = ['chunkSize' => $givenChunkSize, 'hostname' => '4.4.2.2'];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenChunkSize, $retVal->getChunkSize());
    }

    public function test_portSet_buildFromArray_portIsSetInWriter()
    {
        $givenPort = 12203;
        $params = ['chunkSize' => 8096, 'hostname' => '4.4.2.2', 'port' => $givenPort];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenPort, $retVal->getPort());
    }

    public function test_applicationSet_buildFromArray_applicationIsSetInWriter()
    {
        $givenApp = 'myapp';
        $params = ['chunkSize' => 8096, 'hostname' => '4.4.2.2', 'application' => $givenApp];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame($givenApp, $retVal->getApplication());
    }

    public function test_portUnset_buildFromArray_portDefaultsTo12202InWriter()
    {
        $params = ['chunkSize' => 8096, 'hostname' => '4.4.2.2'];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame(12202, $retVal->getPort());
    }

    public function test_applicationUnset_buildFromArray_applicationDefaultsToNullInWriter()
    {
        $params = ['chunkSize' => 8096, 'hostname' => '4.4.2.2'];

        $retVal = $this->strategy->buildFromArray($params);

        $this->assertSame(null, $retVal->getApplication());
    }
}