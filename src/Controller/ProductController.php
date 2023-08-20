<?php

namespace App\Controller;
use App\Repository\ProductRepository;
use App\Form\ProductType;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductController extends AbstractController
{

    /**
     * This function is for dispaly all products
     *
     * @param ProductRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $repo ,  PaginatorInterface $paginator, Request $request): Response
    {
        $products = $paginator->paginate(
            $repo->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
    
        //$products = $repo->findAll();
        //dd($products);
        return $this->render('pages/product/index.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * This function for create new product
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/product/create', 'product.new',methods :['GET','POST'])]
    public function create_function(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            //dd($product);
            $manager->persist($product);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre produit a été créé avec succès !'
            );
            return $this->redirectToRoute('app_product');
        }

        return $this->render('pages/product/new.html.twig', [
            'form' => $form->createView()
        ]
);
    }


    #[Route('/product/edit/{id}', 'product.edit',methods :['GET','POST'])]
    public function edit(
        Request $request,
        ProductRepository $repo,
        EntityManagerInterface $manager,
        //Product $product
        int $id,
    ): Response {
        $product = $repo->findOneBy(['id' => $id]);
       
        if(!$product){
            $this->addFlash(
                'Danger',
                'Product Not Found!'
            );
            return $this->redirectToRoute('app_product');
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary mt-4'
            ],
            'label' => 'Update', // Change the label for editing
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre produit a été modifier avec succès !'
            );
            return $this->redirectToRoute('app_product');
        }
        return $this->render('pages/product/edit.html.twig', [
            'form' => $form->createView()
        ]
);
    }

    #[Route('/product/suppression/{id}', 'product.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Product $product
    ): Response {
        $manager->remove($product);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre Produit a été supprimé avec succès !'
        );

        return $this->redirectToRoute('app_product');
    }

}
