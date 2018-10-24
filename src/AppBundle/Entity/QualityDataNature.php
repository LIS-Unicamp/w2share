<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 01/08/18
 * Time: 12:21
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;


class QualityDataNature
{

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $isMandatory;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $qualitydimensions;

    public function __construct(){
            $this->qualitydimensions = [];
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityDataNature
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
     * Set isMandatory
     *
     * @param boolean $isMandatory
     * @return QualityDataNature
     */
    public function setIsMandatory($isMandatory)
    {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    /**
     * Get isMandatory
     *
     * @return string
     */
    public function getIsMandatory()
    {
        return $this->isMandatory;
    }


    /**
     * Add dimensions
     *
     * @return QualityDataNature
     */
    public function setQualityDimensions($qualitydimensions)
    {

        $this->qualitydimensions = $qualitydimensions;

        return $this;
    }

    /**
     * Add dimension
     *
     * @param \AppBundle\Entity\QualityDimension $dimension
     * @return QualityDataNature
     */
    public function addQualityDimension(\AppBundle\Entity\QualityDimension $dimension)
    {
        $this->qualitydimensions[] = $dimension;

        return $this;
    }

    /**
     * Remove dimension
     *
     * @param \AppBundle\Entity\QualityDimension $dimension
     */
    public function removeQualityDimension(\AppBundle\Entity\QualityDimension $dimension)
    {
        $key = array_search($dimension, $this->qualitydimensions);
        unset($this->qualitydimensions[$key]);
    }

    /**
     * Get dimensions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQualityDimensions()
    {
        return $this->qualitydimensions;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return QualityDataNature
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }




}
