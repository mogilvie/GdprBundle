<?php

/**
 * @author     Written by Mark Ogilvie <mark.ogilvie@specshaper.com>, 9 2016
 */
namespace SpecShaper\GdprBundle\Form\Type;

use SpecShaper\GdprBundle\Form\DataTransformer\PersonalDataTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalDataType extends AbstractType
{
    private $transformer;

    public function __construct(PersonalDataTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected issue does not exist',
        ));
    }

    public function getParent()
    {
        return TextType::class;
    }
}