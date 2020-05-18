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
     * @Route("/{email}", name="update_user", methods={"PATCH"})
     */
    public function updateUser($email, Request $request ,ValidatorInterface $validator, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {

        $user = $userRepository->findOneBy(['email' => $email]);
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
        $decode = json_decode($json, true);



        $errors = $validator->validate($user);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }

        $user->setFirstName($decode['firstName']);
        $user->setLastName($decode['lastName']);
        $user->setAbout($decode['about']);
        $user->setHobbies($decode['hobbies']);
        $user->setFacebookLink($decode['facebookLink']);
        $user->setTwitterLink($decode['twitterLink']);


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
     * @Route("/image/{email}", name="update_image_couverture", methods={"PATCH"})
     */
    public function updateImage($email, Request $request ,ValidatorInterface $validator, EntityManagerInterface $em, UserRepository $userRepository)
    {

        $user = $userRepository->findOneBy(['email' => $email]);
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

        $location = $this->getParameter('upload_directory');
        $json = $request->getContent();
        $decode = json_decode($json, true);

        $filename = md5(uniqid()) . '.' . 'jpg';

        file_put_contents($location. '/' . $filename, base64_decode( $decode['image']['value']));


        $errors = $validator->validate($user);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }


        $user->setImage($filename);


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
     * @Route("/imagecouverture/{email}", name="update_image", methods={"PATCH"})
     */
    public function updateImageCouverture($email, Request $request ,ValidatorInterface $validator, EntityManagerInterface $em, UserRepository $userRepository)
    {

        $user = $userRepository->findOneBy(['email' => $email]);
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

        $location = $this->getParameter('upload_directory');
        $json = $request->getContent();
        $decode = json_decode($json, true);

        $filename = md5(uniqid()) . '.' . 'jpg';

        file_put_contents($location. '/' . $filename, base64_decode( $decode['imageCouverture']['value']));


        $errors = $validator->validate($user);

        if(count($errors) > 0)
        {
            return $this->json($errors, 400);
        }


        $user->setImageCouverture($filename);


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
     * @Route("/{email}", name="show_user", methods={"GET"})
     */
    public function showUser($email , SerializerInterface $serializer, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['email' => $email]);
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

    /**
     * @Route("/search/{value}", name="search_user", methods={"GET"})
     */
    public function searchUser($value , SerializerInterface $serializer, UserRepository $userRepository)
    {
        $user = $userRepository->search($value);
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

    /**
     * @Route("/profile/{id}", name="get_user_profile", methods={"GET"})
     */
    public function getUserProfile($id , SerializerInterface $serializer, UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['id' => $id]);
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
