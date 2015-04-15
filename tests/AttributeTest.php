<?php namespace Sofa\Eloquence\Tests;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Metable\Attribute;
use Sofa\Eloquence\Metable\AttributeBag;

class AttributeTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function it_handles_casting_to_string()
    {
        $color  = new Attribute('color', 'red');
        $array  = new Attribute('array', [1,2,3]);
        $object = new Attribute('array', (object) ['foo', 'bar']);

        $this->assertEquals('red', (string) $color);
        $this->assertEquals('[1,2,3]', (string) $array);
        $this->assertEquals('', (string) $object);
    }

    /**
     * @test
     */
    public function it_calls_instance_mutators()
    {
        $attribute = new AttributeNoMutatorsStub('foo', [1,2]);
        $attribute->getMutators = ['array' => 'customMutator'];

        $this->assertEquals('mutated_value', $attribute->getValue());
    }

    /**
     * @test
     *
     * @expectedException \Sofa\Eloquence\Metable\InvalidMutatorException
     */
    public function wrong_mutator()
    {
        $attribute = new AttributeNoMutatorsStub('foo', [1,2]);
        $attribute->getMutators = ['array' => 'no_function_here'];

        $attribute->getValue();
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_rejects_user_types_without_mutator()
    {
        $attribute = new Attribute('foo', $this->getAttribute()->newCollection());
    }

    /**
     * @test
     *
     * @dataProvider validTypes
     */
    public function it_casts_values_to_proper_types($typeAttribute)
    {
        list($type, $attribute) = $typeAttribute;

        $this->assertInternalType($type, $attribute->getValue());
    }

    /**
     * dataProvider
     */
    public function validTypes()
    {
        return [
            [['int',    new AttributeNoMutatorsStub('key', 1)]],
            [['float',  new AttributeNoMutatorsStub('key', 1.5)]],
            [['bool',   new AttributeNoMutatorsStub('key', true)]],
            [['array',  new AttributeNoMutatorsStub('key', [1,2])]],
            [['null',   new AttributeNoMutatorsStub('key', null)]],
        ];
    }

    /**
     * @test
     */
    public function getters()
    {
        $attribute = $this->getAttribute();

        $this->assertEquals('color', $attribute->getKey());
        $this->assertEquals('red', $attribute->getValue());
    }

    /**
     * @test
     */
    public function it_uses_attribute_bag()
    {
        $bag = $this->getAttribute()->newCollection();

        $this->assertInstanceOf('Sofa\Eloquence\Metable\AttributeBag', $bag);
    }

    /**
     * @test
     * @covers \Sofa\Eloquence\Metable\Attribute::getTable
     * @covers \Sofa\Eloquence\Metable\Attribute::setCustomTable
     */
    public function it_allows_custom_table_name_to_be_set_once()
    {
        $attribute = $this->getAttribute();
        $this->assertEquals('meta_attributes', $attribute->getTable());

        Attribute::setCustomTable('meta');
        $this->assertEquals('meta', $attribute->getTable());

        Attribute::setCustomTable('cant_do_it_again');
        $this->assertEquals('meta', $attribute->getTable());
    }

    protected function getAttribute()
    {
        return new Attribute('color', 'red');
    }
}

class AttributeNoMutatorsStub extends Attribute {
    public $getMutators = [];

    public function customMutator($value)
    {
        return 'mutated_value';
    }
}