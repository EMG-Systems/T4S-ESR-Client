<?php

namespace EmgSystems\Train4Sustain\Request;

use PHPUnit\Framework\TestCase;

function curl_init($url)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return 'handle';
}

function curl_setopt($handle, $option, $value)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return true;
}

function curl_setopt_array($handle, $options)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return true;
}

function curl_exec($handle)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return 'curl result';
}

function curl_errno($handle)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return 0;
}

function curl_error($handle)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    return 'curl error message';
}

function curl_getinfo($handle, $option = null)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
    if ($option > 0) {
        return 'curlInfo';
    }
    return ['allCurlInfo'];
}

function curl_close($handle)
{
    CurlTest::$callLog[] = [__FUNCTION__, func_get_args()];
}

/**
 * Unit test for the Curl adapter.
 *
 * @package emg-systems/t4s-api-client
 * @covers  \EmgSystems\Train4Sustain\Request\Curl
 */
class CurlTest extends TestCase
{
    public static array $callLog = [];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testIsAvailable()
    {
        $this->assertTrue(Curl::isAvailable());
    }

    /**
     * @throws CurlException
     */
    public function testCurlOperations()
    {
        self::$callLog = [];
        $curl = new Curl();
        $curl->init();
        $curl->setOption(CURLOPT_URL, 'test');
        $curl->setOptions([1 => 'b', 3 => 'a']);
        $curl->exec();
        $this->assertEquals('curl error message', $curl->errorMessage());
        $this->assertNull($curl->getInfo(0));
        $this->assertEquals('curlInfo', $curl->getInfo(1));
        $this->assertEquals(['allCurlInfo'], $curl->getAllInfo());
        $curl->close();

        $this->assertEquals([
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_init',
                [null]
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_setopt',
                ['handle', CURLOPT_URL, 'test']
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_setopt_array',
                ['handle', [1 => 'b', 3 => 'a']]
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_exec',
                ['handle']
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_errno',
                ['handle']
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_error',
                ['handle']
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_getinfo',
                ['handle', 1]
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_getinfo',
                ['handle']
            ],
            [
                'EmgSystems\\Train4Sustain\\Request\\curl_close',
                ['handle']
            ]
        ], self::$callLog);
    }

    /**
     * @throws CurlException
     */
    public function testCurlError()
    {
        $curl = $this->getMockBuilder(Curl::class)->onlyMethods(['errorNumber'])->getMock();
        $curl->expects($this->once())->method('errorNumber')->willReturn(1);

        $this->expectException(CurlException::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('curl error message');

        $curl->exec();
    }

    /**
     * @throws CurlException
     */
    public function testResponseRetrieval()
    {
        $curl = $this->getMockBuilder(Curl::class)->onlyMethods(['getAndValidateResponse', 'getInfo'])->getMock();
        $curl->expects($this->once())->method('getAndValidateResponse')->willReturnCallback(function () {
            return 'headerbody';
        });
        $curl->expects($this->exactly(2))->method('getInfo')->with(CURLINFO_HEADER_SIZE)->willReturn((string)strlen('header'));
        $curl->exec();
        $this->assertEquals('body', $curl->getBody());
        $this->assertEquals('header', $curl->getHeader());
    }

    /**
     * @throws CurlException
     */
    public function testResponseHeaderInvalid()
    {
        $curl = $this->getMockBuilder(Curl::class)->onlyMethods(['getAndValidateResponse', 'getInfo'])->getMock();
        $curl->expects($this->once())->method('getAndValidateResponse')->willReturnCallback(function () {
            return 'headerbody';
        });
        $curl->method('getInfo')->with(CURLINFO_HEADER_SIZE)->willReturn('0');
        $curl->exec();

        $this->expectException(CurlException::class);
        $this->expectExceptionMessage('Unprocessable response');
        $curl->getHeader();
    }

    /**
     * @throws CurlException
     */
    public function testResponseBodyInvalid()
    {
        $curl = $this->getMockBuilder(Curl::class)->onlyMethods(['getAndValidateResponse', 'getInfo'])->getMock();
        $curl->expects($this->once())->method('getAndValidateResponse')->willReturnCallback(function () {
            return '';
        });
        $curl->method('getInfo')->with(CURLINFO_HEADER_SIZE)->willReturn('100');
        $curl->exec();

        $this->expectException(CurlException::class);
        $this->expectExceptionMessage('Unprocessable response');
        $curl->getBody();
    }
}
