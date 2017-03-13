<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityMetricType extends AbstractType
{   
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('metric', 'text', array('label' => 'Metric'))
            ->add('description', 'textarea', array('label' => 'Description'))
            ->add('save', 'submit', array(
                    'label' => 'Add',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                    'attr' => array('class' => 'btn btn-primary')
                ))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\QualityMetric',
        ));
    }

    public function getName()
    {
        return 'qualitymetric';
    }
    
}