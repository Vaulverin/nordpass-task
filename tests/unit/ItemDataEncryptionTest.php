<?php

namespace App\Tests\unit;

use App\Entity\Item;
use PHPUnit\Framework\TestCase;

class ItemDataEncryptionTest extends TestCase
{
    function testDataEncryption()
    {
        $item = new Item();
        $data = 'super secret string';
        $item->setData($data);
        $this->assertEquals($data, $item->getData(), 'Item data changed');
    }
}