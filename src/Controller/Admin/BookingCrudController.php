<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use App\Enum\BookingStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Booking')
            ->setEntityLabelInPlural('Bookings')
            ->setSearchFields(['id', 'comment', 'client.name', 'house.houseName'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setFormOptions([
                'validation_groups' => ['Default', 'admin']
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('client', 'Client')
                ->setFormTypeOption('value_type_options.label_field', 'name'))
            ->add(EntityFilter::new('house', 'Summer House')
                ->setFormTypeOption('value_type_options.label_field', 'houseName'))
            ->add(ChoiceFilter::new('status')->setChoices([
                'Pending' => BookingStatus::PENDING,
                'Confirmed' => BookingStatus::CONFIRMED,
                'Cancelled' => BookingStatus::CANCELLED,
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Booking Information');

        yield IdField::new('id');

        yield AssociationField::new('house', 'Summer House')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'houseName');
        
        yield ChoiceField::new('status', 'Status')
            ->setChoices([
                'Pending' => BookingStatus::PENDING,
                'Confirmed' => BookingStatus::CONFIRMED,
                'Cancelled' => BookingStatus::CANCELLED,
            ])
            ->renderAsBadges([
                BookingStatus::PENDING->value => 'warning',
                BookingStatus::CONFIRMED->value => 'success',
                BookingStatus::CANCELLED->value => 'danger',
            ]);
        
        yield TextareaField::new('comment', 'Comment');
    }
}
