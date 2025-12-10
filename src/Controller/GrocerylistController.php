<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Form\ConfirmType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GrocerylistController extends AbstractController
{
    #[Route('/grocerylist', name: 'app_grocerylist')]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login');
        }

        $groceries = $user->getProducts();

        return $this->render('grocerylist/list.html.twig', [
            'user' => $user,
            'groceries' => $groceries,
        ]);
    }

    #[Route('/grocerylist/{id<\d+>}', name: 'app_grocerylist_product')]
    public function show(Product $product): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login');
        }
        if ($user !== $product->getOwner())
        {
            throw $this->createAccessDeniedException('User does not have permission to view this');
        }

        return $this->render('grocerylist/show.html.twig', [
            'user'    => $user,
            'product' => $product,
        ]);
    }

    #[Route('/grocerylist/create', name: 'app_grocerylist_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login', [], 401);
        }

        $product = new Product();
        $product->setOwner($user);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('notice', 'Successfully added product to your grocery list!');

            return $this->redirectToRoute('app_grocerylist_product', ['id' => $product->getId()]);
        }

        return $this->render('grocerylist/create.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/grocerylist/{id<\d+>}/update', name: 'app_grocerylist_update')]
    public function update(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login', [], 401);
        }
        if ($user !== $product->getOwner())
        {
            throw $this->createAccessDeniedException('User does not have permission to view this');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('notice', 'Successfully updated product from your grocery list!');

            return $this->redirectToRoute('app_grocerylist_product', ['id' => $product->getId()]);
        }

        return $this->render('grocerylist/update.html.twig', [
            'user'    => $user,
            'product' => $product,
            'form'    => $form,
        ]);
    }

    #[Route('/grocerylist/{id<\d+>}/delete', name: 'app_grocerylist_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login', [], 401);
        }
        if ($user !== $product->getOwner())
        {
            throw $this->createAccessDeniedException('User does not have permission to view this');
        }

        $form = $this->createForm(ConfirmType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('notice', 'Successfully removed product from your grocery list!');

            return $this->redirectToRoute('app_grocerylist');
        }

        return $this->render('grocerylist/delete.html.twig', [
            'user'    => $user,
            'product' => $product,
            'form'    => $form,
        ]);
    }
}