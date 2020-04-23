<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/{id}", name="show_comments", methods={"GET"})
     */
    public function showArticle(Comment $comment, SerializerInterface $serializer)
    {
        if(empty($comment))
        {
            $response = [
                'code' => 1,
                'message' => 'Comment not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $serializer->serialize($comment, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);

    }

    /**
     * @Route("/", name="add_comment", methods={"POST"})
     */
    public function createArticle(Request $request, ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em)
    {

        $json = $request->getContent();
        $comment = $serializer->deserialize($json, Comment::class, 'json');

        $errors = $validator->validate($comment);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }

        $em->persist($comment);
        $em->flush();

        $response = [
            'code' => 0,
            'message' => 'Comment created !',
            'error' => null,
            'result' => null
        ];

        return $this->json($response, 201, [], ['groups' => 'userArticle']);
    }


    /**
     * @Route("/", name="list_comments", methods={"GET"})
     */
    public function listArticle(CommentRepository $commentRepository, SerializerInterface $serializer)
    {
        $comment = $commentRepository->findAll();

        if(empty($comment))
        {
            $response = [
                'code' => 1,
                'message' => 'No comment found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $data = $serializer->serialize($comment, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }


    /**
     * @Route("/{id}", name="delete_articles", methods={"DELETE"})
     */
    public function deleteArticle(Comment $comment, EntityManagerInterface $em)
    {
        if(empty($comment))
        {
            $response = [
                'code' => 1,
                'message' => 'Comment not found',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $em->remove($comment);
        $em->flush();


        $response = [
            'code' => 0,
            'message' => 'Comment deleted !',
            'error' => null,
            'result' => null
        ];

        return new JsonResponse($response, 200, ['groups' => 'userArticle']);


    }
}
