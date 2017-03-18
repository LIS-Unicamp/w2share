<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of QualityDimensionAddType
 *
 * @author joana
 */
class QualityDimensionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text', array('label' => 'Name'))
            ->add('description', 'textarea', array('label' => 'Description'))
            ->add('valueType', 'text', array('label' => 'Type'))
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
            'data_class' => 'AppBundle\Entity\QualityDimension',
        ));
    }

    public function getName()
    {
        return 'qualitydimension';
    }
    
}
