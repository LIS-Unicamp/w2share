<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QualityMetricAnnotation
 */
class QualityMetricAnnotation
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $result;

    /**
     * @var \AppBundle\Entity\QualityMetric
     */
    private $quality_metric;


    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityMetricAnnotation
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    
        return $this;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set result
     *
     * @param string $result
     * @return QualityMetricAnnotation
     */
    public function setResult($result)
    {
        $this->result = $result;
    
        return $this;
    }

    /**
     * Get result
     *
     * @return string 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set quality_metric
     *
     * @param \AppBundle\Entity\QualityMetric $qualityMetric
     * @return QualityMetricAnnotation
     */
    public function setQualityMetric(\AppBundle\Entity\QualityMetric $qualityMetric = null)
    {
        $this->quality_metric = $qualityMetric;
    
        return $this;
    }

    /**
     * Get quality_metric
     *
     * @return \AppBundle\Entity\QualityMetric 
     */
    public function getQualityMetric()
    {
        return $this->quality_metric;
    }
}
