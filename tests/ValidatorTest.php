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
        'short-string' => 'short',
        'money-valid-1' => '1.32',
        'money-valid-11' => '11.32',
        'money-valid-2' => '1,32',
        'money-valid-21' => '11,32',
        'money-valid-3' => 11.32,
        'money-invalid-1' => '111,321',
        'money-invalid-2' => 111.321,
        'empty' => '',
        'slug-1' => 'test-4-text-4',
        'slug-2' => 'test-4_text-4',
        'slug-3' => 'test-4--text-4',
        'slug-4' => 'test-4-'
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

        $this->assertCount(1, $validator->getParams());
    }

    // Test non existing params
    public function testNonExistingParam()
    {
        $validator = ($this->getValidator())
            ->email('non-existing-param');

        $this->assertCount(1, $validator->getErrors());
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

    // Test validation with a pre configure validator but not all params
    public function testPreConfigureValidationWithoutAllParams()
    {
        $params = [
            'email' => 'joe@doe.fr',
            'number' => 10
        ];

        $validator = (new Validator($params, null, true))
            ->email('email')
            ->email('no-existing-param')
            ->dateTime('date')
            ->numeric('number');

        $this->assertTrue($validator->isValid());
    }

    // Test validation with a pre configure validator but not all params
    public function testPreConfigureValidationWithoutAllParamsWithErrors()
    {
        $params = [
            'email' => 'joe@doe.fr',
            'number' => null
        ];

        $validator = (new Validator($params, null, true))
            ->email('email')
            ->email('no-existing-param')
            ->numeric('number');

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

        $this->assertCount(3, $validator->getErrors());
    }

    // Test email validation
    public function testEmailValidation()
    {
        $validator = ($this->getValidator())
            ->email('email');

        $this->assertTrue($validator->isValid());

        $validator
            ->email('invalid-email');

        $this->assertFalse($validator->isValid());
    }

    // Test extension validation
    public function testExtension()
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError', 'getClientFileName', 'getClientMediaType'])
            ->getMock();
        $file->expects($this->any())->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file->expects($this->any())->method('getClientFileName')->willReturn('mock.jpg');
        $file->expects($this->any())
            ->method('getClientMediaType')
            ->will($this->onConsecutiveCalls('image/jpeg', 'fake/php'));

        //TODO Assert here

        $file2 = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError', 'getClientFileName', 'getClientMediaType'])
            ->getMock();
        $file2->expects($this->any())->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file2->expects($this->any())->method('getClientFileName')->willReturn('mock.png');
        $file2->expects($this->any())
            ->method('getClientMediaType')
            ->will($this->onConsecutiveCalls('image/png', 'fake/php'));

        //TODO Assert here

        $this->assertTrue(true); //tmp
    }

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

        $this->assertCount(3, $validator->getErrors());
    }

    // Test money validation
    public function testMoneyValidation()
    {
        $validator = ($this->getValidator())
            ->money('money-valid-1')
            ->money('money-valid-11')
            ->money('money-valid-2')
            ->money('money-valid-21')
            ->money('money-valid-3');

        $this->assertTrue($validator->isValid());

        $validator
            ->money('string')
            ->money('money-invalid-1')
            ->money('money-invalid-2');

        $this->assertCount(3, $validator->getErrors());
    }

    // Test notEmpty validation
    public function testNotEmptyValisation()
    {
        $validator = ($this->getValidator())
            ->notEmpty('string')
            ->notEmpty('number');

        $this->assertTrue($validator->isValid());

        $validator
            ->notEmpty('unknown')
            ->notEmpty('empty');

        $this->assertCount(2, $validator->getErrors());
    }

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

        $this->assertCount(3, $validator->getErrors());
    }

    // Test slug validation
    public function testSlugValidation()
    {
        $validator = ($this->getValidator())
            ->slug('slug-1');

        $this->assertTrue($validator->isValid());

        $validator
            ->slug('slug-2')
            ->slug('slug-3')
            ->slug('slug-4');

        $this->assertCount(3, $validator->getErrors());
    }

    // Test upload validation
    public function testUploadFile()
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();
        //$file->expects($this->once())->method('getError')->willReturn(UPLOAD_ERR_OK);
        $file2 = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();
        //$file2->expects($this->once())->method('getError')->willReturn(UPLOAD_ERR_CANT_WRITE);

        $params = [
            'image-1' => $file,
            'image-2' => $file2
        ];

        $validator = new Validator($params);

        //TODO Assert here

        //TODO Assert here

        $this->assertTrue(true); //tmp
    }

    // Test required validation normal
    public function testRequiedValidation()
    {
        $validator = ($this->getValidator())
            ->required('email');

        $this->assertTrue($validator->isValid());

        $validator
            ->required('no-exists');

        $this->assertFalse($validator->isValid());
    }

    // Test required validation on with templated validator
    public function testRequiedValidationWith()
    {
        $params = [
            'email' => 'joe@doe.fr',
            'number' => 10
        ];

        $validator = (new Validator($params, null, true))
            ->email('email')
            ->email('no-existing-param')
            ->dateTime('date')
            ->numeric('number')
            ->required('email');

        $this->assertTrue($validator->isValid());

        $validator
            ->required('date');

        $this->assertFalse($validator->isValid());
    }

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
