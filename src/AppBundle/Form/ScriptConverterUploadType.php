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
            ->add('provenance_file', 'file', array('label' => 'Provenance File', 'required' => false))
            ->add('workflow_file', 'file', array('label' => 'Workflow Spec (T2Flow) File', 'required' => false))
            ->add('send', 'submit', array(
                    'label' => 'Send',                    
                    'icon' => 'upload',
                    'attr' => array('class' => 'btn btn-primary')
                ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Workflow',
        ));
    }

    public function getName()
    {
        return 'script_converter';
    }
}