<?php

namespace Dissect\Parser\LALR1\Analysis;

use Dissect\Parser\Rule;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @test
     */
    public function getActiveComponentShouldReturnTheComponentAboutToBeEncountered()
    {
        $item = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 1);

        $this->assertEquals('b', $item->getActiveComponent());
    }

    /**
     * @test
     */
    public function itemShouldBeAReduceItemIfAllComponentsHaveBeenEncountered()
    {
        $item = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 1);
        $this->assertFalse($item->isReduceItem());

        $item = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 3);
        $this->assertTrue($item->isReduceItem());
    }

    /**
     * @test
     */
    public function itemShouldPumpLookaheadIntoConnectedItems()
    {
        $item1 = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 1);
        $item2 = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 2);

        $item1->connect($item2);
        $item1->pump('d');

        $this->assertContains('d', $item2->getLookahead());
    }

    /**
     * @test
     */
    public function itemShouldPumpTheSameLookaheadOnlyOnce()
    {
        $item1 = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 1);

        $item2 = $this->getMockBuilder(
            'Dissect\\Parser\\LALR1\\Analysis\\Item'
            )
            ->setMethods(['pump'])
            ->setConstructorArgs(
                [
                    new Rule(1, 'A', array('a', 'b', 'c')),
                    2,
                ]
            )->getMock();

        $item2->expects($this->once())
            ->method('pump')
            ->with($this->equalTo('d'));

        $item1->connect($item2);

        $item1->pump('d');
        $item1->pump('d');
    }

    /**
     * @test
     */
    public function getUnrecognizedComponentsShouldReturnAllComponentAfterTheDottedOne()
    {
        $item = new Item(new Rule(1, 'A', array('a', 'b', 'c')), 1);

        $this->assertEquals(array('c'), $item->getUnrecognizedComponents());
    }
}
