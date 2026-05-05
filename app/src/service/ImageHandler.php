<?php

namespace App\Service;

use Doctrine\Common\Collections\Collection;

class ImageHandler
{
    public function __construct(private string $projectDir)
    {
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
}
