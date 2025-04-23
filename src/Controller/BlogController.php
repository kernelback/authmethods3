<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route('/', name: 'blog_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    #[Route('/rss.xml', name: 'blog_rss', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'])]
    #[Route('/page/{page}', name: 'blog_index_paginated', defaults: ['_format' => 'html'], requirements: ['page' => Requirement::POSITIVE_INT], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function index(
        Request $request,
        int $page,
        string $_format,
        PostRepository $posts,
        TagRepository $tags,
        WeatherService $weatherService
    ): Response {
        $tag = null;

        if ($request->query->has('tag')) {
            $tag = $tags->findOneBy(['name' => $request->query->get('tag')]);
        }

        $city = $request->query->get('city', 'Paris'); // 🌍 Ville dynamique (Paris par défaut)
        $weather = $weatherService->getWeatherForCity($city);

        $latestPosts = $posts->findLatest($page, $tag);

        return $this->render('blog/index.'.$_format.'.twig', [
            'paginator' => $latestPosts,
            'tagName' => $tag?->getName(),
            'weather' => $weather,
            'selectedCity' => $city, // pour afficher la ville dans l'input
        ]);
    }

    #[Route('/posts/{slug:post}', name: 'blog_post', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function postShow(Post $post): Response
    {
        return $this->render('blog/post_show.html.twig', ['post' => $post]);
    }

    #[Route('/comment/{postSlug}/new', name: 'comment_new', requirements: ['postSlug' => Requirement::ASCII_SLUG], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function commentNew(
        #[CurrentUser] User $user,
        Request $request,
        #[MapEntity(mapping: ['postSlug' => 'slug'])] Post $post,
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
    ): Response {
        $comment = new Comment();
        $comment->setAuthor($user);
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/search', name: 'blog_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        return $this->render('blog/search.html.twig', ['query' => (string) $request->query->get('q', '')]);
    }
}
