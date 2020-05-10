<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
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
     * @Route("/{email}", name="add_articles", methods={"POST"})
     */
    public function createArticle($email,Request $request, ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em,UserRepository $u)
    {

            $json = $request->getContent();
            dd($json);
            $article = $serializer->deserialize($json, Article::class, 'json');
            $user = $u->findOneBy(['email' => $email]);
            $article->setUser($user);

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
            $article->setImage($data->getImage());

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
     * @Route("/{id}", name="delete_articles", methods={"DELETE"})
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
