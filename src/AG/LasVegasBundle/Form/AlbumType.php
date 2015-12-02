<?php

namespace AG\LasVegasBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AlbumType extends AbstractType
{
    /**
     * @var boolean
     */
    private $isSuperAdmin;

    public function __construct($roleFlag)
    {
        $this->isSuperAdmin = $roleFlag;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Nom'
            ))
            ->add('description', 'textarea', array(
                'label' => 'Description'
            ));
        if ($this->isSuperAdmin) {
            $builder
                ->add('author', 'entity', array(
                    'class' => 'AGUserBundle:User',
                    'label' => 'Auteur'
                ));
        }
        $builder
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
            'data_class' => 'AG\LasVegasBundle\Entity\Album'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ag_lasvegasbundle_album';
    }
}
