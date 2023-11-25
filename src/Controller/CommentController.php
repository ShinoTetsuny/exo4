<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em){}

    #[IsGranted("ROLE_USER")]
    #[Route('/comment/create', name: 'app_comment_create')]
public function create(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator)
{
    $comment = new Comment();
    $form = $this->createForm(CommentType::class, $comment);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $comment->setAuthor($this->getUser());

        // Récupérez l'ID de l'article depuis la requête
        $articleId = $request->get('articleId');

        // Assurez-vous que l'ID de l'article est valide
        if (!$articleId) {
            // Gérez l'erreur, redirigez l'utilisateur ou faites quelque chose d'autre
        }

        // Récupérez l'article correspondant à l'ID
        $article = $entityManager->getRepository(Article::class)->find($articleId);

        // Associez le commentaire à l'article
        $comment->setArticle($article);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', $translator->trans('flash_messages.subject.success.create'));

        // Redirigez vers la page des détails de l'article après la création du commentaire
        return $this->redirectToRoute('app_article_detail', ['id' => $articleId]);
    }

    return $this->render("comment/create.html.twig", [
        "controller_name" => "CommentController",
        'form' => $form->createView(),
    ]);
}

#[IsGranted("ROLE_ADMIN")]
#[Route('/comment/{id}/disable', name: 'app_comment_disable')]
public function disable(Comment $comment, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
{
    // Vérifiez si l'utilisateur a le rôle administrateur
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Désactivez le commentaire
    $comment->setState('disable');
    $entityManager->flush();

    $this->addFlash('success', $translator->trans('flash_messages.comment.disable'));

    // Redirigez vers la page des détails de l'article après la désactivation du commentaire
    return $this->redirectToRoute('app_article_detail', ['id' => $comment->getArticle()->getId()]);
}

#[IsGranted("ROLE_ADMIN")]
#[Route('/comment/{id}/enable', name: 'app_comment_enable')]
public function enable(Comment $comment, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
{
    // Vérifiez si l'utilisateur a le rôle administrateur
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Activez le commentaire
    $comment->setState('activate');
    $entityManager->flush();

    $this->addFlash('success', $translator->trans('flash_messages.comment.enable'));

    // Redirigez vers la page des détails de l'article après l'activation du commentaire
    return $this->redirectToRoute('app_article_detail', ['id' => $comment->getArticle()->getId()]);
}
}
