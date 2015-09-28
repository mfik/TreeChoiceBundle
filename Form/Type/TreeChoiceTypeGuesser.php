<?php

namespace CyberApp\TreeChoiceBundle\Form\Type;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;

class TreeChoiceTypeGuesser extends DoctrineOrmTypeGuesser
{
    const TREE_ANNOTATION = 'Gedmo\Mapping\Annotation\Tree';
    const TREE_LEVEL_ANNOTATION = 'Gedmo\Mapping\Annotation\TreeLevel';
    const TREE_PARENT_ANNOTATION = 'Gedmo\Mapping\Annotation\TreeParent';

    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        $metadata = $this->getClassMetadata($class);

        if (! $metadata->hasAssociation($property)) {
            return null;
        }

        $multiple = $metadata->isCollectionValuedAssociation($property);
        $annotationReader = new AnnotationReader();
        $associationMapping = $metadata->getAssociationMapping($property);
        $associationMetadata = $this->getClassMetadata($associationMapping['targetEntity']);

        if (null === $annotationReader->getClassAnnotation($associationMetadata->getReflectionClass(), static::TREE_ANNOTATION)) {
            return null;
        }

        $levelProperty = null;
        $parentProperty = null;

        foreach ($associationMetadata->getReflectionProperties() as $property) {
            if ($annotationReader->getPropertyAnnotation($property, static::TREE_LEVEL_ANNOTATION)) {
                $levelProperty = $property->getName();
            }

            if ($annotationReader->getPropertyAnnotation($property, static::TREE_PARENT_ANNOTATION)) {
                $parentProperty = $property->getName();
            }
        }

        if (null === $levelProperty || null === $parentProperty) {
            return null;
        }

        return new TypeGuess('tree_choice', [
            'class' => $associationMapping['targetEntity'],
            'multiple' => $multiple,
            'level_property' => $levelProperty,
            'parent_property' => $parentProperty,
        ], Guess::VERY_HIGH_CONFIDENCE);
    }

    /**
     * {@inheritdoc}
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function guessPattern($class, $property)
    {
    }

    /**
     * Get class metadata
     *
     * @param string $class Class name
     *
     * @return ClassMetadataInfo
     */
    protected function getClassMetadata($class)
    {
        $metadata = parent::getMetadata($class);

        if (0 === count($metadata)) {
            return null;
        }

        return $metadata[0];
    }
}
