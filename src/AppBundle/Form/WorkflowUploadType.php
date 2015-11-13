<?php
// src/AppBundle/Form/ProductType.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('label' => 'Title'))
            ->add('author', 'text', array('label' => 'Author'))
            ->add('description', 'textarea', array('label' => 'Description'))
            ->add('file', 'file', array('label' => 'XML File'))
            ->add('save', 'submit', array(
                    'label' => 'Salvar',
                    'attr' => array('class' => 'btn blue')
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
        return 'workflow';
    }
}