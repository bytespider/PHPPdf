<?php

namespace PHPPdf\Test\Core\Node;

use PHPPdf\Core\Node\PageContext,
    PHPPdf\Core\Node\DynamicPage;

class PageContextTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function gettingNumberOfPages()
    {
        $numberOfPages = 5;
        $mock = $this->getMockBuilder('PHPPdf\Core\Node\DynamicPage')->setMethods(array('getNumberOfPages'))->getMock();
        $mock->expects($this->atLeastOnce())
             ->method('getNumberOfPages')
             ->will($this->returnValue($numberOfPages));

        $currentPageNumber = 3;
        $context = new PageContext($currentPageNumber, $mock);

        $this->assertEquals($numberOfPages, $context->getNumberOfPages());
        $this->assertEquals($currentPageNumber, $context->getPageNumber());
    }
}
