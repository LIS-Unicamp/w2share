<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

namespace AppBundle\Form;

/**
 * Description of QualityDimensionAddType
 *
 * @author joana
 */
class QualityDimensionAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text', array('label' => 'Name'))
            ->add('description', 'textarea', array('label' => 'Description'))
            ->add('type', 'text', array('label' => 'Type'))
            ->add('save', 'submit', array(
                    'label' => 'Salvar',
                    'attr' => array('class' => 'btn blue')
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
