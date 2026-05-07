<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageHandler
{
    public function __construct(
        private string $projectDir,
        private SluggerInterface $slugger)
    {
    }

    public function uploadImages(array $imageFiles, Product $product):void
{
    $alreadyHasPrincipal = $product->getImages()->exists(
        fn($key, $img) => $img->isPrincipal()
    );

    foreach ($imageFiles as $index => $imageFile) {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFileName = $this->slugger->slug($originalFilename);
        $newFileName = uniqid() . '-' . $safeFileName . '.' . $imageFile->guessExtension();

        $imageFile->move(
            $this->projectDir . '/public/uploads/products',
            $newFileName
        );

        $image = new Image();
        $image->setPath('uploads/products/' . $newFileName);
        $image->setAlt($originalFilename);
        $image->setIsPrincipal(!$alreadyHasPrincipal && $index === 0);
        $product->addImage($image);
    }
}

    public function deleteFiles(Collection $images): void
    {
        foreach ($images as $image) {
            $imagePath = $this->projectDir . '/public/' . $image->getPath();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
        }
    }

    public function deleteSingleFile(Image $image):void
    {
        $imagePath = $this->projectDir . '/public/' . $image->getPath();
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
