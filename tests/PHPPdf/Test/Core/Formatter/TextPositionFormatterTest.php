<?php

namespace PHPPdf\Test\Core\Formatter;

use PHPPdf\Core\Formatter\TextPositionFormatter,
    PHPPdf\Core\Point,
    PHPPdf\Core\Document;

class TextPositionFormatterTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    const TEXT_LINE_HEIGHT = 14;

    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new TextPositionFormatter();
    }

    /**
     * @test
     */
    public function addPointsToBoundaryAccordingToLineSizes()
    {
        $mock = $this->getTextMock(array(50, 100), array(0, 700));

        $this->formatter->format($mock, $this->createDocumentStub());
    }

    private function getTextMock($lineSizes, $parentFirstPoint, $firstXCoord = null)
    {
        $parentMock = $this->getMockBuilder('\PHPPdf\Core\Node\Node')->setMethods(array('getStartDrawingPoint'))->getMock();
        $parentMock->expects($this->once())
                   ->method('getStartDrawingPoint')
                   ->will($this->returnValue(array(0, 700)));

        $mock = $this->getMockBuilder('\PHPPdf\Core\Node\Text')->setMethods(array(
            'getParent',
            'getLineHeightRecursively',
            'getLineSizes',
            'getStartDrawingPoint',
            'getBoundary',
        ))->getMock();


        $mock->expects($this->atLeastOnce())
             ->method('getParent')
             ->will($this->returnValue($parentMock));

        $boundaryMock = $this->getMockBuilder('\PHPPdf\Core\Boundary')->setMethods(array(
            'getFirstPoint',
            'setNext',
            'close',
        ))->getMock();

        $firstXCoord = $firstXCoord ? $firstXCoord : $parentFirstPoint[0];
        $boundaryMock->expects($this->atLeastOnce())
                     ->method('getFirstPoint')
                     ->will($this->returnValue(Point::getInstance($firstXCoord, $parentFirstPoint[1])));

        $this->addBoundaryPointsAsserts($boundaryMock, $lineSizes, $parentFirstPoint[1]);

        $mock->expects($this->atLeastOnce())
             ->method('getBoundary')
             ->will($this->returnValue($boundaryMock));

        $mock->expects($this->atLeastOnce())
             ->method('getBoundary')
             ->will($this->returnValue($boundaryMock));

        $mock->expects($this->once())
             ->method('getLineHeightRecursively')
             ->will($this->returnValue(self::TEXT_LINE_HEIGHT));

        $mock->expects($this->once())
             ->method('getLineSizes')
             ->will($this->returnValue($lineSizes));

        return $mock;
    }

    private function addBoundaryPointsAsserts($boundaryMock, $lineSizes, $firstYCoord)
    {
        $at = 1;
        foreach($lineSizes as $i => $size)
        {
            $yCoord = $firstYCoord - self::TEXT_LINE_HEIGHT*$i;
            $boundaryMock->expects($this->at($at++))
                         ->method('setNext')
                         ->with($size, $yCoord);

            if(isset($lineSizes[$i+1]))
            {
                $boundaryMock->expects($this->at($at++))
                             ->method('setNext')
                             ->with($size, $yCoord - self::TEXT_LINE_HEIGHT);
            }
        }

        $boundaryMock->expects($this->once())
                     ->method('close');
    }
}
