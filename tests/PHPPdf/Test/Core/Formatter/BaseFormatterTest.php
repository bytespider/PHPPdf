<?php

namespace PHPPdf\Test\Core\Formatter;

use PHPPdf\Core\Formatter\BaseFormatter;
use PHPPdf\Stub\Formatter\StubFormatter;

abstract class BaseFormatterTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new StubFormatter();
    }

    /**
     * @test
     */
    public function throwExceptionIfTryToGetUnsettedDocument()
    {
        $this->expectException(\LogicException::class);
        $this->formatter->getDocument();
    }

    /**
     * @test
     */
    public function dontThrowExceptionIfDocumentIsSet()
    {
        $document = $this->createDocumentStub();
        $this->formatter->setDocument($document);

        $this->assertTrue($document === $this->formatter->getDocument());
    }

    /**
     * @test
     */
    public function unserializedFormatterHaveDocumentDetached()
    {
        $this->expectException(\LogicException::class);
        $document = $this->createDocumentStub();
        $this->formatter->setDocument($document);

        $unserializedFormatter = unserialize(serialize($this->formatter));

        $unserializedFormatter->getDocument();
    }
}
