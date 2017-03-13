<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{               
    /**
     * @Route("/search/concepts", name="search-concepts")
     */
    public function conceptsAction(Request $request)
    {
        $concept = $request->get('concept');
        $query = $request->get('query');
        
        $model = $this->get('model.search'); 
        $result = $model->concepts();
        
        return $this->render('search/concepts-select.html.twig', array(
            'concept' => $concept,
            'query' => $query,
            'result' => $result
        ));
    }         
    
    /**
     * @Route("/search/query", name="search-query")
     */
    public function queryAction(Request $request)
    {
        $concept = urldecode($request->get('concept'));
        $query = urldecode($request->get('query'));
        
        $model = $this->get('model.search'); 
        $result = $model->query($query, $concept);
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $result, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
                
        return $this->render('search/query-result.html.twig', array(
            'pagination' => $pagination
        ));
    }   
}