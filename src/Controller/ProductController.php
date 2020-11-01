<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @var EntityManagerInterface $em
     */
    private EntityManagerInterface $em;

    /**
     * @var FileManager $fileManager
     */
    private FileManager $fileManager;


    public function __construct(EntityManagerInterface $em, FileManager $fileManager)
    {
        $this->em = $em;
        $this->fileManager = $fileManager;
    }


    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $this->em->getRepository(Product::class)->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('images')->getData();

            if ($images) {
                $product->setImages($this->fileManager->saveImages($images));
            }

            $this->em->persist($product);
            $this->em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     * @param Product $product
     * @return Response
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'images' => $this->fileManager->getImages($product)
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('images')->getData();

            if ($images) {
                $product->setImages($this->fileManager->updateImages($product, $images));
            }

            $this->em->persist($product);
            $this->em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     * @param Product $product
     * @return Response
     */
    public function delete(Product $product): Response
    {
        $this->fileManager->removeImages($product);

        $this->em->remove($product);
        $this->em->flush();

        return $this->redirectToRoute('product_index');
    }
}
