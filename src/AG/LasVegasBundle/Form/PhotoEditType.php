<?php
/**
 * Created by PhpStorm.
 * User: Maître
 * Date: 12/05/2015
 * Time: 11:00
 */

namespace AG\LasVegasBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PhotoEditType extends AbstractType
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
            ->remove('file')
            ->remove('size')
            ->remove('save');
        if (!$this->isSuperAdmin) {
            $builder
                ->remove('takenAt');
        }
        if ($this->isSuperAdmin) {
            $builder
            ->add('author', 'entity', array(
                'class' => 'AGUserBundle:User',
                'label' => 'Auteur'
            ))
            ->add('album', 'entity', array(
                'class' => 'AGLasVegasBundle:Album',
                'label' => 'Album'
            ));
        }
        $builder
            ->add('save', 'submit', array(
                'label' => 'Enregistrer',
            ))
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ag_lasvegasbundle_photo_edit';
    }

    /**
     * @return PhotoType|null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return new PhotoType();
    }
}
