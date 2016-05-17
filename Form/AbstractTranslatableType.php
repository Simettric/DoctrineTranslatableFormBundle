<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 16:21
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;




use Symfony\Component\Form\Exception;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;



/**
 *
 * stof_doctrine_extensions:
        default_locale: %locale%
        translation_fallback: true
        persist_default_translation: true
        orm:
            default:
                translatable: true
 *
 * Class AbstractType
 * @package Simettric\DoctrineTranslatableFormBundle\Form
 */
abstract class AbstractTranslatableType extends \Symfony\Component\Form\AbstractType{


    private $locales=[];

    private $required_locale;

    /**
     * @var DataMapperInterface
     */
    private $mapper;


    function __construct(DataMapperInterface $dataMapper){
        $this->mapper = $dataMapper;
    }


    public function setRequiredLocale($iso){
        $this->required_locale = $iso;
    }

    public function setLocales(array $locales){
        $this->locales = $locales;
    }


    /**
     * @param FormBuilderInterface $builderInterface
     * @param array $options
     * @return DataMapperInterface
     */
    protected function createTranslatableMapper(FormBuilderInterface $builderInterface, array $options){



        $this->mapper->setBuilder($builderInterface, $options);
        $this->mapper->setLocales($options["locales"]);
        $this->mapper->setRequiredLocale($options["required_locale"]);
        $builderInterface->setDataMapper($this->mapper);

        return $this->mapper;
    }




    protected function configureTranslationOptions(OptionsResolver $resolver)
    {


        $resolver->setRequired(["locales", "required_locale"]);

        $data = [
            'locales'         => $this->locales?:["en"],
            "required_locale" => $this->required_locale?:"en",
        ];


        $resolver->setDefaults($data);
    }





}