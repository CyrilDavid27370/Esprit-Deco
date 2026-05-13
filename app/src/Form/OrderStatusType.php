<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OrderStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [
            'En attente de paiement' => Order::STATUS_PENDING_PAYMENT,
            'Payé'                   => Order::STATUS_PAID,
            'Expédié'                => Order::STATUS_SHIPPED,
            'Livré'                  => Order::STATUS_DELIVERED,
            'Annulé'                 => Order::STATUS_CANCELLED,
        ];

        $builder
            ->add('status', ChoiceType::class, [
                'label'   => 'Statut',
                'choices' => $choices,
                'constraints' => [
                    new Assert\NotBlank(message: 'Le statut est obligatoire.'),
                    new Assert\Choice(
                        choices: array_values($choices),
                        message: 'Statut invalide.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
