<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="adduser", methods={"POST"})
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
}
