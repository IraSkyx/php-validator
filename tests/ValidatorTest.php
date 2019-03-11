<?php
namespace Aubind97\Tests;

use Aubind97\Validator;
use Aubind97\ValidatorError;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var array params test array
     */
    private $params = [
        'unknown' => null,
        'number' => 1,
        'string' => 'normal string',
        'email' => 'joe@doe.fr',
        'invalid-email' => 'failed_email',
        'float' => 1.1,
        'string-int' => '1',
        'string-float-valid' => '1.1',
        'string-float-invalid' => '1,1',
        'datetime' => '2019-03-11 22:50:34',
        'datetime-invalid-1' => '2017-42-10',
        'datetime-invalid-2' => '2018-02-29 15:56:10',
        'long-string' => 'this is a long string',
        'short-string' => 'short'
    ];

    // Test ths construction without any filter
    public function testConstruction()
    {
        $validator = $this->getValidator();

        $this->assertSame(sizeof($this->params), sizeof($validator->getParams()));
    }

    // Test construction with a filter
    public function testConstructionWithFilter()
    {
        $filter = ['unknown'];
        $validator = new Validator($this->params, $filter);

        $this->assertSame(1, sizeof($validator->getParams()));
    }

    // Test non existing params
    public function testNonExistingParam()
    {
        $validator = ($this->getValidator())
            ->email('non-existing-param');

        $errors = $validator->getErrors();

        $this->assertSame(1, sizeof($errors));
    }

    // Test validation with a pre configure validator
    public function testPreConfigureValidation()
    {
        $validator = $this->getPreConfiguredValidator($this->params);

        $this->assertTrue($validator->isValid());
    }

    // Test validation with a pre configure validator
    public function testPreConfigureValidationWithNewRule()
    {
        $validator = ($this->getPreConfiguredValidator($this->params))
            ->numeric('email');

        $this->assertFalse($validator->isValid());
    }

    // Test dateTime validation
    public function testDateTimeValidation()
    {
        $format = 'Y-m-d H:i:s';

        $validator = ($this->getValidator())
            ->dateTime('datetime', $format);

        $this->assertTrue($validator->isValid());

        $validator = ($this->getValidator())
            ->dateTime('string', $format)
            ->dateTime('datetime-invalid-1', $format)
            ->dateTime('datetime-invalid-2', $format);

        $errors = $validator->getErrors();

        $this->assertSame(3, sizeof($errors));
        $this->assertEquals($errors['string'], $this->getErrorMessage('string', 'datetime', [$format]));
    }

    // Test email validation
    public function testEmailValidation()
    {
        $validator = ($this->getValidator())
            ->email('email');

        $this->assertTrue($validator->isValid());

        $validator
            ->email('invalid-email');

        $errors = $validator->getErrors();

        $this->assertFalse($validator->isValid());
        $this->assertSame(1, sizeof($errors));
        $this->assertEquals($errors['invalid-email'], $this->getErrorMessage('invalid-email', 'email'));
    }

    //
    // TODO: Test exists validation
    //

    //
    // TODO: Test extension validation
    //

    // Test length validation
    public function testLengthValidation()
    {
        $validator = ($this->getValidator())
            ->length('string', 3, 255);

        $this->assertTrue($validator->isValid());

        $validator
            ->length('short-string', 100)
            ->length('long-string', null, 4)
            ->length('string', 2, 4);

        $errors = $validator->getErrors();

        $this->assertSame(3, sizeof($errors));
        $this->assertEquals($errors['string'], $this->getErrorMessage('string', 'betweenLength', [2, 4]));
        $this->assertEquals($errors['long-string'], $this->getErrorMessage('long-string', 'maxLength', [4]));
        $this->assertEquals($errors['short-string'], $this->getErrorMessage('short-string', 'minLength', [100]));
    }

    //
    // TODO: Test money validation
    //

    //
    // TODO: Test notEmpty validation
    //

    // Test number validation
    public function testNumericValidation()
    {
        $validator = ($this->getValidator())
            ->numeric('number')
            ->numeric('float')
            ->numeric('string-int')
            ->numeric('string-float-valid');

        $this->assertTrue($validator->isValid());

        $validator
            ->numeric('string-float-invalid')
            ->numeric('string')
            ->numeric('unknown');

        $errors = $validator->getErrors();

        $this->assertSame(3, sizeof($errors));
        $this->assertEquals($errors['string'], $this->getErrorMessage('string', 'numeric'));
    }

    //
    // TODO: Test required validation
    //

    //
    // TODO: Test unique validation
    //

    /**
     * Return a validator with the test params
     *
     * @return Validator
     */
    private function getValidator() : Validator
    {
        return new Validator($this->params);
    }

    /**
     * Return a pre configure validator
     *
     * @param array $params
     * @return Validator
     */
    private function getPreConfiguredValidator(array $params) : Validator
    {
        $validator = (new Validator($params))
            ->email('email')
            ->numeric('number');

        return $validator;
    }

    /**
     * Return the validator error message
     *
     * @param string $key param you check
     * @param string $rule message name
     * @param array $array argument to complete the error message
     * @return ValidatorError
     */
    private function getErrorMessage(string $key, string $rule, array $array = []) : ValidatorError
    {
        return new ValidatorError($key, $rule, $array);
    }
}
