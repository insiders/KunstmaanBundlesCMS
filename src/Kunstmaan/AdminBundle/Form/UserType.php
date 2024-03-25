<?php

namespace Kunstmaan\AdminBundle\Form;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Kunstmaan\AdminBundle\Entity\Group;
use Kunstmaan\AdminBundle\Entity\GroupInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * UserType defines the form used for {@link User}
 */
class UserType extends AbstractType implements RoleDependentUserFormInterface
{
    /**
     * @var bool
     */
    private $canEditAllFields = false;

    /**
     * Setter to check if we can display all form fields
     *
     * @return bool
     */
    public function setCanEditAllFields($canEditAllFields)
    {
        $this->canEditAllFields = (bool) $canEditAllFields;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $languages = [];
        foreach ($options['langs'] as $lang) {
            $languages[$lang] = $lang;
        }

        $this->canEditAllFields = $options['can_edit_all_fields'];

        $builder
            ->add('username', TextType::class, ['required' => true, 'label' => 'settings.user.username'])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['password_required'],
                'invalid_message' => 'errors.password.dontmatch',
                'first_options' => [
                    'label' => 'settings.user.password',
                ],
                'second_options' => [
                    'label' => 'settings.user.repeatedpassword',
                ],
            ])
            ->add('email', EmailType::class, ['required' => true, 'label' => 'settings.user.email'])
            ->add('adminLocale', ChoiceType::class, [
                'choices' => $languages,
                'label' => 'settings.user.adminlang',
                'required' => true,
                'placeholder' => false,
            ]);

        if ($this->canEditAllFields) {
            $builder->add('enabled', CheckboxType::class, ['required' => false, 'label' => 'settings.user.enabled']);
            $groups = $builder->create('groups', EntityType::class, [
                'label' => 'settings.user.roles',
                'class' => Group::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $this->getQueryBuilder($er, $options['can_add_super_users']);
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'settings.user.roles_placeholder',
                    'class' => 'js-advanced-select form-control advanced-select',
                ],
            ]);

            if (!$options['can_add_super_users']) {
                // When the user is not allowed to modify super users,
                // save any existing super user groups and add them manually to the user
                $existingSuperGroups = [];
                $groups->addEventListener(FormEvents::POST_SET_DATA,
                    function (FormEvent $event) use (&$existingSuperGroups) {
                        $groups = $event->getData();
                        if (!\is_iterable($groups)) {
                            return;
                        }
                        foreach ($groups as $group) {
                            if ($group instanceof GroupInterface && $group->hasRole('ROLE_SUPER_ADMIN')) {
                                $existingSuperGroups[] = $group;
                            }
                        }
                    });

                $groups->addEventListener(FormEvents::SUBMIT,
                    function (FormEvent $event) use (&$existingSuperGroups) {
                        $groups = $event->getData();
                        if ($groups instanceof Collection) {
                            foreach ($existingSuperGroups as $superGroup) {
                                $groups->add($superGroup);
                            }
                        }
                        $event->setData($groups);
                    });
            }
            $builder->add($groups);
        }
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'user';
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'password_required' => false,
                'data_class' => 'Kunstmaan\AdminBundle\Entity\User',
                'langs' => null,
                'can_edit_all_fields' => null,
                'can_add_super_users' => false,
            ]
        );

        $resolver->addAllowedValues('password_required', [true, false]);
    }

    private function getQueryBuilder(EntityRepository $repo, bool $canAddSuperUsers): QueryBuilder
    {
        $qb = $repo->createQueryBuilder('g');
        $qb->orderBy('g.name', 'ASC');
        if (!$canAddSuperUsers) {
            $superAdminGroupsBuilder = $repo->createQueryBuilder('_g');
            $superAdminGroupsBuilder->select('_g.id');
            $superAdminGroupsBuilder->join('_g.roles', '_r');
            $superAdminGroupsBuilder->where('_r.role = :role');

            $qb->where($qb->expr()->notIn('g.id', $superAdminGroupsBuilder->getDQL()));
            $qb->setParameter('role', 'ROLE_SUPER_ADMIN');
        }

        return $qb;
    }
}
