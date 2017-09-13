<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 20:58
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;


use Doctrine\ORM\EntityManager;
use Simettric\DoctrineTranslatableFormBundle\Interfaces\TranslatableFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception;

class DataMapper implements DataMapperInterface{


    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TranslationRepository
     */
    private $repository;

    /**
     * @var FormBuilderInterface
     */
    private $builder;

    private $translations=[];


    private $locales=[];

    private $required_locale;

    private $property_names=[];




    public function __construct(EntityManager $entityManager){

        $this->em = $entityManager;
        $this->repository = $this->em->getRepository('Gedmo\Translatable\Entity\Translation');

    }

    public function setBuilder(FormBuilderInterface $builderInterface){
        $this->builder = $builderInterface;
    }

    public function setRequiredLocale($locale){
        $this->required_locale = $locale;
    }

    public function setLocales(array $locales){
        $this->locales = $locales;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function getTranslations($entity){

        if(!count($this->translations)){
            $this->translations = $this->repository->findTranslations($entity);
        }

        return $this->translations;

    }


    /**
     * @param $name
     * @param $type
     * @param array $options
     * @return DataMapper
     * @throws \Exception
     */
    public function add($name, $type, $options=[])
    {

        $this->property_names[] = $name;

        $field = $this->builder
            ->add($name, $type)
            ->get($name);

        if(!$field->getType()->getInnerType() instanceof TranslatableFieldInterface)
            throw new \Exception("{$name} must implement TranslatableFieldInterface");

        foreach($this->locales as $iso){

            $options = [
                "label"   => $iso,
                "required"=> $iso == $this->required_locale
            ];

            $field->add($iso, get_class($field->getType()->getParent()->getInnerType()), $options);

        }

        return $this;

    }


    /**
     * Maps properties of some data to a list of forms.
     *
     * @param mixed $data Structured data.
     * @param FormInterface[] $forms A list of {@link FormInterface} instances.
     *
     * @throws Exception\UnexpectedTypeException if the type of the data parameter is not supported.
     */
    public function mapDataToForms($data, $forms)
    {

        foreach($forms as $form){

            $translations = $this->getTranslations($data);

            if(false !== in_array($form->getName(), $this->property_names)) {

                $values = [];
                foreach($this->getLocales() as $iso){

                    if(isset($translations[$iso])){
                        $values[$iso] =  $translations[$iso][$form->getName()];
                    }

                }
                $form->setData($values);

            }else{

                if(false === $form->getConfig()->getOption("mapped") || null === $form->getConfig()->getOption("mapped")){
                    continue;
                }

                $accessor = PropertyAccess::createPropertyAccessor();
                $form->setData($accessor->getValue($data, $form->getName()));

            }

        }

    }

    /**
     * Maps the data of a list of forms into the properties of some data.
     *
     * @param FormInterface[] $forms A list of {@link FormInterface} instances.
     * @param mixed $data Structured data.
     *
     * @throws Exception\UnexpectedTypeException if the type of the data parameter is not supported.
     */
    public function mapFormsToData($forms, &$data)
    {
        /**
         * @var $form FormInterface
         */
        foreach ($forms as $form) {

            $entityInstance = $data;


            if(false !== in_array($form->getName(), $this->property_names)) {

                $meta = $this->em->getClassMetadata(get_class($entityInstance));
                $listener = new TranslatableListener();
                $listener->loadMetadataForObjectClass($this->em, $meta);
                $config = $listener->getConfiguration($this->em, $meta->name);

                $translations = $form->getData();
                foreach($this->getLocales() as $iso) {
                    if(isset($translations[$iso])){
                        if (isset($config['translationClass'])) {
                            $t = $this->em->getRepository($config['translationClass'])
                                ->translate($entityInstance, $form->getName(), $iso, $translations[$iso]);
                            $this->em->persist($entityInstance);
                            $this->em->flush();
                        } else {
                            $this->repository->translate($entityInstance, $form->getName(), $iso, $translations[$iso] );
                        }
                    }
                }


            }else{

                if(false === $form->getConfig()->getOption("mapped") || null === $form->getConfig()->getOption("mapped")){
                    continue;
                }

                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->setValue($entityInstance, $form->getName(), $form->getData());

            }

        }

    }


}
