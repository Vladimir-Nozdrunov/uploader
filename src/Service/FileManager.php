<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class FileManager
{
    /**
     * @var Filesystem $fileSystem
     */
    private Filesystem $fileSystem;

    private string $folder;


    public function __construct(ContainerInterface $container)
    {
        $this->fileSystem = new Filesystem();
        $this->folder = $container->getParameter('img_dir');
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getImages(Product $product): array
    {
        if (!$product->getImages()) {
            return [];
        }

        foreach ($product->getImages() as $image) {
            $data[] = $this->folder . $image;
        }

        return $data;
    }

    /**
     * @param array $images
     * @return array
     */
    public function saveImages(array $images): array
    {
        $gallery = [];

        foreach ($images as $image) {
            $gallery[] = $this->uploadFile($image);
        }

        return $gallery;
    }

    /**
     * @param Product $product
     * @param array $images
     * @return array
     */
    public function updateImages(Product $product, array $images): array
    {
        $this->removeImages($product);

        return $this->saveImages($images);
    }

    /**
     * @param File $file
     * @return string
     */
    private function uploadFile(File $file): string
    {
        $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();

        try {

            $file->move($this->folder , $fileName);

        } catch (FileException $e){

            throw new FileException('Failed to upload file' . PHP_EOL .$e);

        }

        return $fileName;
    }

    /**
     * @param Product $product
     */
    public function removeImages(Product $product): void
    {
        if ($product->getImages()) {
            foreach ($product->getImages() as $image) {
                $this->removeFile($this->folder . $image);
            }
        }
    }

    /**
     * @param string $path
     */
    private function removeFile(string $path): void
    {
        $this->fileSystem->remove($path);
    }
}