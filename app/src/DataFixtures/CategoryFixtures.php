<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_TEXTILE = "category-textile";
    public const CATEGORY_LUMIERE = "category-lumiere";
    public const CATEGORY_MURAL = "category-mural";
    public const CATEGORY_DECOR = "category-decor";
    public const CATEGORY_PRATIQUE = "category-pratique";

    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'Textile', 'color' => '#5bc0de', 'ref' => self::CATEGORY_TEXTILE],
            ['name' => 'Lumière', 'color' => '#f0ad4e', 'ref' => self::CATEGORY_LUMIERE],
            ['name' => 'Mural', 'color' => '#d9534f', 'ref' => self::CATEGORY_MURAL],
            ['name' => 'Décor', 'color' => '#5cb85c', 'ref' => self::CATEGORY_DECOR],
            ['name' => 'Pratique', 'color' => '#df691a', 'ref' => self::CATEGORY_PRATIQUE],
        ];

        foreach ($categories as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setColor($data['color']);

            $manager->persist($category);

            $this->addReference($data['ref'], $category);
        }

            $manager->flush();
    }
}
