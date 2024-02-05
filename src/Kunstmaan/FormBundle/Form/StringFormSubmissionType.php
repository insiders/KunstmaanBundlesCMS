<?php

namespace Kunstmaan\FormBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringFormSubmissionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['value_constraints']) && !empty($options['value_constraints'])) {
            $options['constraints'] = $options['value_constraints'];
        }

        $keys = array_fill_keys(['label', 'required', 'constraints'], null);
        $fieldOptions = array_filter(
            array_replace($keys, array_intersect_key($options, $keys)),
            function ($v) {
                return isset($v);
            }
        );
        $builder->add('value', TextType::class, $fieldOptions);
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Kunstmaan\FormBundle\Entity\FormSubmissionFieldTypes\StringFormSubmissionField',
                'value_constraints' => [],
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'kunstmaan_formbundle_stringformsubmissiontype';
    }
}
