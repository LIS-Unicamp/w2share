<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityAnnotationType extends AbstractType
{
    protected $quality_dimensions;
    
    public function __construct($quality_dimensions)
    {
        $this->quality_dimensions = array_combine($quality_dimensions, $quality_dimensions);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('quality_dimension', 'choice', array(                    
                    'expanded' => false,
                    'multiple' => false,
                    'choices' => $this->quality_dimensions,
                    'label'=>'Quality Dimension')) 
            ->add('value', 'text', array('label' => 'Value'))
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
            'data_class' => 'AppBundle\Entity\QualityAnnotation',
        ));
    }

    public function getName()
    {
        return 'qualityannotation';
    }
    
}