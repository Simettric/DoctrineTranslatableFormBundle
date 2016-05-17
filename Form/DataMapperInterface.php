<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 21:07
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;

interface DataMapperInterface extends \Symfony\Component\Form\DataMapperInterface{

    public function setBuilder(FormBuilderInterface $builderInterface);

    public function add($name, $type, $options=[]);

    public function setLocales(array $locales);

    public function getLocales();

    public function setRequiredLocale($locale);

} 