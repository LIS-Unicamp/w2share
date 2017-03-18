<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WROType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('wro_file', 'file', array('label' => 'WRO Bundle File', 'required' => true))
            ->add('save', 'submit', array(
                    'label' => 'Save',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                    'attr' => array('class' => 'btn btn-primary')
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\WRO',
        ));
    }

    public function getName()
    {
        return 'wro';
    }
}