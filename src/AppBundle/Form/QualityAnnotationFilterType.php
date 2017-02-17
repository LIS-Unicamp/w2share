<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QualityAnnotationFilterType extends AbstractType
{
    protected $users;
    
    public function __construct($users)
    {
        $this->users = $users;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'choice', array(                    
                    'expanded' => false,
                    'mapped' => false,
                    'empty_value' => '',
                    'multiple' => false,
                    'choices' => $this->users,
                    'label'=>'Creator')) 
            ->add('filter', 'submit', array(
                    'icon'  => 'search',
                    'label' => 'Filter',
                    'attr' => array('class' => 'btn btn-primary')
                ))  
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'required' => false,
        ));
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'quality_annotation_filter';
    }
}
