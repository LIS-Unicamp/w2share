<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 01/08/18
 * Time: 12:21
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;


class QualityDataType
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
     * @return QualityDataType
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
     * Add dimensions
     *
     * @return QualityDataType
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
     * @return QualityDataType
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
        $this->qualitydimensions->removeElement($dimension);
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
     * @return QualityDataType
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
