<?php
/**
 *
 * Created by PhpStorm.
 * User: leila
 * Date: 03/08/18
 * Time: 15:21
 */

namespace AppBundle\Model;

use AppBundle\Utils\Utils;

class QualityDataNature
{
    private $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function insertQualityDataNature(\AppBundle\Entity\QualityDataNature $qdn)
    {
        $uri = Utils::convertNameToUri("Quality Data Nature", $qdn->getName());
        $qdn->setUri($uri);
        $query =
            "INSERT        
        { 
            GRAPH <" . $this->driver->getDefaultGraph('qualitydatanature') . "> 
            { 
                <" . $qdn->getUri() . "> a <w2share:QualityDataNature>.
                <" . $qdn->getUri() . "> <w2share:qdnName> '" . $qdn->getName() . "'.
                <" . $qdn->getUri() . "> <w2share:isMandatory> '" . $qdn->getIsMandatory() . "'.
                
            }
        }";

        return $this->driver->getResults($query);

    }

    public function insertQualityDimensions(\AppBundle\Entity\QualityDataNature $qdn)
    {
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydatanature')."> 
            {\n";

        foreach($qdn->getQualityDimensions() as $dimension)
        {
            $query .= "<" . $qdn->getUri()."> <w2share:describesQualityDimension> <".$dimension->getUri().">.\n";
        }

        $query .= "   }
        }";

        return $this->driver->getResults($query);

    }



    public function deleteQualityDataNature($uri)
    {
        $query =
            "DELETE FROM <" . $this->driver->getDefaultGraph('qualitydatanature') . "> 
            {
                <" .$uri. "> a <w2share:QualityDataNature>.
                <" .$uri . "> ?property ?object.
            }
            WHERE 
            {
                <" .$uri. "> a <w2share:QualityDataNature>.
                <" .$uri . "> ?property ?object .

            }";

         return $this->driver->getResults($query);
    }

    public function findOneQDN($uri)
    {
        $query =
            "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydatanature')."> 
            { 
                <".$uri."> a <w2share:QualityDataNature>.
                <".$uri."> <w2share:qdnName> ?name.
                <".$uri."> <w2share:isMandatory> ?bool.
            }
        }";

        $qdn = $this->driver->getResults($query);

        $qualityDataNature = new \AppBundle\Entity\QualityDataNature();
        try {
            $qualityDataNature->setUri($uri);
            $qualityDataNature->setName($qdn[0]['name']['value']);
            $qualityDataNature->setIsMandatory(boolval($qdn[0]['bool']['value']));
            $qualitydimensions = $this->findAllDimensionsByQDN($qualityDataNature);
            $qualityDataNature->setQualityDimensions($qualitydimensions);
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Data Nature not found!");
        }

        return $qualityDataNature;
    }

    public function updateQualityDataNature($uri, \AppBundle\Entity\QualityDataNature $qdn)
    {
        $this->deleteQualityDataNature($uri);
        $this->insertQualityDataNature($qdn);
        $this->insertQualityDimensions($qdn);

    }

    public function findAllDimensionsByQDN(\AppBundle\Entity\QualityDataNature $qdn)
    {
        $query =
            "SELECT DISTINCT * WHERE        
        { 
            <".$qdn->getUri()."> <w2share:describesQualityDimension> ?dimension .
            ?dimension a <w2share:QualityDimension> ;
            <w2share:qdName> ?name ;
            <w2share:valueType> ?valueType ;
            <rdfs:description> ?description ;
            <dc:creator> ?creator .
            ?creator <foaf:name> ?creator_name .
        }";

        $quality_dimension_array = array();
        $quality_dimensions = $this->driver->getResults($query);
        for ($i = 0; $i < count($quality_dimensions); $i++)
        {
            $qualityDimension = new \AppBundle\Entity\QualityDimension();
            $qualityDimension->setUri($quality_dimensions[$i]['dimension']['value']);
            $qualityDimension->setName($quality_dimensions[$i]['name']['value']);
            $qualityDimension->setDescription($quality_dimensions[$i]['description']['value']);
            $qualityDimension->setValueType($quality_dimensions[$i]['valueType']['value']);

            $creator = new \AppBundle\Entity\Person();
            $creator->setName($quality_dimensions[$i]['creator_name']['value']);
            $creator->setUri($quality_dimensions[$i]['creator']['value']);

            $qualityDimension->setCreator($creator);

            $quality_dimension_array[$qualityDimension->getUri()] = $qualityDimension;
        }

        return $quality_dimension_array;
    }

    public function findAllQualityDataNatures()
    {
        $query =
                "SELECT * WHERE 
            {
                ?uri a <w2share:QualityDataNature> ;
                <w2share:qdnName> ?name ;
                <w2share:isMandatory> ?bool.
            }";

        $qdn_array = array();
        $qdns = $this->driver->getResults($query);
        for ($i = 0; $i < count($qdns); $i++)
        {
            $qdn = new \AppBundle\Entity\QualityDataNature();
            $qdn->setUri($qdns[$i]['uri']['value']);
            $qdn->setName($qdns[$i]['name']['value']);
            $qdn->setIsMandatory($qdns[$i]['bool']['value']);
            $qdn->setQualityDimensions($this->findAllDimensionsByQDN($qdn));
            $qdn_array[$qdn->getUri()] = $qdn;
        }

        return $qdn_array;
    }

    public function findAllMandatoryQualityDataNatures()
    {
        $query =
            "SELECT * WHERE 
            {
                ?uri a <w2share:QualityDataNature> ;
                <w2share:qdnName> ?name ;
                <w2share:isMandatory> '1'.
            }";

        $qdn_array = array();
        $qdns = $this->driver->getResults($query);
        for ($i = 0; $i < count($qdns); $i++)
        {
            $qdn = new \AppBundle\Entity\QualityDataNature();
            $qdn->setUri($qdns[$i]['uri']['value']);
            $qdn->setName($qdns[$i]['name']['value']);
            $qdn->setIsMandatory(true);
            $qdn->setQualityDimensions($this->findAllDimensionsByQDN($qdn));
            $qdn_array[$qdn->getUri()] = $qdn;
        }

        return $qdn_array;
    }

    public function qualityDataNatureBeingUsed(\AppBundle\Entity\QualityDataNature $qdn)
    {
        $query = "SELECT ?qed WHERE
        { ?qed <w2share:hasDataNature> <".$qdn->getUri() . "> . 
        }";
        if (count( $this->driver->getResults($query)) > 0) {
            return true;
        }
        return false;
    }

}