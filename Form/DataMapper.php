<?php
/**
 * Created by Asier Marqués <asiermarques@gmail.com>
 * Date: 17/5/16
 * Time: 20:58
 */

namespace Simettric\DoctrineTranslatableFormBundle\Form;


use Doctrine\ORM\EntityManager;
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

    private $translations=[];


    private $locales=[];

    private $property_names=[];

    public function __construct(EntityManager $entityManager){

        $this->em = $entityManager;
        $this->repository = $this->em->getRepository('Gedmo\Translatable\Entity\Translation');

    }

    public function setLocales(array $locales){
        $this->locales = $locales;
    }

    public function setPropertyNames(array $property_names){
        $this->property_names = $property_names;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function getPropertyNames()
    {
        return $this->property_names;
    }


    public function getTranslations($entity){

        if(!count($this->translations)){
            $this->translations = $this->repository->findTranslations($entity);
        }

        return $this->translations;

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


            $entityInstance = $data;

            $translations = $this->getTranslations($entityInstance);



            if(false !== in_array($form->getName(), $this->getPropertyNames())) {

                $values = [];
                foreach($this->getLocales() as $iso){

                    if(isset($translations[$iso])){
                        $values[$iso] =  $translations[$iso][$form->getName()];
                    }

                }
                $form->setData($values);


            }else{

                $accessor = PropertyAccess::createPropertyAccessor();
                $form->setData($accessor->getValue($entityInstance, $form->getName()));

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


            if(false !== in_array($form->getName(), $this->getPropertyNames())) {


                $translations = $form->getData();
                foreach($this->getLocales() as $iso) {
                    if(isset($translations[$iso])){
                        $this->repository->translate($entityInstance, $form->getName(), $iso, $translations[$iso] );
                    }
                }


            }else{

                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->setValue($entityInstance, $form->getName(), $form->getData());

            }

        }

    }


}