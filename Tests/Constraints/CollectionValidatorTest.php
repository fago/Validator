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

use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Collection\Required;
use Symfony\Component\Validator\Constraints\Collection\Optional;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\CollectionValidator;

abstract class CollectionValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;

    protected function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new CollectionValidator();
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue('MyGroup'));
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    public function deprecationErrorHandler($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return \PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    abstract protected function prepareTestData(array $contents);

    public function testNullIsValid()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate(null, new Collection(array('fields' => array(
            'foo' => new Range(array('min' => 4)),
        ))));

        restore_error_handler();
    }

    public function testFieldsAsDefaultOption()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array('foo' => 'foobar'));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'foo' => new Range(array('min' => 4)),
        )));

        restore_error_handler();
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testThrowsExceptionIfNotTraversable()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $this->validator->validate('foobar', new Collection(array('fields' => array(
            'foo' => new Range(array('min' => 4)),
        ))));

        restore_error_handler();
    }

    public function testWalkSingleConstraint()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));
        $constraint = new Range(array('min' => 4));
        restore_error_handler();

        $array = array(
            'foo' => 3,
            'bar' => 5,
        );
        $i = 1;

        foreach ($array as $key => $value) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($value, $constraint, '['.$key.']', 'MyGroup');
        }

        $data = $this->prepareTestData($array);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'fields' => array(
                'foo' => $constraint,
                'bar' => $constraint,
            ),
        )));
    }

    public function testWalkMultipleConstraints()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));
        $constraints = array(
            new Range(array('min' => 4)),
            new NotNull(),
        );
        restore_error_handler();

        $array = array(
            'foo' => 3,
            'bar' => 5,
        );
        $i = 1;

        foreach ($array as $key => $value) {
            foreach ($constraints as $constraint) {
                $this->context->expects($this->at($i++))
                    ->method('validateValue')
                    ->with($value, $constraint, '['.$key.']', 'MyGroup');
            }
        }

        $data = $this->prepareTestData($array);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'fields' => array(
                'foo' => $constraints,
                'bar' => $constraints,
            )
        )));
    }

    public function testExtraFieldsDisallowed()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array(
            'foo' => 5,
            'baz' => 6,
        ));

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[baz]', 'myMessage', array(
                '{{ field }}' => 'baz'
            ));

        $this->validator->validate($data, new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 4)),
            ),
            'extraFieldsMessage' => 'myMessage',
        )));

        restore_error_handler();
    }

    // bug fix
    public function testNullNotConsideredExtraField()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $constraint = new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 4)),
            ),
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);

        restore_error_handler();
    }

    public function testExtraFieldsAllowed()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array(
            'foo' => 5,
            'bar' => 6,
        ));

        $constraint = new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 4)),
            ),
            'allowExtraFields' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);

        restore_error_handler();
    }

    public function testMissingFieldsDisallowed()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array());

        $constraint = new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 4)),
            ),
            'missingFieldsMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[foo]', 'myMessage', array(
                '{{ field }}' => 'foo',
            ));

        $this->validator->validate($data, $constraint);

        restore_error_handler();
    }

    public function testMissingFieldsAllowed()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $data = $this->prepareTestData(array());

        $constraint = new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 4)),
            ),
            'allowMissingFields' => true,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, $constraint);

        restore_error_handler();
    }

    public function testOptionalFieldPresent()
    {
        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'foo' => new Optional(),
        )));
    }

    public function testOptionalFieldNotPresent()
    {
        $data = $this->prepareTestData(array());

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'foo' => new Optional(),
        )));
    }

    public function testOptionalFieldSingleConstraint()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $array = array(
            'foo' => 5,
        );

        $constraint = new Range(array('min' => 4));

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($array['foo'], $constraint, '[foo]', 'MyGroup');

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Collection(array(
            'foo' => new Optional($constraint),
        )));

        restore_error_handler();
    }

    public function testOptionalFieldMultipleConstraints()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $array = array(
            'foo' => 5,
        );

        $constraints = array(
            new NotNull(),
            new Range(array('min' => 4)),
        );
        $i = 1;

        foreach ($constraints as $constraint) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($array['foo'], $constraint, '[foo]', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Collection(array(
            'foo' => new Optional($constraints),
        )));

        restore_error_handler();
    }

    public function testRequiredFieldPresent()
    {
        $data = $this->prepareTestData(array(
            'foo' => null,
        ));

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($data, new Collection(array(
            'foo' => new Required(),
        )));
    }

    public function testRequiredFieldNotPresent()
    {
        $data = $this->prepareTestData(array());

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('[foo]', 'myMessage', array(
                '{{ field }}' => 'foo',
            ));

        $this->validator->validate($data, new Collection(array(
            'fields' => array(
                'foo' => new Required(),
            ),
            'missingFieldsMessage' => 'myMessage',
        )));
    }

    public function testRequiredFieldSingleConstraint()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $array = array(
            'foo' => 5,
        );

        $constraint = new Range(array('min' => 4));

        $this->context->expects($this->once())
            ->method('validateValue')
            ->with($array['foo'], $constraint, '[foo]', 'MyGroup');

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($data, new Collection(array(
            'foo' => new Required($constraint),
        )));

        restore_error_handler();
    }

    public function testRequiredFieldMultipleConstraints()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $array = array(
            'foo' => 5,
        );

        $constraints = array(
            new NotNull(),
            new Range(array('min' => 4)),
        );
        $i = 1;

        foreach ($constraints as $constraint) {
            $this->context->expects($this->at($i++))
                ->method('validateValue')
                ->with($array['foo'], $constraint, '[foo]', 'MyGroup');
        }

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $data = $this->prepareTestData($array);

        $this->validator->validate($array, new Collection(array(
            'foo' => new Required($constraints),
        )));

        restore_error_handler();
    }

    public function testObjectShouldBeLeftUnchanged()
    {
        set_error_handler(array($this, "deprecationErrorHandler"));

        $value = new \ArrayObject(array(
            'foo' => 3
        ));

        $this->validator->validate($value, new Collection(array(
            'fields' => array(
                'foo' => new Range(array('min' => 2)),
            )
        )));

        $this->assertEquals(array(
            'foo' => 3
        ), (array) $value);

        restore_error_handler();
    }
}
