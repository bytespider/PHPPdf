<?php

namespace PHPPdf\Test\Core;

use PHPPdf\Core\ColorPalette;
use PHPPdf\Core\Configuration\LoaderImpl;
use PHPPdf\Core\Facade;
use PHPPdf\Core\FacadeConfiguration;

class FacadeTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $facade;

    private $loaderMock;
    private $documentParser;

    public function setUp(): void
    {
        $this->loaderMock = $this->getMockBuilder('PHPPdf\Core\Configuration\Loader')->getMock();
        $this->documentParser = $this->getMockBuilder('PHPPdf\Core\Parser\DocumentParser')->getMock();
        $this->stylesheetParser = $this->getMockBuilder('PHPPdf\Core\Parser\StylesheetParser')
                                       ->setMethods(array('parse'))
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $document = $this->createDocumentStub();

        $this->facade = new Facade($this->loaderMock, $document, $this->documentParser, $this->stylesheetParser);
    }

    /**
     * @test
     */
    public function parsersMayByInjectedFromOutside()
    {
        $documentParser = $this->getMockBuilder('PHPPdf\Core\Parser\DocumentParser')->getMock();
        $stylesheetParser = $this->getMockBuilder('PHPPdf\Core\Parser\StylesheetParser')->getMock();

        $this->facade->setDocumentParser($documentParser);
        $this->facade->setStylesheetParser($stylesheetParser);

        $this->assertTrue($this->facade->getDocumentParser() === $documentParser);
        $this->assertTrue($this->facade->getStylesheetParser() === $stylesheetParser);
    }

    /**
     * @test
     */
    public function drawingProcess()
    {
        $xml = '<pdf></pdf>';
        $stylesheet = '<stylesheet></stylesheet>';
        $colorPaletteContent = '<colors></colors>';
        $colorPalette = array('color' => '#abcabc');
        $content = 'pdf content';

        $documentMock = $this->getMockBuilder('PHPPdf\Core\Document')
                             ->setMethods(array('draw', 'initialize', 'render', 'addFontDefinitions', 'setComplexAttributeFactory', 'setColorPalette'))
                             ->disableOriginalConstructor()
                             ->getMock();

        $stylesheetParserMock = $this->getMockBuilder('PHPPdf\Core\Parser\StylesheetParser')->setMethods(array('parse'))->getMock();
        $constraintMock = $this->getMockBuilder('PHPPdf\Core\Parser\StylesheetConstraint')->getMock();
        $pageCollectionMock = $this->getMockBuilder('PHPPdf\Core\Node\PageCollection')->getMock();

        $colorPaletteParserMock = $this->getMockBuilder('PHPPdf\Parser\Parser')->getMock();
        $colorPaletteParserMock->expects($this->once())
                               ->method('parse')
                               ->will($this->returnValue($colorPalette));
        $this->facade->setColorPaletteParser($colorPaletteParserMock);

        $nodeFactoryMock = $this->getMockBuilder('PHPPdf\Core\Node\NodeFactory')->getMock();
        $complexAttributeFactoryMock = $this->getMockBuilder('PHPPdf\Core\ComplexAttribute\ComplexAttributeFactory')->getMock();
        $fontDefinitionsStub = array('some-data');

        $this->loaderMock->expects($this->atLeastOnce())
                         ->method('createNodeFactory')
                         ->will($this->returnValue($nodeFactoryMock));
        $this->loaderMock->expects($this->atLeastOnce())
                         ->method('createComplexAttributeFactory')
                         ->will($this->returnValue($complexAttributeFactoryMock));
        $this->loaderMock->expects($this->atLeastOnce())
                         ->method('createFontRegistry')
                         ->will($this->returnValue($fontDefinitionsStub));

        $documentMock->expects($this->once())
                     ->method('setColorPalette')
                     ->with(new ColorPalette($colorPalette));

        $documentMock->expects($this->once())
                     ->method('addFontDefinitions')
                     ->with($fontDefinitionsStub);
        $documentMock->expects($this->once())
                     ->method('setComplexAttributeFactory')
                     ->with($complexAttributeFactoryMock);
        $this->documentParser->expects($this->once())
                             ->method('setComplexAttributeFactory')
                             ->with($complexAttributeFactoryMock);
        $this->documentParser->expects($this->once())
                             ->method('setNodeFactory')
                             ->with($nodeFactoryMock);

        $this->documentParser->expects($this->once())
                             ->method('parse')
                             ->with($this->equalTo($xml), $this->equalTo($constraintMock))
                             ->will($this->returnValue($pageCollectionMock));

        $this->stylesheetParser->expects($this->once())
                               ->method('parse')
                               ->with($this->equalTo($stylesheet))
                               ->will($this->returnValue($constraintMock));

        $documentMock->expects($this->at(3))
                ->method('draw')
                ->with($this->equalTo($pageCollectionMock));
        $documentMock->expects($this->at(4))
                ->method('render')
                ->will($this->returnValue($content));
        $documentMock->expects($this->at(5))
                ->method('initialize');

        $this->invokeMethod($this->facade, 'setDocument', array($documentMock));

        $result = $this->facade->render($xml, $stylesheet, $colorPaletteContent);

        $this->assertEquals($content, $result);
    }

    /**
     * @test
     * @dataProvider stylesheetCachingParametersProvider
     */
    public function dontCacheStylesheetConstraintByDefault($numberOfCacheMethodInvoking, $useCache)
    {
        $facade = new Facade(new LoaderImpl(), $this->createDocumentStub(), $this->documentParser, $this->stylesheetParser);

        $cache = $this->getMockBuilder('PHPPdf\Cache\NullCache')->setMethods(array('test', 'save', 'load'))->getMock();
        $cache->expects($this->exactly($numberOfCacheMethodInvoking))
              ->method('test')
              ->will($this->returnValue(false));
        $cache->expects($this->exactly(0))
              ->method('load');
        $cache->expects($this->exactly($numberOfCacheMethodInvoking))
              ->method('save');

        $this->documentParser->expects($this->once())
                             ->method('parse')
                             ->will($this->returnValue(new \PHPPdf\Core\Node\PageCollection()));

        $this->stylesheetParser->expects($this->once())
                                 ->method('parse')
                                 ->will($this->returnValue(new \PHPPdf\Core\Parser\CachingStylesheetConstraint()));


        $facade->setCache($cache);

        $facade->setUseCacheForStylesheetConstraint($useCache);

        $facade->render('<pdf></pdf>', '<stylesheet></stylesheet>');
    }

    public function stylesheetCachingParametersProvider()
    {
        return array(
            array(0, false),
            array(1, true),
        );
    }
}
