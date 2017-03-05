<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScriptConverterUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder            
            ->add('script_file', 'file', array('label' => 'Script File'))
            ->add('send', 'submit', array(
                    'label' => 'Send',
                    'attr' => array('class' => 'btn blue')
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ScriptConverter',
        ));
    }

    public function getName()
    {
        return 'script_converter';
    }
}