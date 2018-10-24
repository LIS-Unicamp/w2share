<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 24/08/18
 * Time: 17:12
 */

namespace AppBundle\Form;

use AppBundle\AppBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityEvidenceDataType extends AbstractType
{


    public function __construct(array $resources, array $qdts) {
        $this->resources = $resources;
        $this->qdts  = $qdts;

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder

            ->add('qualitydatanature', 'choice', array(
                'choices' => $this->qdts,
                'choices_as_values' => true,
                'choice_label' => function($qd, $key, $index){
                    return $qd->getName();
                }
            ))
            ->add('resource', 'choice', array(
                'choices' => $this->resources,
                'choices_as_values' => true,
                'choice_label' => function($qd, $key, $index){
                    return $qd->getFilename();
                }
            ))
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
            'data_class' => 'AppBundle\Entity\QualityEvidenceData',
        ));
    }

    public function getName()
    {
        return 'qualityevidencedata';
    }
}