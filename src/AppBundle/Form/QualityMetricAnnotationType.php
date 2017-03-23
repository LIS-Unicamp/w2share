<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityMetricAnnotationType extends AbstractType
{   
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {   
        $builder
            ->add('result', 'text', array('label' => 'Result'))
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
            'data_class' => 'AppBundle\Entity\QualityMetricAnnotation',
        ));
    }

    public function getName()
    {
        return 'qualitymetric_annotation';
    }
    
}