<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\TypeValidator;

class TypeValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected static $file;

    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new TypeValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Type(array('type' => 'integer')));
    }

    public function testEmptyIsValidIfString()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Type(array('type' => 'string')));
    }

    public function testEmptyIsInvalidIfNoString()
    {
        $this->context->expects($this->once())
            ->method('addViolation');

        $this->validator->validate('', new Type(array('type' => 'integer')));
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value, $type)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Type(array('type' => $type));

        $this->validator->validate($value, $constraint);
    }

    public function getValidValues()
    {
        $object = new \stdClass();
        $file = $this->createFile();

        return array(
            array(true, 'Boolean'),
            array(false, 'Boolean'),
            array(true, 'boolean'),
            array(false, 'boolean'),
            array(true, 'bool'),
            array(false, 'bool'),
            array(0, 'numeric'),
            array('0', 'numeric'),
            array(1.5, 'numeric'),
            array('1.5', 'numeric'),
            array(0, 'integer'),
            array(1.5, 'float'),
            array('12345', 'string'),
            array(array(), 'array'),
            array($object, 'object'),
            array($object, 'stdClass'),
            array($file, 'resource'),
            array('12345', 'digit'),
            array('12a34', 'alnum'),
            array('abcde', 'alpha'),
            array("\n\r\t", 'cntrl'),
            array('arf12', 'graph'),
            array('abcde', 'lower'),
            array('ABCDE', 'upper'),
            array('arf12', 'print'),
            array('*&$()', 'punct'),
            array("\n\r\t", 'space'),
            array('AB10BC99', 'xdigit'),
        );
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value, $type, $valueAsString)
    {
        $constraint = new Type(array(
            'type' => $type,
            'message' => 'myMessage'
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ value }}' => $valueAsString,
                '{{ type }}' => $type,
            ));

        $this->validator->validate($value, $constraint);
    }

    public function getInvalidValues()
    {
        $object = new \stdClass();
        $file = $this->createFile();

        return array(
            array('foobar', 'numeric', '"foobar"'),
            array('foobar', 'boolean', '"foobar"'),
            array('0', 'integer', '"0"'),
            array('1.5', 'float', '"1.5"'),
            array(12345, 'string', '12345'),
            array($object, 'boolean', 'object'),
            array($object, 'numeric', 'object'),
            array($object, 'integer', 'object'),
            array($object, 'float', 'object'),
            array($object, 'string', 'object'),
            array($object, 'resource', 'object'),
            array($file, 'boolean', 'resource'),
            array($file, 'numeric', 'resource'),
            array($file, 'integer', 'resource'),
            array($file, 'float', 'resource'),
            array($file, 'string', 'resource'),
            array($file, 'object', 'resource'),
            array('12a34', 'digit', '"12a34"'),
            array('1a#23', 'alnum', '"1a#23"'),
            array('abcd1', 'alpha', '"abcd1"'),
            array("\nabc", 'cntrl', "\"\nabc\""),
            array("abc\n", 'graph', "\"abc\n\""),
            array('abCDE', 'lower', '"abCDE"'),
            array('ABcde', 'upper', '"ABcde"'),
            array("\nabc", 'print', "\"\nabc\""),
            array('abc&$!', 'punct', '"abc&$!"'),
            array("\nabc", 'space', "\"\nabc\""),
            array('AR1012', 'xdigit', '"AR1012"'),
        );
    }

    protected function createFile()
    {
        if (!self::$file) {
            self::$file = fopen(__FILE__, 'r');
        }

        return self::$file;
    }

    public static function tearDownAfterClass()
    {
        if (self::$file) {
            fclose(self::$file);
        }
    }
}
