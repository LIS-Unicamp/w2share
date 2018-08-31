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


    public function __construct(\AppBundle\Dao\WRODAO $WROdao, \AppBundle\Model\QualityDataType $model, \AppBundle\Entity\WRO $wro) {
        $this->dao = $WROdao;
        $this->model = $model;
        $this->wro  = $wro;

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder

            ->add('qualitydatatype', 'choice', array(
                'choices' => $this->model->findAllQualityDataTypes(),
                'choices_as_values' => true,
                'choice_label' => function($qd, $key, $index){
                    return $qd->getName();
                }
            ))
            ->add('resource', 'choice', array(
                'choices' => $this->dao->findAllResourcesByWRO($this->wro),
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