<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityAnnotationType extends AbstractType
{
    protected $quality_dimensions;
    private $model;
    
    public function __construct($quality_dimensions, $model)
    {
        $this->quality_dimensions = $quality_dimensions;
        $this->model = $model;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('quality_dimension', 'choice', array(                    
                    'expanded' => false,
                    'multiple' => false,
                    'empty_value' => '',
                    'choices' => $this->quality_dimensions,
                    'label'=>'Quality Dimension'))
                
            ->add('value', 'text', array('label' => 'Value'))
            ->add('save', 'submit', array(
                    'label' => 'Save',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                    'attr' => array('class' => 'btn btn-primary')
                ))                                        
        ;
        
        $builder->get('quality_dimension')->addModelTransformer(new DataTransformer\QualityDimensionToStringTransformer($this->model));
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