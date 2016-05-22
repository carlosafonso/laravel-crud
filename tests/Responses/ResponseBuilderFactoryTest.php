<?php
namespace Afonso\LvCrud\Tests\Responses;

use Illuminate\Support\Facades\Request;
use Afonso\LvCrud\Responses\ResponseBuilderFactory;
use Afonso\LvCrud\Tests\BaseTestCase;

class ResponseBuilderFactoryTest extends BaseTestCase
{
    private $requestMock;

    private $controllerMock;

    public function setUp()
    {
        parent::setUp();
        $this->requestMock = $this->getMockBuilder('Illuminate\\Http\\Request')
            ->getMock();
        Request::shouldReceive('instance')
            ->andReturn($this->requestMock);
        $this->controllerMock = $this->getMockBuilder('Afonso\\LvCrud\\Controllers\\CrudController')
            ->getMock();
    }

    public function testFactoryShouldReturnJsonBuilderIfAllFormatsAreSupportedAndRequestWantsJson()
    {
        $this->requestMock->expects($this->any())
            ->method('wantsJson')
            ->willReturn(true);
        $this->controllerMock->expects($this->any())
            ->method('supportsJson')
            ->willReturn(true);
        $this->controllerMock->expects($this->any())
            ->method('supportsHtml')
            ->willReturn(true);

        $builder = ResponseBuilderFactory::forRequest($this->requestMock, $this->controllerMock);
        $this->assertInstanceOf('Afonso\\LvCrud\\Responses\\JsonResponseBuilder', $builder);
    }

    public function testFactoryShouldReturnHtmlBuilderIfAllFormatsAreSupportedAndRequestDoesntWantJson()
    {
        $this->requestMock->expects($this->any())
            ->method('wantsJson')
            ->willReturn(false);
        $this->controllerMock->expects($this->any())
            ->method('supportsJson')
            ->willReturn(true);
        $this->controllerMock->expects($this->any())
            ->method('supportsHtml')
            ->willReturn(true);

        $builder = ResponseBuilderFactory::forRequest($this->requestMock, $this->controllerMock);
        $this->assertInstanceOf('Afonso\\LvCrud\\Responses\\HtmlResponseBuilder', $builder);
    }

    public function testFactoryShouldReturnJsonResponseBuilderIfHtmlIsNotSupported()
    {
        $this->controllerMock->expects($this->any())
            ->method('supportsJson')
            ->willReturn(true);
        $this->controllerMock->expects($this->any())
            ->method('supportsHtml')
            ->willReturn(false);

        $builder = ResponseBuilderFactory::forRequest($this->requestMock, $this->controllerMock);
        $this->assertInstanceOf('Afonso\\LvCrud\\Responses\\JsonResponseBuilder', $builder);
    }

    public function testFactoryShouldReturnHtmlResponseBuilderIfJsonIsNotSupported()
    {
        $this->controllerMock->expects($this->any())
            ->method('supportsJson')
            ->willReturn(false);
        $this->controllerMock->expects($this->any())
            ->method('supportsHtml')
            ->willReturn(true);

        $builder = ResponseBuilderFactory::forRequest($this->requestMock, $this->controllerMock);
        $this->assertInstanceOf('Afonso\\LvCrud\\Responses\\HtmlResponseBuilder', $builder);
    }
}
