<?php

namespace CyberApp\TreeChoiceBundle\Validator\Constraint;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class TreeChoiceValidator extends ConstraintValidator
{
    const PARENT_ANNOTATION = 'Gedmo\Mapping\Annotation\TreeParent';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Validator constructor
     *
     * @param Registry $registry Doctrine registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (! $constraint instanceof TreeChoice) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\TreeParent');
        }

        $class = get_class($entity);
        $em = $this->registry->getManagerForClass($class);

        if (! $em) {
            throw new ConstraintDefinitionException(sprintf('Unable to find the object manager associated with an entity of class "%s".', $class));
        }

        $reader = new AnnotationReader;
        $metadata = $em->getClassMetadata($class);
        $properties = $metadata->getReflectionProperties();

        $idProperty = $metadata->getIdentifier();
        if (count($idProperty) > 1) {
            throw new ConstraintDefinitionException(
                'Entity is not allowed to have more than one identifier field to be '.
                'part of a unique constraint in: ' . $metadata->getName()
            );
        }

        $idProperty = $idProperty[0];

        $parentProperty = null;
        foreach ($properties as $property) {
            if ($reader->getPropertyAnnotation($property, static::PARENT_ANNOTATION)) {
                $parentProperty = $property->getName();
                break;
            }
        }

        if (null === $parentProperty) {
            throw new ConstraintDefinitionException(
                'Neither of property of class: ' . $class .
                ' does not contain annotation:' . static::PARENT_ANNOTATION
            );
        }

        $parentEntity = $this->propertyAccessor->getValue($entity, $parentProperty);
        if (null === $parentEntity) {
            return ;
        }

        if (! $parentEntity instanceof $class) {
            throw new ConstraintDefinitionException(
                'Parent property value should be instance of: ' . $class .
                ' but given instance of: ' . get_class($parentEntity)
            );
        }

        $entityId = $this->propertyAccessor->getValue($entity, $idProperty);
        $parentId = $this->propertyAccessor->getValue($parentEntity, $idProperty);

        if ($entityId === $parentId) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->atPath($parentProperty)
                ->addViolation();
        }
    }
}
