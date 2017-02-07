<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityAnnotationAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('value', 'text', array('label' => 'Value'))
            ->add('save', 'submit', array(
                    'label' => 'Add',
                    'attr' => array('class' => 'btn btn-primary')
                ))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\QualityAnnotation',
        ));
    }

    public function getName()
    {
        return 'qualityannotation';
    }
    
}