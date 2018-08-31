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
     * @var \AppBundle\Entity\QualityDataType
     */
    private $qualitydatatype;



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
     * Set Quality Data TYpe
     *
     * @param \AppBundle\Entity\QualityDataType $qdt
     * @return QualityEvidenceData
     */
    public function setQualityDataType(\AppBundle\Entity\QualityDataType $qdt)
    {
        $this->qualitydatatype = $qdt;

        return $this;
    }

    /**
     * Get Quality Data Type
     *
     * @return \AppBundle\Entity\QualityDataType
     */
    public function getQualityDataType()
    {
        return $this->qualitydatatype;
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