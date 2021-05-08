<?php

use pingi\trontrxapi\Block;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{

    /**
     * @covers \pingi\trontrxapi\Block::__construct
     */
    public function testConstructorThrowsException()
    {
        new Block('blockId', new stdClass());

        $this->expectException(Exception::class);
        new Block('', new stdClass());
    }
}
