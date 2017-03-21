<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//Esta clase nao esta sendo utilizada. O formulario foi construido a mao!
class QualityMetricSelectType extends AbstractType
{
    protected $quality_metrics;
    
    public function __construct($quality_metrics)
    {
        $this->quality_metrics = $quality_metrics;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('metric', 'choice', array(                    
                    'expanded' => true,
                    'multiple' => false,
                    //'mapped' => false,
                    'empty_value' => '',
                    'choices' => array($this->quality_metrics)
                ))  
            ->add('save', 'submit', array(
                    'label' => 'Add',
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
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'qualitymetric_select';
    }
}
