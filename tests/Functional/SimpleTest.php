<?php

namespace App\tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleTest extends WebTestCase
{
    public function testBasic(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        $entityManager = $container->get('doctrine')->getManager();
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);
        
        // Простая проверка
        $house = new \App\Entity\SummerHouse();
        $house->setHouseName('Test');
        $house->setPrice(100);
        $house->setSleeps(4);
        $house->setDistanceToSea(50);
        $house->setHasTV(true);
        
        $entityManager->persist($house);
        $entityManager->flush();
        
        $this->assertNotNull($house->getId());
        echo "✅ Basic EntityManager test passed\n";
    }
}