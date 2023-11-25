<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;

class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em){}

    #[Route('/user', name: 'app_user')]
    public function index(Request $request)
    {
        $user = $this->getUser();

        if($user instanceof User){
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $user->setRoles(['ROLE_USER']);
            } else {
                $user->setRoles(['ROLE_ADMIN']);
            }
        }
       

        // Enregistrez les modifications dans la base de données
        $this->em->flush();

        return $this->redirectToRoute('app_category'); // Redirigez selon vos besoins
    }
}
