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


class QualityDataNatureController extends Controller
{
    /**
     * @Route("/quality-data-nature/list", name="quality-data-nature-list")
     */
    public function indexAction(Request $request)
    {
        $model = $this->get('model.qualitydatanature');

        $query = $model->findAllQualityDataNatures();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('quality-data-nature/list.html.twig', array(
            'pagination' => $pagination
        ));
    }


    /**
     * @Route("/quality-data-nature/add", name="quality-data-nature-add")
     */
    public function addAction(Request $request) {

        $model = $this->get('model.qualitydatanature');

        $modelQD = $this->get('model.qualitydimension');

        $qdn = new \AppBundle\Entity\QualityDataNature();
        $form = $this->createForm(new \AppBundle\Form\QualityDataNatureType($modelQD),
            $qdn,
            array(
                'action' => $this->generateUrl('quality-data-nature-add'),
                'method' => 'POST'
            ));

        $form->handleRequest($request);

        if ($form->isValid())
        {
            if(sizeof($qdn->getQualityDimensions())!=0){
            $model->insertQualityDataNature($qdn);
            $model->insertQualityDimensions($qdn);
            $this->get('session')
                ->getFlashBag()
                ->add('success', 'Quality Data Nature added!')
            ;

            return $this->redirect($this->generateUrl('quality-data-nature-add'));
            }
            else{
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'No Quality Dimension selected!')
                ;

                return $this->redirect($this->generateUrl('quality-data-nature-add'));

            }
        }

        return $this->render('quality-data-nature/form.html.twig', array(
            'form' => $form->createView(),
            'qdn' => $qdn
        ));
    }

    /**
     * @Route("/quality-data-nature/edit/{qualitydatanature_uri}", name="quality-data-nature-edit")
     */
    public function editAction(Request $request, $qualitydatanature_uri)
    {
        $model = $this->get('model.qualitydatanature');
        $uri = urldecode($qualitydatanature_uri);
        $qdn = $model->findOneQDN($uri);
        $modelQD = $this->get('model.qualitydimension');

        $form = $this->createForm(new \AppBundle\Form\QualityDataNatureType($modelQD), $qdn);

        $form->handleRequest($request);

        if ($form->isValid())
        {
            if(sizeof($qdn->getQualityDimensions())!=0) {
                $model->updateQualityDataNature($uri, $qdn);

                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Quality data Nature edited!');
            }
            else{
                $this->get('session')
                    ->getFlashBag()
                    ->add('error', 'No Quality Dimension selected!')
                ;

            }
        }

        return $this->render('quality-data-nature/form.html.twig', array(
            'form' => $form->createView(),
            'qdn' => $qdn
        ));
    }

    /**
     * @Route("/quality-data-nature/delete/{qualitydatanature_uri}", name="quality-data-nature-delete")
     */

    public function removeAction(Request $request, $qualitydatanature_uri)
    {
        $model = $this->get('model.qualitydatanature');
        $uri = urldecode($qualitydatanature_uri);

        $qdn = $model->findOneQDN($uri);

        if ($qdn)
        {
            if ($model->qualityDataNatureBeingUsed($qdn)){
                $this->get('session')
                    ->getFlashBag()
                    ->add(
                        'error', 'This quality data nature is being used by a quality evidence data and it cannot be deleted' );
            }
            elseif
            ($qdn->getIsMandatory()){
                $this->get('session')
                    ->getFlashBag()
                    ->add(
                        'error', 'This quality data nature is mandatory and it cannot be deleted' );
            }
            else {
                $model->deleteQualityDataNature($qdn->getUri());

                $this->get('session')
                    ->getFlashBag()
                    ->add('success', 'Quality data nature deleted!');
            }
        }
        //TO-DO verificar
        return $this->redirect($this->generateUrl('quality-data-nature-list'));
    }


}