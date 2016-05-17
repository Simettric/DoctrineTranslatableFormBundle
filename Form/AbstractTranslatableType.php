<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 16:21
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;



use Simettric\DoctrineTranslatableFormBundle\EventSubscriber\EntityPropertyFormSubscriber;
use Simettric\DoctrineTranslatableFormBundle\Interfaces\TranslatableFieldInterface;

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



    private $property_names=[];

    private $required_locale="en";

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

    public function setPropertyNames(array $property_names){
        $this->property_names = $property_names;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    protected function transformTranslatableFields(FormBuilderInterface $builder, array $options)
    {

        foreach($options["property_names"] as $name){

            $field = $builder->get($name);

            if(!$field->getType()->getInnerType() instanceof TranslatableFieldInterface)
                throw new \Exception("{$name} must implement TranslatableFieldInterface");


            $this->mapper->setLocales($options["locales"]);
            $this->mapper->setPropertyNames($options["property_names"]);
            $builder->setDataMapper($this->mapper);


            foreach($options["locales"] as $iso){


                $options = ["label"=>$iso];
                if($iso == $this->required_locale){
                    $options = ["required"=>true];
                }


                $field->add($iso, get_class($field->getType()->getParent()->getInnerType()), $options);

            }




        }

    }

    protected function configureTranslationOptions(OptionsResolver $resolver)
    {


        $resolver->setRequired(["property_names", "locales", "required_locale"]);

        $data = [
            'locales'         => $this->locales?:["en"],
            "required_locale" => $this->required_locale?:"en",
        ];

        if($this->property_names){
            $data["property_names"] = $this->property_names;
        }

        $resolver->setDefaults($data);
    }





}