<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WROResourceType extends AbstractType
{    
    public function __construct() 
    {
        $concepts = array
        (
            'http://purl.org/wf4ever/roterms#Conclusion',
            'http://purl.org/wf4ever/roterms#Hypothesis',
            'http://purl.org/wf4ever/roterms#Result',
            'http://purl.org/wf4ever/roterms#ExampleRun',
            'http://purl.org/wf4ever/roterms#Paper',
            'http://purl.org/wf4ever/roterms#ProspectiveRun',
            'http://purl.org/wf4ever/roterms#ResearchQuestion',
            'http://purl.org/wf4ever/roterms#ResultGenerationRun',
            'http://purl.org/wf4ever/roterms#Sketch',
            'http://purl.org/wf4ever/wf4ever#Script',
            'http://purl.org/wf4ever/wf4ever#Image',
            'http://purl.org/wf4ever/wf4ever#Image',
            'http://purl.org/wf4ever/wfdesc#Workflow',
            'http://purl.org/wf4ever/wfprov#WorkflowRun'
        );        
        
        $this->concepts = array_combine($concepts, $concepts);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filename', 'text', array('label' => 'Filename', 'required' => true))
            ->add('title', 'text', array('required' => true))
            ->add('description', 'text', array('required' => true))
            ->add('type', 'choice', array(                    
                    'expanded' => false,
                    'empty_value' => '',
                    'multiple' => false,
                    'choices' => $this->concepts)
                ) 
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
            'data_class' => 'AppBundle\Entity\WROResource',
        ));
    }

    public function getName()
    {
        return 'wro_resource';
    }
}