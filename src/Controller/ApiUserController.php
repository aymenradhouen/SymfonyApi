<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/api/users")
 */
class ApiUserController extends AbstractController
{
    /**
     * @Route("/", name="list_users", methods={"GET"})
     */
    public function index(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $users = $userRepository->findAll();
        if(empty($users))
        {
            $response = [
                'code' => 1,
                'message' => 'No users found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $serializer->serialize($users, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success !',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }

    /**
     * @Route("/", name="add_user", methods={"POST"})
     */
    public function addUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
            $json = $request->getContent();
            $user = $serializer->deserialize($json, User::class, 'json');

            $errors = $validator->validate($user);

            if(count($errors) > 0)
            {
                return $this->json($errors, 400);
            }

            $em->persist($user);
            $em->flush();
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $user->getPassword()
                )
            );
            $em->persist($user);
            $em->flush();

        $response = [
            'code' => 0,
            'message' => 'User created !',
            'error' => null,
            'result' => null
        ];

        return $this->json($response, 201, [], ['groups' => 'userArticle']);

        }

    /**
     * @Route("/{id}", name="update_user", methods={"PUT"})
     */
    public function updateUser(User $user, Request $request ,ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {

        if(empty($user))
        {
            $response = [
                'code' => 1,
                'message' => 'User not found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }


        $json = $request->getContent();
        $data = $serializer->deserialize($json, User::class, 'json');
        $errors = $validator->validate($user);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }

        $user->setEmail($data->getEmail());
        $user->setPassword(
            $passwordEncoder->encodePassword(
                $user,
                $data->getPassword()
            )
        );
        $user->setLoginName($data->getLoginName());

        $em->persist($user);
        $em->flush();

        $response = [
            'code' => 0,
            'message' => 'User updated !',
            'error' => null,
            'result' => null
        ];

        return $this->json($response, 201, [], ['groups' => 'userArticle']);
    }


    /**
     * @Route("/{id}", name="delete_user", methods={"DELETE"})
     */
    public function deleteUser(User $user, EntityManagerInterface $em)
    {
        if(empty($user))
        {
            $response = [
                'code' => 1,
                'message' => 'User not found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $em->remove($user);
        $em->flush();


        $response = [
            'code' => 0,
            'message' => 'User deleted !',
            'error' => null,
            'result' => null
        ];

        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }

    /**
     * @Route("/{id}", name="show_user", methods={"GET"})
     */
    public function showUser(User $user , SerializerInterface $serializer)
    {
        if(empty($user))
        {
            $response = [
                'code' => 1,
                'message' => 'User not found !',
                'error' => null,
                'result' => null
            ];
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $data = $serializer->serialize($user, 'json', ['groups' => 'userArticle']);

        $response = [
            'code' => 0,
            'message' => 'Success',
            'error' => null,
            'result' => json_decode($data)
        ];
        return new JsonResponse($response, 200, ['groups' => 'userArticle']);
    }




}
