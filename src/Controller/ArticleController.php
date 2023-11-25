<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em){}

    #[Route('/articles', name: 'app_article')]
    public function listArticles(ArticleRepository $articleRepository): Response
    {
        $groupedArticles = $articleRepository->findArticlesGroupedByCategory();

        return $this->render('article/index.html.twig', [
            'groupedArticles' => $groupedArticles,
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/article/create', name: 'app_article_create')]
    public function create(Request $requets, TranslatorInterface $translator)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($requets);
        if($form->isSubmitted() && $form->isValid()){
            $article->setAuthor($this->getUser());

            if ($article->getState() === 'publish') {
                $article->setParutiondate(new \DateTimeImmutable());
            } else {
                $article->setParutiondate(null);
            }

            $this->em->persist($article);
            $this->em->flush();
            $this->addFlash('success', $translator->trans('flash_messages.subject.success.create'));
            return $this->redirectToRoute('app_article');
        }

        return $this->render("article/create.html.twig", [
            "controller_name" => "ArticleController",
            'form' => $form->createView()
        ]);
    }

    #[Route('article/{id}', name: 'app_article_detail')]
    public function details(Article $article = null, Request $requets,TranslatorInterface $translator)
    {
        if ($article == null) {
            return $this->redirectToRoute('app_article');
        } 
    
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($requets);
        if($form->isSubmitted() && $form->isValid()){
            $article->setAuthor($this->getUser());

            if ($article->getState() === 'publish') {
                $article->setParutiondate(new \DateTimeImmutable());
            } else {
                $article->setParutiondate(null);
            }

            $this->em->persist($article);
            $this->em->flush();
            $this->addFlash('success', $translator->trans('flash_messages.subject.success.create'));
            return $this->redirectToRoute('app_article');
        }
        
        return $this->render("article/details.html.twig", [
            "controller_name" => "ArticleController",
            "article" => $article,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('article/{id}/delete', name: 'app_article_delete')]
    public function delete(Article $article = null, TranslatorInterface $translator): Response
    {
        // Vérifiez si l'article existe
        if ($article === null) {
            $this->addFlash('danger', $translator->trans('flash_messages.subject.error.not_found'));
            return $this->redirectToRoute('app_article');
        }

        // Vérifiez si l'utilisateur a le rôle administrateur ou s'il est l'auteur de l'article
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous n\'avez pas le droit de supprimer cet article.');

        // Supprimez l'article
        $this->em->remove($article);
        $this->em->flush();

        $this->addFlash('success', $translator->trans('flash_messages.subject.success.delete'));

        return $this->redirectToRoute('app_article');
    }
}
