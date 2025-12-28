<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['houseName', 'id'])
            ->setSearchFields(['name', 'phoneNumber', 'id'])
            ->setDefaultSort(['name' => 'ASC'])
            ->showEntityActionsInlined()
            ->setFormOptions([
                'validation_groups' => ['Default', 'admin'],
            ]);
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('name')
        ->add('phoneNumber');
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Personal Information');

        yield IdField::new('id')
            ->onlyOnIndex();

        yield TextField::new('name', 'Full Name')
            ->setRequired(true);

        yield TextField::new('phoneNumber', 'Phone Number')
            ->setRequired(true);

        yield FormField::addPanel('Security & Roles');

        yield ChoiceField::new('roles', 'Roles')
            ->setChoices([
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderAsBadges([
                'ROLE_USER' => 'primary',
                'ROLE_ADMIN' => 'success',
            ])
            ->setRequired(true);
    }
}
