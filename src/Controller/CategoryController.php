<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\CategoryRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private CategoryRepository $categoryRepository){}   

    #[Route('/', name: 'app_category')]
    public function index()
    {
        $categorys = $this->categoryRepository->findTheThreeLastCategory();

        return $this->render("category/index.html.twig", [
            "controller_name" => "CategoryController",
            "categorys" => $categorys,
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/category/create', name: 'app_category_create')]
    public function create(Request $requets, TranslatorInterface $translator)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($requets);
        if($form->isSubmitted() && $form->isValid()){
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success','Catégorie Crée');
            return $this->redirectToRoute('app_category');
        }

        return $this->render("category/create.html.twig", [
            "controller_name" => "CategoryController",
            'form' => $form->createView()
        ]);
    }

    #[Route('category/{id}', name: 'app_category_details')]
    public function details(Category $category = null, Request $requets,TranslatorInterface $translator)
    {
        if ($category == null) {
            return $this->redirectToRoute('app_category');
        } 
    
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($requets);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', $translator->trans('flash_messages.subject.success.edit'));
        }
        
        return $this->render("category/details.html.twig", [
            "controller_name" => "CategoryController",
            "category" => $category,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('category/{id}/delete', name: 'app_category_delete')]
    public function delete(Category $category = null,TranslatorInterface $translator)
    {
        if ($category == null) {
            $this->addFlash('danger', $translator->trans('flash_messages.subject.error.not_found'));
            return $this->redirectToRoute('app_subject');
        }    
        // Delete the subject
        $this->em->remove($category);
        $this->em->flush();
    
    
        $this->addFlash('success',  $translator->trans('flash_messages.subject.success.delete'));
    
        return $this->redirectToRoute('app_category');
    }
}
