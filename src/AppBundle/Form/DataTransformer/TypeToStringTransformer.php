<?php
namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TypeToStringTransformer implements DataTransformerInterface
{    
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

        return $obj;
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
        return $uri;
    }
}