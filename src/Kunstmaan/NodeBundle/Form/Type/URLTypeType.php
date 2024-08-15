<?php

declare(strict_types=1);

namespace Kunstmaan\NodeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class URLTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('choices');
        $resolver->setDefault('choices', []);
        $resolver->setAllowedTypes('choices', 'array');
        $resolver->setRequired('enable_improved_urlchooser');
        $resolver->setDefault('enable_improved_urlchooser', false);
        $resolver->setAllowedTypes('enable_improved_urlchooser', 'bool');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = $options['choices'];
        $view->vars['xhr'] = !$options['enable_improved_urlchooser'];
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
