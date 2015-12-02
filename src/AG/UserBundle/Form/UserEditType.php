<?php

namespace AG\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class UserEditType extends AbstractType
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
            ->remove('plain_password')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ag_userbundle_user_edit';
    }

    /**
     * @return UserType|null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return new UserType($this->isSuperAdmin);
    }
} 