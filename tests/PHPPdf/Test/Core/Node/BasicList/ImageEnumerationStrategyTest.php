<?php

namespace PHPPdf\Test\Core\Node\BasicList;

use PHPPdf\Core\Document;
use PHPPdf\Core\Node\BasicList;
use PHPPdf\Core\Point;
use PHPPdf\Core\Node\BasicList\ImageEnumerationStrategy;

class ImageEnumerationStrategyTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $strategy;

    public function setUp(): void
    {
        $this->strategy = new ImageEnumerationStrategy();
    }

    /**
     * @test
     * @dataProvider enumerationProvider
     */
    public function drawEnumerationInValidPosition(Point $point, $position, $childMarginLeft, $fontSize)
    {
        $elementIndex = 1;

        $listMock = $this->getMockBuilder('PHPPdf\Core\Node\BasicList')->setMethods(array('getChild', 'getAttribute', 'getRecurseAttribute', 'getImage'))->getMock();

        $imageWidth = 100;
        $imageHeight = 100;
        $image = $this->createImageMock($imageWidth, $imageHeight);

        if($imageWidth > $fontSize)
        {
            $imageHeight = $imageHeight * $fontSize/$imageWidth;
            $imageWidth = $fontSize;
        }

        if($imageHeight > $fontSize)
        {
            $imageWidth = $imageWidth * $fontSize/$imageHeight;
            $imageHeight = $fontSize;
        }

        $listMock->expects($this->atLeastOnce())
                 ->method('getImage')
                 ->will($this->returnValue($image));

        $listMock->expects($this->atLeastOnce())
                 ->method('getAttribute')
                 ->with('list-position')
                 ->will($this->returnValue($position));

        $listMock->expects($this->atLeastOnce())
                 ->method('getRecurseAttribute')
                 ->with('font-size')
                 ->will($this->returnValue($fontSize));

        $child = $this->getMockBuilder('PHPPdf\Core\Node\Container')->setMethods(array('getFirstPoint', 'getMarginLeft'))->getMock();
        $child->expects($this->atLeastOnce())
              ->method('getFirstPoint')
              ->will($this->returnValue($point));
        $child->expects($this->atLeastOnce())
              ->method('getMarginLeft')
              ->will($this->returnValue($childMarginLeft));

        $listMock->expects($this->atLeastOnce())
                 ->method('getChild')
                 ->with($elementIndex)
                 ->will($this->returnValue($child));

        $xTranslation = 0;
        if($position === BasicList::LIST_POSITION_OUTSIDE)
        {
            $xTranslation = $imageWidth;
        }

        $expectedX1Coord = $point->getX() - $childMarginLeft - $xTranslation;
        $expectedY1Coord = $point->getY() - $imageHeight;
        $expectedX2Coord = $point->getX() + $imageWidth - $childMarginLeft - $xTranslation;
        $expectedY2Coord = $point->getY();

        $gc = $this->getMockBuilder('PHPPdf\Core\Engine\GraphicsContext')
                       ->getMock();
        $gc->expects($this->once())
           ->method('drawImage')
           ->with($image, $expectedX1Coord, $expectedY1Coord, $expectedX2Coord, $expectedY2Coord);

        $this->strategy->setIndex($elementIndex);
        $this->strategy->drawEnumeration($this->getDocumentStub(), $listMock, $gc);
    }

    private function getDocumentStub()
    {
        return $this->createDocumentStub();
    }

    private function createImageMock($width, $height)
    {
        $image = $this->getMockBuilder('PHPPdf\Core\Engine\Image')
                      ->setMethods(array('getOriginalHeight', 'getOriginalWidth'))
                      ->disableOriginalConstructor()
                      ->getMock();

        $image->expects($this->atLeastOnce())
              ->method('getOriginalHeight')
              ->will($this->returnValue($height));
        $image->expects($this->atLeastOnce())
              ->method('getOriginalWidth')
              ->will($this->returnValue($width));

        return $image;
    }

    public function enumerationProvider()
    {
        return array(
            array(Point::getInstance(50, 200), BasicList::LIST_POSITION_INSIDE, 20, 10),
        );
    }

    /**
     * @test
     */
    public function throwExceptionIfImageIsNotSet()
    {
        $this->expectException(\LogicException::class);
        $elementIndex = 1;
        $listMock = $this->getMockBuilder('PHPPdf\Core\Node\BasicList')
                         ->setMethods(array('getImage', 'getChild'))
                         ->getMock();

        $listMock->expects($this->once())
                 ->method('getImage')
                 ->will($this->returnValue(null));

        $child = $this->getMockBuilder('PHPPdf\Core\Node\Container')
                      ->setMethods(array())
                      ->getMock();

        $listMock->expects($this->atLeastOnce())
                 ->method('getChild')
                 ->with($elementIndex)
                 ->will($this->returnValue($child));

        $gc = $this->getMockBuilder('PHPPdf\Core\Engine\GraphicsContext')
                   ->getMock();

        $this->strategy->setIndex($elementIndex);
        $this->strategy->drawEnumeration($this->getDocumentStub(), $listMock, $gc);
    }
}
