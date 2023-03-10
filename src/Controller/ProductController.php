<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductServiceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;
use SimpleXMLIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request, ProductServiceInterface $productService): Response
    {
        $offset = max(0, $request->query->getInt('offset'));
        $filter = $request->query->get('filter');
        $sort = $request->query->get('sort');

        $products = $productRepository->getProductPaginator($offset, $filter, $sort);

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'previous' => $offset - ProductRepository::PRODUCTS_PER_PAGE,
            'next' => min(count($products), $offset + ProductRepository::PRODUCTS_PER_PAGE),
            'filter' => $filter
        ]);
    }

    #[Route('/import', name: 'import_file', methods: ['GET', 'POST'])]
    public function import(): Response
    {
        return $this->render('file.upload.twig');
    }

    #[Route('/handleImport', name: 'handle_import', methods: ['POST'])]
    public function handleImport(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('importFile');

        foreach ((new SimpleXMLIterator(file_get_contents($file))) as $importProduct){
            $product = (new Product())
                ->setName($importProduct->name)
                ->setCategory($importProduct->category)
                ->setDescription($importProduct->description);


            if (str_ends_with($importProduct->weight, 'g')){
                $product->setWeight((int)mb_substr($importProduct->weight, 0, strlen($importProduct->weight)-2) * 0.001);
            } else {
                $product->setWeight((int)mb_substr($importProduct->weight, 0, strlen($importProduct->weight)-2));
            }

            $productRepository->save($product);
        }

        $entityManager->flush();

        $this->redirectToRoute('app_product_index');
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

//    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
//    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
//    {
//        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
//            $productRepository->remove($product, true);
//        }
//
//        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
//    }
}
