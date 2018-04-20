<?php

namespace Simettric\DoctrineTranslatableFormBundle\Form;

use Simettric\DoctrineTranslatableFormBundle\Interfaces\TranslatableFieldInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslatableTextType extends AbstractType implements TranslatableFieldInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'compound' => true,
            ])
            ->setRequired('compound')
            ->setAllowedValues('compound', true)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }
}
