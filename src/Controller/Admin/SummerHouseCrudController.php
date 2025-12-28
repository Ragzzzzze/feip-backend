<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SummerHouse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class SummerHouseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SummerHouse::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('SummerHouse')
            ->setEntityLabelInPlural('SummerHouses')
            ->setSearchFields(['houseName', 'id'])
            ->setDefaultSort(['houseName' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined()
            ->setFormOptions([
                'validation_groups' => ['Default', 'admin'],
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('houseName')
            ->add(NumericFilter::new('price', 'Price'))
            ->add(NumericFilter::new('sleeps', 'Sleeps'))
            ->add(NumericFilter::new('distanceToSea', 'Distance to Sea'))
            ->add(BooleanFilter::new('hasTV', 'Has TV'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('SummerHouse Information');

        yield IdField::new('id');

        yield TextField::new('houseName', 'House Name')
            ->setRequired(true);

        yield FormField::addPanel('Pricing & Capacity');

        yield MoneyField::new('price', 'Price per night')
            ->setCurrency('RUB')
            ->setStoredAsCents(false)
            ->setRequired(true);

        yield IntegerField::new('sleeps', 'Sleeps (Capacity)')
            ->setRequired(true);

        yield FormField::addPanel('Details & Amenities');

        yield IntegerField::new('distanceToSea', 'Distance to Sea (meters)')
            ->setRequired(true);

        yield BooleanField::new('hasTV', 'Has TV')
            ->setRequired(true);
    }
}
