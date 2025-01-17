<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Likes;
use App\Repository\ArticleRepository;
use App\Repository\LikesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api/articles")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/{id}", name="show_articles", methods={"GET"})
     */
    public function showArticle(Article $article, SerializerInterface $serializer)
    {
        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'Article not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $serializer->serialize($article, 'json', ['groups' => 'userArticle']);

        $response = [
                'code' => 0,
                'message' => 'Success',
                'error' => null,
                'result' => json_decode($data)
            ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);

    }


    /**
     * @Route("/list/{email}", name="show_profile_article", methods={"GET"})
     */
    public function showProfileArticle($email, SerializerInterface $serializer, ArticleRepository $articleRepository, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['email' => $email]);
        $article = $articleRepository->findBy(['user' => $user]);

        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'Article not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $serializer->serialize($article, 'json', ['groups' => 'profileArticles']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'profileArticles']);

    }

    /**
     * @Route("/{email}", name="add_articles", methods={"POST"})
     */
    public function createArticle($email,Request $request, ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em,UserRepository $u)
    {

            $article = new Article();
            $location = $this->getParameter('articlesupload_directory');
            $json = $request->getContent();
            $decode = json_decode($json, true);

            $filename = md5(uniqid()) . '.' . 'jpg';

            file_put_contents($location. '/' . $filename, base64_decode( $decode['image']['value']));
            $article->setTitle($decode['title']);
            $article->setContent($decode['content']);
            $article->setImage($filename);
            $user = $u->findOneBy(['email' => $email]);
            $article->setUser($user);
            $article->setCreatedBy($user->getFirstName());


            $errors = $validator->validate($article);

            if(count($errors) > 0)
            {
                return $this->json($errors, 400);
            }

            $em->persist($article);
            $em->flush();

        $response = [
            'code' => 0,
            'message' => 'Article created !',
            'error' => null,
            'result' => null
        ];

        return $this->json($response, 201, [], ['groups' => 'userArticle']);
     }


    /**
     * @Route("/", name="list_articles", methods={"GET"})
     */
    public function listArticle(ArticleRepository $articleRepository, SerializerInterface $serializer)
    {
        $article = $articleRepository->findAll();

        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'No articles found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $data = $serializer->serialize($article, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }

    /**
     * @Route("/like/{id}", name="list_like_articles", methods={"GET"})
     */
    public function listArticleLikes(LikesRepository $likesRepository,Article $article, ArticleRepository $articleRepository, SerializerInterface $serializer)
    {
        $likes = $likesRepository->findBy(['articles' => $article]);

        if(empty($likes))
        {
            $response = [
                'code' => 1,
                'message' => 'No articles found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $data = $serializer->serialize($likes, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }

    /**
     * @Route("/profile/{id}", name="list_articles_profile", methods={"GET"})
     */
    public function listArticleProfile($id,UserRepository $userRepository,ArticleRepository $articleRepository, SerializerInterface $serializer)
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $article = $articleRepository->findBy(['user' => $user]);

        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'No articles found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $data = $serializer->serialize($article, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }


    /**
     * @Route("/like/{email}/{id}", name="like_article", methods={"POST"})
     */
    public function likeArticle(LikesRepository $likesRepository, $email, UserRepository $userRepository, Article $article, Request $request ,ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em)
    {

        $user = $userRepository->findOneBy(['email' => $email]);
        $likes = $likesRepository->findOneBy(['users' => $user,'articles' => $article]);
        if(!$likes)
        {
            $like = new Likes();
            $like->setUsers($user);
            $like->setArticles($article);
            $em->persist($like);
            $em->flush();
        } else {
            $like = $likesRepository->findOneBy(['id' => $likes->getId()]);
            $em->remove($like);
            $em->flush();
        }

            $response = [
                'code' => 0,
                'message' => 'Article updated !',
                'error' => null,
                'result' => null
            ];

            return $this->json($response, 201, [], ['groups' => 'userArticle']);
        }


    /**
     * @Route("/{id}", name="update_articles", methods={"PUT"})
     */
    public function updateArticle(Article $article, Request $request ,ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em)
    {

        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'Article not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        $data = $serializer->deserialize($json, Article::class, 'json');
        $errors = $validator->validate($article);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }

        $article->setTitle($data->getTitle());
        $article->setContent($data->getContent());

        $em->persist($article);
        $em->flush();

        $response = [
            'code' => 0,
            'message' => 'Article updated !',
            'error' => null,
            'result' => null
        ];

        return $this->json($response, 201, [], ['groups' => 'userArticle']);
    }

    /**
     * @Route("/delete/{id}", name="delete_articles", methods={"DELETE"})
     */
    public function deleteArticle(Article $article, EntityManagerInterface $em)
    {
        if(empty($article))
        {
            $response = [
                'code' => 1,
                'message' => 'Article not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $em->remove($article);
        $em->flush();


        $response = [
            'code' => 0,
            'message' => 'Article deleted !',
            'error' => null,
            'result' => null
        ];

        return new JsonResponse($response, 200, ['groups' => 'userArticle']);

    }

}
