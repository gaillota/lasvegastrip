<?php

namespace AG\LasVegasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Nom',
            ))
            ->add('size', 'text', array(
                'label' => 'Taille'
            ))
            ->add('takenAt', 'text', array(
                'label' => 'Date de la prise'
            ))
            ->add('comment', 'textarea', array(
                'label' => 'Commentaire',
                'required' => false,
            ))
            ->add('file', 'file', array(
                'label' => 'Photos',
                'attr' => array(
                    'multiple' => true
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Enregistrer'
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AG\LasVegasBundle\Entity\Photo'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ag_lasvegasbundle_photo';
    }
}
