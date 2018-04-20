<?php

namespace Simettric\DoctrineTranslatableFormBundle\Form;

use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;
use Gedmo\Translatable\TranslatableListener;
use Simettric\DoctrineTranslatableFormBundle\Interfaces\TranslatableFieldInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Exception;

class DataMapper implements DataMapperInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TranslationRepository
     */
    private $repository;

    /**
     * @var FormBuilderInterface
     */
    private $builder;

    private $translations = [];

    /**
     * @var string[]
     */
    private $locales = [];

    /**
     * @var string
     */
    private $requiredLocale;

    /**
     * @var string[]
     */
    private $propertyNames = [];

    public function __construct(EntityManager $entityManager, TranslationRepository $repository = null)
    {
        $this->entityManager = $entityManager;

        if (!$repository) {
            $repository = Translation::class;
        }

        $this->repository = $this->entityManager->getRepository($repository);
    }

    public function setBuilder(FormBuilderInterface $builderInterface)
    {
        $this->builder = $builderInterface;
    }

    public function setRequiredLocale($locale)
    {
        $this->requiredLocale = $locale;
    }

    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function getTranslations($entity)
    {
        if (!count($this->translations)) {
            $this->translations = $this->repository->findTranslations($entity);
        }

        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function add($name, $type, array $options = [])
    {
        $this->propertyNames[] = $name;

        $field = $this->builder
            ->add($name, $type)
            ->get($name)
        ;

        if (!$field->getType()->getInnerType() instanceof TranslatableFieldInterface) {
            throw new \Exception("{$name} must implement TranslatableFieldInterface");
        }

        foreach ($this->locales as $iso) {
            $options = [
                'label' => $iso,
                'attr' => isset($options['attr']) ? $options['attr'] : [],
                'required' => $iso === $this->requiredLocale && (!isset($options['required']) || $options['required'])
            ];

            $field->add($iso, get_class($field->getType()->getParent()->getInnerType()), $options);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        foreach ($forms as $form) {
            $this->translations = [];
            $translations = $this->getTranslations($data);

            if (false !== in_array($form->getName(), $this->propertyNames)) {
                $values = [];

                foreach ($this->getLocales() as $iso) {
                    if (isset($translations[$iso])) {
                        $values[$iso] = isset($translations[$iso][$form->getName()])
                            ? $translations[$iso][$form->getName()] : '';
                    }
                }

                $form->setData($values);
            } else {
                if (false === $form->getConfig()->getOption('mapped')
                    || null === $form->getConfig()->getOption('mapped')) {
                    continue;
                }

                $accessor = PropertyAccess::createPropertyAccessor();
                $form->setData($accessor->getValue($data, $form->getName()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        foreach ($forms as $form) {
            $entityInstance = $data;

            if (false !== in_array($form->getName(), $this->propertyNames)) {
                $meta = $this->entityManager->getClassMetadata(get_class($entityInstance));

                $listener = new TranslatableListener();
                $listener->loadMetadataForObjectClass($this->entityManager, $meta);
                $config = $listener->getConfiguration($this->entityManager, $meta->name);

                $translations = $form->getData();

                foreach ($this->getLocales() as $iso) {
                    if (isset($translations[$iso])) {
                        if (isset($config['translationClass'])) {
                            $this->entityManager
                                ->getRepository($config['translationClass'])
                                ->translate($entityInstance, $form->getName(), $iso, $translations[$iso])
                            ;
                            $this->entityManager->persist($entityInstance);
                            $this->entityManager->flush();
                        } else {
                            $this->repository->translate($entityInstance, $form->getName(), $iso, $translations[$iso]);
                        }
                    }
                }
            } else {
                if (false === $form->getConfig()->getOption('mapped')
                    || null === $form->getConfig()->getOption('mapped')) {
                    continue;
                }

                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->setValue($entityInstance, $form->getName(), $form->getData());
            }
        }
    }
}
