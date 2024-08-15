<?php

declare(strict_types=1);

namespace Kunstmaan\NodeBundle\Form\Type;

use Kunstmaan\NodeBundle\Form\DataTransformer\URLChooserToLinkTransformer;
use Kunstmaan\NodeBundle\Validator\Constraint\ValidExternalUrl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * URLChooserType
 */
class URLChooserType extends AbstractType
{
    const INTERNAL = 'internal';

    const EXTERNAL = 'external';

    const EMAIL = 'email';

    public function __construct(private bool $improvedUrlChooser = false)
    {
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'pagepart.link.internal' => self::INTERNAL,
            'pagepart.link.external' => self::EXTERNAL,
            'pagepart.link.email' => self::EMAIL,
        ];

        if ($types = $options['link_types']) {
            foreach ($choices as $key => $choice) {
                if (!\in_array($choice, $types, false)) {
                    unset($choices[$key]);
                }
            }
        }

        $builder->add('link_url', HiddenType::class, [
            'required' => false,
            'constraints' => [
                new Callback([$this, 'validateLink']),
            ],
        ]);
        $builder->add('link_type', URLTypeType::class, [
            'required' => true,
            'choices' => array_flip($choices),
            'enable_improved_urlchooser' => $this->improvedUrlChooser,
            'attr' => [
                'class' => 'urlchooser-type',
            ],
        ]);

        $builder->add('choice_external', TextType::class, [
            'attr' => ['placeholder' => 'https://'],
        ]);
        $builder->add('choice_email', EmailType::class);
        $builder->add('choice_internal', InternalURLSelectorType::class);

        $builder->addViewTransformer(new URLChooserToLinkTransformer());
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver the resolver for the options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => null,
                'link_types' => [],
                'error_bubbling' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'urlchooser';
    }

    public function validateLink($value, ExecutionContextInterface $context): void
    {
        $urlChooserTypeForm = $context->getObject()?->getParent();
        if (!$urlChooserTypeForm) {
            return;
        }

        $type = $urlChooserTypeForm->get('link_type')->getData();
        if ($type === self::EXTERNAL) {
            $context->getValidator()->inContext($context)->validate($this->getFormDataValue($urlChooserTypeForm, 'choice_external'), [new ValidExternalUrl()]);

            return;
        }

        if ($type === self::EMAIL) {
            $context->getValidator()->inContext($context)->validate($this->getFormDataValue($urlChooserTypeForm, 'choice_email'), [new Email()]);
        }
    }

    private function getFormDataValue(FormInterface $form, string $fieldName): ?string
    {
        return $form->has($fieldName) ? $form->get($fieldName)->getData() : null;
    }
}
