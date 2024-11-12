<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class RateComparisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('loan_amount', ChoiceType::class, [
                'choices' => [
                    50000 => 50000,
                    100000 => 100000,
                    200000 => 200000,
                    500000 => 500000
                ],
                'placeholder' => 'Sélectionner un montant',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le montant du prêt est obligatoire.',
                    ]),
                    new Assert\Choice([
                        'choices' => [50000, 100000, 200000, 500000],
                        'message' => 'Veuillez sélectionner un montant valide.',
                    ]),
                ]
            ])
            ->add('loan_duration', ChoiceType::class, [
                'choices' => [
                    15 => 15,
                    20 => 20,
                    25 => 25
                ],
                'placeholder' => 'Sélectionner une durée',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La durée du prêt est obligatoire.',
                    ])
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire.',
                    ])
                ]
            ])

            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'email est obligatoire.',
                    ]),
                    new Assert\Email([
                        'message' => 'L\'email "{{ value }}" n\'est pas valide.',
                    ])
                ]
            ])
            ->add('phone', TelType::class, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le numéro de téléphone est obligatoire.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\+?[0-9]{1,3}[0-9]{4,14}$/',
                        'message' => 'Le numéro de téléphone n\'est pas valide.',
                    ])
                ]
            ]);
    }
}
