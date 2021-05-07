<?php

use imehrzadm\TronTrxAPI\Block;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{

    /**
     * @covers \imehrzadm\TronTrxAPI\Block::__construct
     */
    public function testConstructorThrowsException()
    {
        new Block('blockId', new stdClass());

        $this->expectException(Exception::class);
        new Block('', new stdClass());
    }
}
