<?php
/**
 * Created by PhpStorm.
 * User: leila
 * Date: 01/08/18
 * Time: 12:15
 */

namespace AppBundle\Entity;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QualityEvidenceData
{

    /**
     * @var string
     */
    private $uri;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \AppBundle\Entity\Person
     */
    private $creator;

    /**
     * @var \AppBundle\Entity\QualityDataType
     */
    private $type;

    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->created_at = new \Datetime();
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return QualityEvidenceData
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
     * Set creator
     *
     * @param string $creator
     * @return QualityEvidenceData
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }


    /**
     * Set type
     *
     * @param \AppBundle\Entity\QualityDataType $qdt
     * @return QualityEvidenceData
     */
    public function setType(\AppBundle\Entity\QualityDataType $qdt = null)
    {
        $this->type = $qdt;

        return $this;
    }

    /**
     * Get workflow
     *
     * @return \AppBundle\Entity\QualityDataType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return QualityEvidenceData
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }



}