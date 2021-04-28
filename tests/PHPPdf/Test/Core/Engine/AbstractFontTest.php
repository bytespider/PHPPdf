<?php

namespace PHPPdf\Test\Core\Engine;

use PHPPdf\PHPUnit\Framework\TestCase;
use PHPPdf\Stub\Engine\Font as StubFont;
use PHPPdf\Core\Engine\Font;

class AbstractFontTest extends TestCase
{
    private $font;

    public function setUp(): void
    {
        $this->font = new StubFont(array(
            Font::STYLE_NORMAL => TEST_RESOURCES_DIR.'/font-judson/normal.ttf',
            Font::STYLE_BOLD => TEST_RESOURCES_DIR.'/font-judson/bold.ttf',
            Font::STYLE_ITALIC => TEST_RESOURCES_DIR.'/font-judson/italic.ttf',
            Font::STYLE_BOLD_ITALIC => TEST_RESOURCES_DIR.'/font-judson/bold+italic.ttf',
        ));
    }

    /**
     * @test
     */
    public function creationWithEmptyArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        new StubFont(array());
    }

    /**
     * @test
     */
    public function creationWithInvalidFontTypes()
    {
        $this->expectException(\InvalidArgumentException::class);
        new StubFont(array(
            Font::STYLE_BOLD => TEST_RESOURCES_DIR.'/font-judson/bold.ttf',
            Font::STYLE_NORMAL => TEST_RESOURCES_DIR.'/font-judson/normal.ttf',
            8 => TEST_RESOURCES_DIR.'/font-judson/normal.ttf',
        ));
    }

    /**
     * @test
     */
    public function creationWithoutNormalFont()
    {
        $this->expectException(\InvalidArgumentException::class);
        new StubFont(array(
            Font::STYLE_BOLD => TEST_RESOURCES_DIR.'/font-judson/normal.ttf',
        ));
    }
}
