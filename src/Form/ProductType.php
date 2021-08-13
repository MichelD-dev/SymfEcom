<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit: ',
                'attr' => [
                    'class' => 'w3-margin-bottom w3-input'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit: ',
                'attr' => [
                    'class' => 'w3-margin-bottom w3-input'
                ]
            ])
            ->add('price', TextType::class, [
                'label' => 'Prix: ',
                'attr' => [
                    'class' => 'w3-margin-bottom w3-input'
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Quantité:',
                'attr' => [
                    'class' => 'w3-margin-bottom'
                ]
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags: ',
                'class' => Tag::class, // Le nom de l'Entity que nous voulons attribuer à ce champ
                'choice_label' => 'name', // L'attribut de notre Entity que nous voulons utiliser comme label pour chaque choix
                'expanded' => true, // change l'affichage en checks plutôt que list
                'multiple' => true, // permet de faire un choix multiple, renvoie ici une erreur si false, parce que nous sommes en ManyToMany (nous récupérons un TABLEAU de tags, même si choix unique)
                'attr' => [
                    'class' => 'w3-margin-bottom w3-checkbox',
                    'style' => 'display: grid;  grid-template-columns: 1rem 1fr 1fr 1fr;'
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie: ',
                'class' => Category::class, // Le nom de l'Entity que nous voulons 
                'choice_label' => 'name',
                'expanded' => false, // change l'affichage en boutons plutôt que liste
                'multiple' => false, // permet de faire un choix multiple
                'attr' => [
                    'class' => 'w3-margin-bottom'
                ]
            ])
            ->add('valider', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => '	
                    w3-btn w3-green w3-margin-bottom w3-padding-8',
                    'style' => 'margin-top: 5px;'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
