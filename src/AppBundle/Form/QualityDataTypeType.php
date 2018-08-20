<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of QualityDataTypeType
 *
 * @author leila
 */
class QualityDataTypeType extends AbstractType
{
    public function __construct(\AppBundle\Model\QualityDimension $model) {
        $this->model = $model;
    }
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', 'text', array('label' => 'Name'))
            ->add('save', 'submit', array(
                'label' => 'Save',
                'icon' => 'glyphicon glyphicon-floppy-disk',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->add('qualitydimensions', 'choice', array(
                'choices' => $this->model->findAllQualityDimensions(),
                'choices_as_values' => true,
                'choice_label' => function($qd, $key, $index){
                    return $qd->getName();
                },
                'multiple' => true,
                'expanded' => true
            ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\QualityDataType',
        ));
    }

    public function getName()
    {
        return 'qualitydatatype';
    }

}
