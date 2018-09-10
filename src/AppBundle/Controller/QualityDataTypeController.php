<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 03/08/18
 * Time: 16:19
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class QualityDataTypeController extends Controller
{
    /**
     * @Route("/quality-data-type/list", name="quality-data-type-list")
     */
    public function indexAction(Request $request)
    {
        $model = $this->get('model.qualitydatatype');

        $query = $model->findAllQualityDataTypes();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('quality-data-type/list.html.twig', array(
            'pagination' => $pagination
        ));
    }


    /**
     * @Route("/quality-data-type/add", name="quality-data-type-add")
     */
    public function addAction(Request $request) {

        $model = $this->get('model.qualitydatatype');

        $modelQD = $this->get('model.qualitydimension');

        $qdt = new \AppBundle\Entity\QualityDataType();
        $form = $this->createForm(new \AppBundle\Form\QualityDataTypeType($modelQD),
            $qdt,
            array(
                'action' => $this->generateUrl('quality-data-type-add'),
                'method' => 'POST'
            ));

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $model->insertQualityDataType($qdt);
            $model->insertQualityDimensions($qdt);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Data Type added!')
            ;

            return $this->redirect($this->generateUrl('quality-data-type-add'));
        }

        return $this->render('quality-data-type/form.html.twig', array(
            'form' => $form->createView(),
            'qdt' => $qdt
        ));
    }

    /**
     * @Route("/quality-data-type/edit/{qualitydatatype_uri}", name="quality-data-type-edit")
     */
    public function editAction(Request $request, $qualitydatatype_uri)
    {
        $model = $this->get('model.qualitydatatype');
        $uri = urldecode($qualitydatatype_uri);
        $qdt = $model->findOneQDT($uri);
        echo $qdt->getIsMandatory();
        $modelQD = $this->get('model.qualitydimension');

        $form = $this->createForm(new \AppBundle\Form\QualityDataTypeType($modelQD), $qdt);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            $model->updateQualityDataType($qdt);

            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality data type edited!')
            ;
        }

        return $this->render('quality-data-type/form.html.twig', array(
            'form' => $form->createView(),
            'qdt' => $qdt
        ));
    }

    /**
     * @Route("/quality-data-type/delete/{qualitydatatype_uri}", name="quality-data-type-delete")
     */

    public function removeAction(Request $request, $qualitydatatype_uri)
    {
        $model = $this->get('model.qualitydatatype');
        $uri = urldecode($qualitydatatype_uri);

        $qdt = $model->findOneQDT($uri);

        if ($qdt)
        {
            if($model->qualityDataTypeBeingUsed($qdt)){
                $this->get('session')
                    ->getFlashBag()
                    ->add(
                        'error', 'This quality data type is being used by a quality evidence data and it cannot be deleted' );
            }

            if($qdt->getIsMandatory()){
                $this->get('session')
                    ->getFlashBag()
                    ->add(
                        'error', 'This quality data type is mandatory and it cannot be deleted' );
            }
            else {
                $model->deleteQualityDataType($qdt);

                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Quality data type deleted!');
            }
        }
        //TO-DO verificar
        return $this->redirect($this->generateUrl('quality-data-type-list'));
    }


}