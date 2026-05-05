<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank(message: "le titre est obligatoire."),
                    new Length(min: 3, minMessage: "le titre doit faire au moins {{ limit }} caractères"),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(message : "La description est obligatoire."),
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'constraints' => [
                    new NotBlank(message: "Le prix est obligatoire."),
                    new Positive(message: "le prix doit etre positif."),
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une catégorie',
                'constraints' => [
                    new NotBlank(message: "la catégorie est obligatoire.")
                ]
            ])
            ->add('images', FileType::class, [
                'label' => 'Images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new Image([
                            'maxSize' => "2M",
                        ])
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
