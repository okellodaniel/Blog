<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogPostsController extends AbstractController
{

    /**
     * @Route("/", name="app_blog_posts")
     */
    public function index(PaginatorInterface $paginator,
                          PostRepository $postRepository, Request $request): Response
    {
        $posts = $paginator->paginate($postRepository->findAll(),
            $request->query->getInt('page',1), // Page Number
            7 //Page Limit
        );

        return $this->render('blog_posts/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /*
     * @Route("/posts/new", name="new_article")
     */

    public function new(Request $request, FlashyNotifier $flashy, EntityManagerInterface $entityManager):Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setImageUrl("https://picsum.photos/200/300");
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();
            $flashy->success("Post created");

            return $this->redirectToRoute("blog_by_id",
            ['id' => $post->getId()]
            );
        }

        return $this->render('blog_posts/newpost.html.twig', [
            'form' =>$form->createView()
        ]);
    }


}
