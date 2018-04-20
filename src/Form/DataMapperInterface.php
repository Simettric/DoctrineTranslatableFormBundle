<?php

namespace Simettric\DoctrineTranslatableFormBundle\Form;

use Symfony\Component\Form\DataMapperInterface as BaseDataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface DataMapperInterface extends BaseDataMapperInterface
{
    public function setBuilder(FormBuilderInterface $builderInterface);

    public function add($name, $type, array $options = []);

    public function setLocales(array $locales);

    public function getLocales();

    public function setRequiredLocale($locale);
}
