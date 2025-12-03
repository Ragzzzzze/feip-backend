<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

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
                'validation_groups' => ['Default', 'admin']
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('name')
        ->add('phoneNumber');
    }

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
