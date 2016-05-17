<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 21:07
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;


interface DataMapperInterface extends \Symfony\Component\Form\DataMapperInterface{

    public function setLocales(array $locales);

    public function setPropertyNames(array $property_names);

    public function getLocales();

    public function getPropertyNames();
} 