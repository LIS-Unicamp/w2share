<?php
namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class QualityDimensionToStringTransformer implements DataTransformerInterface
{
    private $dao;

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($obj)
    {
        if (null === $obj) {
            return '';
        }

        return $obj->getUri();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $uri
     * @return Object|null
     * @throws TransformationFailedException if object is not found.
     */
    public function reverseTransform($uri)
    {
        // no string? It's optional, so that's ok
        if (!$uri) {
            return;
        }

        $obj = $this->dao
            ->findOneQualityDimension($uri)
        ;
        
        if (null === $obj) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An quality dimension with uri "%s" does not exist!',
                $uri
            ));
        }

        return $obj;
    }
}