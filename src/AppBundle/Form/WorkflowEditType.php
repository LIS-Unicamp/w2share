<?php
namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class WorkflowEditType extends WorkflowAddType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('title', 'text')
            ->add('description', 'textarea')          
        ;
    }   
}