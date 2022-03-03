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
use Symfony\Component\Security\Core\Security;

class BlogPostsController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;        
    }

    /**
     * @Route("/", name="app_blog_posts")
     */
    public function index(PaginatorInterface $paginator,
                          PostRepository $postRepository, Request $request): Response
    {
        $posts = $paginator->paginate($postRepository->findAll(),
            $request->query->getInt('page',1), // Page Number
            5 //Page Limit
        );

        return $this->render('blog_posts/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     *  @Route ("/posts/new", name="new_post")
     */

    public function new(Request $request,EntityManagerInterface $entityManager):Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $post->setCreatedAt(new \DateTime());
            $post->setImageUrl("https://picsum.photos/200/300");
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute("post_by_id",
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

    public function edit(Request $request,Post $post,
                         EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute("blog_by_id", [
                'id' => $post->getId()
            ]);

        }

        return $this->render('blog_posts/edit.html.twig',[
            'edit-form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_by_id", methods={"GET","POST"})
     */

    public function byId(Post $post, Request $request,
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

            return $this->redirectToRoute("post_by_id",[
                'id' => $post->getId()
            ]);
        }

        return $this->render('blog_posts/byId.html.twig',[
            'post' => $post, 'comment_form' => $form -> createView()
        ]);
    }
}
