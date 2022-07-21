<?php

namespace App\Controller;

use DateTime;
use App\Entity\Category;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CategoryController extends AbstractController
{
    /**
     * @Route("/ajouter-une-categorie", name="create_category", methods={"GET|POST"})
     */
    public function createCategory(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new Category;

        $form = $this->createForm(CategoryFormType::class, $category)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $category->setAlias($slugger->slug($category->getName()));
            $category->setCreatedAt(new DateTime());
            $category->setUpdateAt(new DateTime());
            
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie a bien été ajouté");
            return $this->redirectToRoute('show_dashboard');
        }


        return $this->render("admin/form/category.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier-une-categorie", name="update_category", methods={"GET"})
     */
    public function updateCategory(Category $category, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category)
            ->handleRequest();

        if($form->isSubmitted() && $form->isValid()) {

            $category->setAlias($slugger->slug($category->getName()));
            $category->setUpdateAt(new DateTime());

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie a bien été modifié !");
            return $this->redirectToRoute('show_dashboard');
        }

        return $this->render("admin/form/category.html.twig", [
            'form' => $form->createView(),
            'category' => $category 
        ]);
    }

    public function softDeleteCategory(Category $category, EntityManagerInterface $entityManager): RedirectResponse
    {
        $category->setDeletedAt(new DateTime());

        $entityManager->persist($category);
        $entityManager->flush();

        $this->addFlash('success', 'La catégorie a bien été archivé');
        return $this->redirectToRoute('show_dashboard');
    }

    /**
     * @Route("/restaurer-une-categorie_{id}, name="restore_category", methods={"GET"})
     */
    public function restoreCategory(Category $category, EntityManagerInterface $entityManager): RedirectResponse
    {
        $category->setDeletedAt(null);

        $entityManager->persist($category);
        $entityManager->flush();

        $this->addFlash('success', "La catégorie a bien été restauré");
        return $this->redirectToRoute('show_dashboard');
    }

    /**
     * @Route("supprimer-une-categorie_{id}", name="hard_delete_category", methods={"GET"})
     */
    public function hardDeleteCategory(Category $category, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', "La catégorie a bien été supprimé de la base de données");
        return $this->redirectToRoute('show_trash');
    }
}