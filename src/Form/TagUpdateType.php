<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TagUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tags', EntityType::class, [
                'label' => 'Tags: ',
                'class' => Tag::class, // Le nom de l'Entity que nous voulons attribuer à ce champ
                'choice_label' => 'name', // L'attribut de notre Entity que nous voulons utiliser comme label pour chaque choix
                'expanded' => true, // change l'affichage en checks plutôt que list
                'multiple' => true, // permet de faire un choix multiple, renvoie ici une erreur si false, parce que nous sommes en ManyToMany (nous récupérons un TABLEAU de tags, même si choix unique)
                'attr' => [
                    'class' => 'w3-margin-bottom w3-checkbox',
                    'style' => 'display: block;'
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
