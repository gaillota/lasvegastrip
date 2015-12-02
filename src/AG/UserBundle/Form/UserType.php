<?php

namespace AG\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
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
            ->add('username', 'text', array(
                'label' => 'Nom d\'utilisateur'
            ))
            ->add('email', 'email', array(
                'label' => 'Adresse mail'
            ))
            ->add('plain_password', 'repeated', array(
                'type' => 'password',
                'first_options' => array(
                    'label' => 'Mot de passe',
                    'attr' => array(
                        'placeholder' => 'Mot de passe',
                    )
                ),
                'second_options' => array(
                    'label' => ' ',
                    'attr' => array(
                        'placeholder' => 'Confirmation mot de passe',
                    )
                ),
            ))
            ->add('firstName', 'text', array(
                'label' => 'Prénom'
            ))
            ->add('lastName', 'text', array(
                'label' => 'Nom'
            ))
        ;
        if ($this->isSuperAdmin) {
            $builder
                ->add('roles', 'choice', array(
                    'label' => 'Droit(s)',
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => array(
                        'ROLE_USER' => 'Utilisateur simple',
                        'ROLE_ADMIN' => 'Administrateur',
                        'ROLE_SUPER_ADMIN' => 'Super Administrateur'
                    )
                ))
                ->add('enabled', 'checkbox', array(
                    'label' => 'Activé',
                    'required' => false,
                ))
                ->add('locked', 'checkbox', array(
                    'label' => 'Verrouillé',
                    'required' => false
                ))
            ;
        }
        $builder
            ->add('save', 'submit', array(
                'label' => 'Sauvegarder'
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AG\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ag_userbundle_user';
    }
}
