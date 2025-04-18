<?php

namespace App\Controller;

use App\Entity\Usager;
use App\Repository\CommandeRepository;
use App\Form\UsagerType;
use App\Repository\UsagerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/usager', requirements: ['_locale'=>'%app.supported_locales%'])]
final class UsagerController extends AbstractController
{
    #[Route(name: 'app_usager_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {

        $usager = $this->getUser();

        // Trouver les commandes en fonction de l'utilisateur
        $commandes = $commandeRepository->findBy(['usager' => $usager], ['dateCreation' => 'DESC']);

        return $this->render('usager/index.html.twig', [
            'nbCommandes' => count($commandes),
        ]);
    }

    #[Route('/inscription', name: 'app_usager_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usager = new Usager();
        $form = $this->createForm(UsagerType::class, $usager);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword($usager, $usager->getPassword());
            $usager->setPassword($hashedPassword);

            $usager->setRoles(["ROLE_CLIENT"]);

            $entityManager->persist($usager);
            $entityManager->flush();

            return $this->redirectToRoute('app_usager_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usager/new.html.twig', [
            'usager' => $usager,
            'form' => $form,
        ]);
    }

    #[Route('/commandes',
            name: 'app_usager_commandes',
        )]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        $usager = $this->getUser();

        // Trouver les commandes en fonction de l'utilisateur
        $commandes = $commandeRepository->findBy(['usager' => $usager], ['dateCreation' => 'ASC']);

        return $this->render('usager/commandes.html.twig', [
                    'commandes' => $commandes
                ]);
    }

    #[Route('/commande/{id}',
        name: 'app_usager_commande',
        methods: ['GET'],
        requirements: ['id'=>'\d+']
        )]
    public function commande(int $id, CommandeRepository $commandeRepository): Response
    {
        // Trouver la commande à partir de son identifiant
        $commande = $commandeRepository->find($id);

        return $this->render('usager/commande.html.twig', [
            'commande' => $commande
        ]);
    }
}
