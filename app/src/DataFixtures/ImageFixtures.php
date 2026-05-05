<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $images = [
            // === Textile ===
            ['path' => 'image/product/image1.webp',  'alt' => 'Coussin bohème',     'product' => 1, 'isPrincipal' => true],
            ['path' => 'image/product/image2.webp',  'alt' => 'Plaid doux',         'product' => 1, 'isPrincipal' => false],
            ['path' => 'image/product/image3.webp',  'alt' => 'Rideaux en lin',     'product' => 3, 'isPrincipal' => true],
            ['path' => 'image/product/image4.webp',  'alt' => 'Tapis berbère',      'product' => 4, 'isPrincipal' => true],

            // === Lumière ===
            ['path' => 'image/product/image5.webp',  'alt' => 'Lampe de chevet',    'product' => 5, 'isPrincipal' => true],
            ['path' => 'image/product/image6.webp',  'alt' => 'Suspension design',  'product' => 6, 'isPrincipal' => true],
            ['path' => 'image/product/image7.webp',  'alt' => 'Guirlande LED',      'product' => 7, 'isPrincipal' => true],
            ['path' => 'image/product/image8.webp',  'alt' => 'Lampe sur pied',     'product' => 8, 'isPrincipal' => true],

            // === Mural ===
            ['path' => 'image/product/image9.webp',  'alt' => 'Tableau abstrait',   'product' => 9,  'isPrincipal' => true],
            ['path' => 'image/product/image10.webp', 'alt' => 'Miroir rond',        'product' => 10, 'isPrincipal' => true],
            ['path' => 'image/product/image11.webp', 'alt' => 'Affiche vintage',    'product' => 11, 'isPrincipal' => true],
            ['path' => 'image/product/image12.webp', 'alt' => 'Horloge murale',     'product' => 12, 'isPrincipal' => true],

            // === Décor ===
            ['path' => 'image/product/image13.webp', 'alt' => 'Vase en céramique',  'product' => 13, 'isPrincipal' => true],
            ['path' => 'image/product/image14.webp', 'alt' => 'Bougie parfumée',    'product' => 14, 'isPrincipal' => true],
            ['path' => 'image/product/image15.webp', 'alt' => 'Statue déco',        'product' => 15, 'isPrincipal' => true],
            ['path' => 'image/product/image16.webp', 'alt' => 'Plante artificielle','product' => 16, 'isPrincipal' => true],

            // === Pratique ===
            ['path' => 'image/product/image17.webp', 'alt' => 'Panier de rangement','product' => 17, 'isPrincipal' => true],
            ['path' => 'image/product/image18.webp', 'alt' => 'Boîte de stockage',  'product' => 18, 'isPrincipal' => true],
            ['path' => 'image/product/image19.webp', 'alt' => 'Porte-manteau',      'product' => 18, 'isPrincipal' => false],
            ['path' => 'image/product/image20.webp', 'alt' => 'Étagère murale',     'product' => 18, 'isPrincipal' => false],
        ];

        foreach ($images as $data) {
            $image = new Image();
            $image->setPath($data['path']);
            $image->setAlt($data['alt']);
            $image->setIsPrincipal($data['isPrincipal']);

            $product = $this->getReference(
                ProductFixtures::PRODUCT_REFERENCE_PREFIX . $data['product'],
                Product::class
            );
            $image->setProduct($product);

            $manager->persist($image);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
        ];
    }
}
