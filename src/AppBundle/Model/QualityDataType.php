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

class QualityDataType
{
    private $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function insertQualityDataType(\AppBundle\Entity\QualityDataType $qdt)
    {
        $uri = Utils::convertNameToUri("Quality Data Type", $qdt->getName());
        $qdt->setUri($uri);
        $query =
            "INSERT        
        { 
            GRAPH <" . $this->driver->getDefaultGraph('qualitydatatype') . "> 
            { 
                <" . $qdt->getUri() . "> a <w2share:QualityDataType>.
                <" . $qdt->getUri() . "> <w2share:qdtName> '" . $qdt->getName() . "'.
            }
        }";

        return $this->driver->getResults($query);

    }

    public function insertQualityDimensions(\AppBundle\Entity\QualityDataType $qdt)
    {
        $query = "        
        INSERT        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydatatype')."> 
            {\n";

        foreach($qdt->getQualityDimensions() as $dimension)
        {
            $query .= "<" . $qdt->getUri()."> <w2share:describesQualityDimension> <".$dimension->getUri().">.\n";
        }

        $query .= "   }
        }";

        return $this->driver->getResults($query);

    }



    public function deleteQualityDataType(\AppBundle\Entity\QualityDataType $qdt)
    {
        $query =
            "DELETE FROM <" . $this->driver->getDefaultGraph('qualitydatatype') . "> 
            {
                <" .$qdt->getUri() . "> a <w2share:QualityDataType>.
                <" . $qdt->getUri() . "> <w2share:qdtName>  '" . $qdt->getName(). "' .
                <" .$qdt->getUri() . "> ?property ?object.
            }
            WHERE 
            {
                <" .$qdt->getUri() . "> a <w2share:QualityDataType>.
                <" . $qdt->getUri() . "> <w2share:qdtName>  '" . $qdt->getName(). "' .
                <" .$qdt->getUri() . "> ?property ?object .

            }";

         return $this->driver->getResults($query);
    }

    public function findOneQDT($uri)
    {
        $query =
            "SELECT * WHERE        
        { 
            GRAPH <".$this->driver->getDefaultGraph('qualitydatatype')."> 
            { 
                <".$uri."> a <w2share:QualityDataType>.
                <".$uri."> <w2share:qdtName> ?name.
            }
        }";

        $qdt = $this->driver->getResults($query);

        $qualityDataType = new \AppBundle\Entity\QualityDataType();
        try {
            $qualityDataType->setUri($uri);
            $qualityDataType->setName($qdt[0]['name']['value']);
            $qualitydimensions = $this->findAllDimensionsByQDT($qualityDataType);
            $qualityDataType->setQualityDimensions($qualitydimensions);
        } catch (\Symfony\Component\Debug\Exception\ContextErrorException $ex) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Data Type not found!");
        }

        return $qualityDataType;
    }

    public function updateQualityDataType(\AppBundle\Entity\QualityDataType $qdt)
    {
        echo sizeof($qdt->getQualityDimensions());
        $this->deleteQualityDataType($qdt);
        echo sizeof($this->findAllDimensionsByQDT($qdt));
        $this->insertQualityDataType($qdt);
        $this->insertQualityDimensions($qdt);
        echo sizeof($this->findAllDimensionsByQDT($qdt));

    }

    public function findAllDimensionsByQDT(\AppBundle\Entity\QualityDataType $qdt)
    {
        $query =
            "SELECT DISTINCT * WHERE        
        { 
            <".$qdt->getUri()."> <w2share:describesQualityDimension> ?dimension .
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

    public function findAllQualityDataTypes()
    {
        $query =
                "SELECT * WHERE 
            {
                ?uri a <w2share:QualityDataType> ;
                <w2share:qdtName> ?name .
            }";

        $qdt_array = array();
        $qdts = $this->driver->getResults($query);
        for ($i = 0; $i < count($qdts); $i++)
        {
            $qdt = new \AppBundle\Entity\QualityDataType();
            $qdt->setUri($qdts[$i]['uri']['value']);
            $qdt->setName($qdts[$i]['name']['value']);
            $qdt->setQualityDimensions($this->findAllDimensionsByQDT($qdt));
            $qdt_array[$qdt->getUri()] = $qdt;
        }

        return $qdt_array;
    }
}