<?php

namespace Dissect\Parser;

use PHPUnit\Framework\TestCase;

class GrammarTest extends TestCase
{
    protected $grammar;

    public function setUp(): void
    {
        $this->grammar = new ExampleGrammar();
    }

    /**
     * @test
     */
    public function ruleAlternativesShouldHaveTheSameName()
    {
        $rules = $this->grammar->getRules();

        $this->assertEquals('Foo', $rules[1]->getName());
        $this->assertEquals('Foo', $rules[2]->getName());
    }

    /**
     * @test
     */
    public function theGrammarShouldBeAugmentedWithAStartRule()
    {
        $this->assertEquals(
            Grammar::START_RULE_NAME,
            $this->grammar->getStartRule()->getName()
        );

        $this->assertEquals(
            array('Foo'),
            $this->grammar->getStartRule()->getComponents()
        );
    }

    /**
     * @test
     */
    public function shouldReturnAlternativesGroupedByName()
    {
        $rules = $this->grammar->getGroupedRules();
        $this->assertCount(2, $rules['Foo']);
    }

    /**
     * @test
     */
    public function nonterminalsShouldBeDetectedFromRuleNames()
    {
        $this->assertTrue($this->grammar->hasNonterminal('Foo'));
    }
}
