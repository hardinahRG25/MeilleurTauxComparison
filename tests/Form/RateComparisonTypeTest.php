<?php

namespace App\Tests\Form;

use App\Form\RateComparisonType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class RateComparisonTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'loan_amount' => 100000,
            'loan_duration' => 20,
            'name' => 'Hard Inah',
            'email' => 'hardinah.raj@example.com',
            'phone' => '+1234567890',
        ];

        $form = $this->factory->create(RateComparisonType::class);

        $form->submit($formData);

        // Check if data set form
        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
    }

    public function testSubmitInvalidData()
    {
        $formData = [
            'loan_amount' => 300000, // invalid data
            'loan_duration' => 10, // invalid data
            'name' => '', // name empty
            'email' => 'not-an-email', // Email invalid
            'phone' => '12345', // Phone invalid
        ];

        $form = $this->factory->create(RateComparisonType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertFalse($form->isValid());
        $errors = $form->getErrors(true, true);

        $this->assertGreaterThan(0, $errors->count());
    }

    public function testRequiredFields()
    {
        $formData = [];

        $form = $this->factory->create(RateComparisonType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());

        $errors = $form->getErrors(true, true);
        $this->assertGreaterThan(0, $errors->count());
    }
}
