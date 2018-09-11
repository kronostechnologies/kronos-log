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
}