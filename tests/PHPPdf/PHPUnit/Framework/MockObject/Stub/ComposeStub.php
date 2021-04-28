<?php
declare(strict_types=1);

namespace PHPPdf\PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Stub\Stub;

final class ComposeStub implements Stub
{
    private $stubs;

    public function __construct(array $stubs)
    {
        foreach($stubs as $stub)
        {
            if(!$stub instanceof Stub)
            {
                throw new \InvalidArgumentException('Stubs have to implements PHPUnit\Framework\MockObject\Stub\Stub interface.');
            }
        }

        $this->stubs = $stubs;
    }

    public function invoke(\PHPUnit\Framework\MockObject\Invocation $invocation)
    {
        $returnValue = null;
        foreach($this->stubs as $stub)
        {
            $value = $stub->invoke($invocation);

            if($value !== null)
            {
                $returnValue = $value;
            }
        }

        return $returnValue;
    }

    public function toString(): string
    {
        $text = '';

        foreach($this->stubs as $stub)
        {
            $text .= $stub->toString();
        }

        return $text;
    }
}
