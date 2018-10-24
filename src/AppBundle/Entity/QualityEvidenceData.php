<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 24/08/18
 * Time: 14:16
 */

namespace AppBundle\Entity;


class QualityEvidenceData
{

    /**
    * @var string
    */
    private $uri;

    /**
     * @var string
     */
    private $creator;


    /**
     * @var \DateTime
     */
    private $created_at_time;

    /**
     * @var \AppBundle\Entity\WROResource
     */
    private $resource;

    /**
     * @var \AppBundle\Entity\QualityDataNature
     */
    private $qualitydatanature;



    /**
     * @var \AppBundle\Entity\WRO
     */
    private $wro;


    /**
     * Set creator
     *
     * @param Person $creator
     *
     * @return Person
     */
    public function setCreator(Person $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get the creator
     *
     * @return Person
     */
    public function getCreator()
    {
        return $this->creator;
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
     * Get uri
     *
     * @return string
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;

    }


    /**
     * Set created_at_time
     *
     * @param \DateTime $createdAtTime
     * @return QualityEvidenceData
     */
    public function setCreatedAtTime($createdAtTime)
    {
        $this->created_at_time = $createdAtTime;

        return $this;
    }

    /**
     * Get created_at_time
     *
     * @return \DateTime
     */
    public function getCreatedAtTime()
    {
        return $this->created_at_time;
    }

    /**
     * Set Resource
     *
     * @param \AppBundle\Entity\WROResource $resource
     * @return QualityEvidenceData
     */
    public function setResource(\AppBundle\Entity\WROResource $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @return \AppBundle\Entity\WROResource
     */
    public function getResource()
    {
        return $this->resource;
    }


    /**
     * Set Quality Data Nature
     *
     * @param \AppBundle\Entity\QualityDataNature $qdn
     * @return QualityEvidenceData
     */
    public function setQualityDataNature(\AppBundle\Entity\QualityDataNature $qdn)
    {
        $this->qualitydatanature = $qdn;

        return $this;
    }

    /**
     * Get Quality Data Nature
     *
     * @return \AppBundle\Entity\QualityDataNature
     */
    public function getQualityDataNature()
    {
        return $this->qualitydatanature;
    }

    /**
     * Set wro
     *
     * @param \AppBundle\Entity\WRO $wro
     * @return QualityEvidenceData
     */
    public function setWro(\AppBundle\Entity\WRO $wro = null)
    {
        $this->wro = $wro;

        return $this;
    }

    /**
     * Get wro
     *
     * @return \AppBundle\Entity\WRO
     */
    public function getWro()
    {
        return $this->wro;
    }

}