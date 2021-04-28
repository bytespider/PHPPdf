<?php

namespace PHPPdf\Test\Core\Formatter;

use PHPPdf\Core\Formatter\CellFirstPointPositionFormatter,
    PHPPdf\Core\Document,
    PHPPdf\Core\Point;

class CellFirstPointPositionFormatterTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new CellFirstPointPositionFormatter();
    }

    /**
     * @test
     */
    public function setFirstPointAsFirstPointOfParent()
    {
        $firstPoint = Point::getInstance(0, 500);

        $parent = $this->getMockBuilder('PHPPdf\Core\Node\Container')->setMethods(array('getFirstPoint'))->getMock();
        $parent->expects($this->atLeastOnce())
               ->method('getFirstPoint')
               ->will($this->returnValue($firstPoint));

        $boundary = $this->getMockBuilder('PHPPdf\Core\Boundary')->setMethods(array('setNext'))->getMock();
        $boundary->expects($this->once())
                 ->method('setNext')
                 ->with($firstPoint);

        $node = $this->getMockBuilder('PHPPdf\Core\Node\Container')->setMethods(array('getParent', 'getBoundary'))->getMock();
        $node->expects($this->atLeastOnce())
              ->method('getParent')
              ->will($this->returnValue($parent));
        $node->expects($this->atLeastOnce())
              ->method('getBoundary')
              ->will($this->returnValue($boundary));

        $this->formatter->format($node, $this->createDocumentStub());
    }
}
