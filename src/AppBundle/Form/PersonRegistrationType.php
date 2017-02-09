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
class PersonRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text', array('label' => 'Name'))
            ->add('email', 'email', array('label' => 'E-mail'))
            ->add('password', 'password', array('label' => 'Password'))
            ->add('confirm_password', 'password', array('label' => 'Confirm Password'))
            ->add('homepage', 'url', array('label' => 'Description'))
            ->add('save', 'submit', array(
                    'label' => 'Register Now',
                    'attr' => array('class' => 'form-control btn btn-register')
                ))
        ;
        
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Person',
        ));
    }

    public function getName()
    {
        return 'person';
    }
    
}
