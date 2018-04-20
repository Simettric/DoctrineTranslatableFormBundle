<?php

namespace Simettric\DoctrineTranslatableFormBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTranslatableType extends AbstractType
{
    /**
     * @var DataMapperInterface
     */
    private $mapper;

    /**
     * @var string[]
     */
    private $locales = ['en'];

    /**
     * @var string
     */
    private $requiredLocale = 'en';

    function __construct(DataMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    public function setRequiredLocale($requiredLocale)
    {
        $this->requiredLocale = $requiredLocale;
    }

    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * @param FormBuilderInterface $builderInterface
     * @param array                $options
     *
     * @return DataMapperInterface
     */
    protected function createTranslatableMapper(FormBuilderInterface $builderInterface, array $options)
    {
        $this->mapper->setBuilder($builderInterface, $options);
        $this->mapper->setLocales($options['locales']);
        $this->mapper->setRequiredLocale($options['required_locale']);

        $builderInterface->setDataMapper($this->mapper);

        return $this->mapper;
    }

    protected function configureTranslationOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'locales',
                'required_locale',
            ])
            ->setDefaults([
                'locales' => $this->locales,
                'required_locale' => $this->requiredLocale,
            ])
        ;
    }
}
