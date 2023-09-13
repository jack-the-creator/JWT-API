<?php

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class RegistrationFormTypeTest extends TypeTestCase
{
    /**
     * Adds extension for form validation to the form builder
     *
     * @return ValidatorExtension[]
     */
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator()
        ;

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $email = 'test@email.com';

        $formData = [
            'email' => $email,
            'password' => 'password123',
        ];

        $form = $this->submitFormData($formData);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSubmitted());
        $this->assertEquals($email, $form->get('email')->getData());
    }

    public function testSubmitInvalidEmailFormat(): void
    {
        $email = 'INVALID';

        $formData = [
            'email' => $email,
            'password' => 'password123',
        ];

        $form = $this->submitFormData($formData);

        $errors = $form->getErrors(true);
        $emailError = $errors[0];

        $this->assertFalse($form->isValid());
        $this->assertSame($emailError->getOrigin()->getName(), 'email');
        $this->assertSame($emailError->getMessage(), 'This value is not a valid email format.');
    }

    public function testPasswordTooShort(): void
    {
        $formData = [
            'email' => 'test@test.com',
            'password' => 'short',
        ];

        $form = $this->submitFormData($formData);

        $errors = $form->getErrors(true);
        $passwordError = $errors[0];

        $this->assertFalse($form->isValid());
        $this->assertSame($passwordError->getOrigin()->getName(), 'password');
        $this->assertSame($passwordError->getMessage(), 'Your password should be at least 6 characters.');
    }

    private function submitFormData(array $formData): FormInterface
    {
        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);
        $form->submit($formData);

        return $form;
    }
}