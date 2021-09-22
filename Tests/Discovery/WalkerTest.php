<?php
declare(strict_types=1);

use Discovery\Walker;
use PHPUnit\Framework\TestCase;

class WalkerTest extends TestCase
{
    public function testBuildWalkerFailsWithWrongPath(): void
    {
        $this->expectException(Exception::class);
        new Walker(__DIR__ . "WalkerTest.php/" . uniqid());
    }

    public function testBuildWalker(): void
    {
        $walker = new Walker(__DIR__);
        $this->assertInstanceOf(Walker::class, $walker);
    }
}
