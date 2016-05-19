# DoctrineTranslatableFormBundle

The goal of this Symfony Bundle is simplify the creation of translatable forms using Gedmo Doctrine Extensions and StofDoctrineExtensionsBundle.


Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require simettric/doctrine-translatable-form-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Simettric\DoctrineTranslatableFormBundle\SimettricDoctrineTranslatableFormBundle(),
        );

        // ...
    }

    // ...
}
```

Configuration
=============

You must to activate the persist_default_translation key in your stof_doctrine_extensions configuration options

    #app/config/config.yml
    stof_doctrine_extensions:
        default_locale: %locale%
        translation_fallback: true
        persist_default_translation: true
        orm:
            default:
                translatable: true
                
                
Creating your forms
===================

This is a simple example showing how you can code your translatable forms

```php
<?php

namespace AppBundle\Form;


use Simettric\DoctrineTranslatableFormBundle\Form\AbstractTranslatableType;
use Simettric\DoctrineTranslatableFormBundle\Form\TranslatableTextType;

class CategoryType extends AbstractTranslatableType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // you can add the translatable fields
        $this->createTranslatableMapper($builder, $options)
             ->add("name", TranslatableTextType::class)
             ->add("some other translatable field", TranslatableTextType::class)
        ;

        // and then you can add the rest of the fields using the standard way
        $builder->add('enabled')
        ;

    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
            'data_class'   => 'AppBundle\Entity\Category'
        ));

        // this is required
        $this->configureTranslationOptions($resolver);

    }
}

```

Then you need to declare your form type as a service

    #app/config/services.yml
    
    parameters:
        locale: es
        locales: [es, eu, en, fr]
    
    services:
        app.form.category_type:
            class: AppBundle\Form\CategoryType
            arguments: ["@sim_trans_form.mapper"]
            calls:
                - [ setRequiredLocale, [%locale%] ]
                - [ setLocales, [%locales%] ]
            tags:
                - { name: form.type }
                
                
And now you can work in your controller as if you worked with normal entities 

    $category = new Category();
    
    $form = $this->createForm(CategoryType::class, $category);
    
    if($request->getMethod() == "POST"){
    
        $form->handleRequest($request);
        
        if($form->isValid()){
        
            $em = $this->getDoctrine()->getManager();
            
            $em->persist($category);
            $em->flush();
        
        }
    }
    

