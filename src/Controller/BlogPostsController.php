<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
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

    /**
     *  @Route ("/posts/new", name="new_article")
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

    /**
     * @Route("/posts/{}/edit", name="post_edit")
     */

    public function edit(Request $request, FlashyNotifier $flashy,Post $post,
                         EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($post);
            $entityManager->flush();
            $flashy->success("Blog Post Updated");

            return $this->redirectToRoute("blog_by_id", [
                'id' => $post->getId()
            ]);

        }

        return $this->render('blog_posts/edit.html.twig',[
            'editform' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_by_id", methods={"GET","POST"})
     */

    public function byId(Post $post, Request $request, FlashyNotifier $flashy,
                         EntityManagerInterface $entityManager): Response
    {
        $comment = new Comments();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form -> isValid()){
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTime());
            $entityManager->persist($comment);
            $entityManager->flush();
            $flashy->success('comment created!');

            return $this->redirectToRoute("blog_by_id",[
                'id' => $post->getId()
            ]);
        }

        return $this->render('blog_posts/byId.html.twig',[
            'post' => $post, 'comment_form' => $form -> createView()
        ]);
    }
}
