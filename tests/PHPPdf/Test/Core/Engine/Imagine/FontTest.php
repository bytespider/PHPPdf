<?php

namespace PHPPdf\Test\Core\Engine\Imagine;

use PHPPdf\PHPUnit\Framework\TestCase;
use PHPPdf\Core\Engine\Imagine\Font;

class FontTest extends TestCase
{
    private $font;
    private $imagine;
    private $fontResources;

    public function setUp(): void
    {
        if(!interface_exists('Imagine\Image\ImagineInterface', true))
        {
            $this->fail('Imagine library is missing. You have to download dependencies, for example by using "vendors.php" file.');
        }

        $this->imagine = $this->getMockBuilder('Imagine\Image\ImagineInterface')->getMock();
        $this->fontResources = array(
            Font::STYLE_NORMAL => TEST_RESOURCES_DIR.'/font-judson/normal.ttf',
            Font::STYLE_BOLD => TEST_RESOURCES_DIR.'/font-judson/bold.ttf',
            Font::STYLE_ITALIC => TEST_RESOURCES_DIR.'/font-judson/italic.ttf',
            Font::STYLE_BOLD_ITALIC => TEST_RESOURCES_DIR.'/font-judson/bold+italic.ttf',
        );

        $this->font = new Font($this->fontResources, $this->imagine);
    }

    /**
     * @test
     */
    public function getWidthOfText()
    {
        $text = 'some text';
        $fontSize = 12;

        $expectedWidth = 111;

        $box = $this->getMockBuilder('Imagine\Image\BoxInterface')->getMock();
        $box->expects($this->once())
            ->method('getWidth')
            ->will($this->returnValue($expectedWidth));

        $font = $this->getMockBuilder('Imagine\Image\FontInterface')->getMock();
        $font->expects($this->once())
             ->method('box')
             ->with($text)
             ->will($this->returnValue($box));

        $this->imagine->expects($this->once())
                      ->method('font')
                      ->with($this->fontResources[Font::STYLE_NORMAL], $fontSize, $this->anything())
                      ->will($this->returnValue($font));

        $width = $this->font->getWidthOfText($text, $fontSize);

        $this->assertEquals($expectedWidth, $width);
    }

    /**
     * @test
     * @dataProvider styleProvider
     */
    public function styleSwitching($style)
    {
        $color = '#000000';
        $fontSize = 13;

        $this->font->setStyle($style);

        $expectedFont = $this->getMockBuilder('Imagine\Image\FontInterface')->getMock();

        $this->imagine->expects($this->once())
                      ->method('font')
                      ->with($this->fontResources[$style], $fontSize, $this->anything())
                      ->will($this->returnValue($expectedFont));

        $actualFont = $this->font->getWrappedFont($color, $fontSize);

        $this->assertEquals($expectedFont, $actualFont);
    }

    public function styleProvider()
    {
        return array(
            array(Font::STYLE_NORMAL),
            array(Font::STYLE_BOLD),
        );
    }
}
