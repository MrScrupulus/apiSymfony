<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contrôleur pour gérer les opérations CRUD des auteurs
 * 
 * Ce contrôleur permet de :
 * - Récupérer la liste des auteurs
 * - Récupérer un auteur spécifique
 * - Créer un nouvel auteur
 * - Modifier un auteur existant
 * - Supprimer un auteur (avec suppression en cascade des livres)
 */
final class AuthorController extends AbstractController
{
    /**
     * Récupère la liste de tous les auteurs
     * 
     * @param AuthorRepository $authorRepository Repository pour accéder aux données des auteurs
     * @param SerializerInterface $serializer Service de sérialisation JSON
     * @return JsonResponse Liste des auteurs au format JSON
     */
    #[Route('/api/author/', name: 'author', methods: ['GET'])]
    public function getAllAuthor(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        // Récupération de tous les auteurs depuis la base de données
        $authorList = $authorRepository->findAll();

        // Sérialisation des auteurs en JSON avec le groupe 'getBook'
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ['groups' => 'getBook']);

        // Retour de la réponse JSON avec le statut HTTP 200 (OK)
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, [], true);
    }

    /**
     * Récupère un auteur spécifique par son ID
     * 
     * @param int $id Identifiant unique de l'auteur
     * @param SerializerInterface $serializer Service de sérialisation JSON
     * @param AuthorRepository $authorRepository Repository pour accéder aux données des auteurs
     * @return JsonResponse Auteur au format JSON ou erreur 404 si non trouvé
     */
    #[Route('/api/author/{id}', name: 'author_show', methods: ['GET'])]
    public function getAuthor(int $id, SerializerInterface $serializer, AuthorRepository $authorRepository): JsonResponse
    {
        // Recherche de l'auteur par son ID
        $author = $authorRepository->find($id);

        // Vérification si l'auteur existe
        if ($author) {
            // Sérialisation de l'auteur en JSON
            $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBook']);
            return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
        }

        // Retour d'une erreur 404 si l'auteur n'existe pas
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Crée un nouvel auteur
     * 
     * @param Request $request Requête HTTP contenant les données de l'auteur
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param SerializerInterface $serializer Service de sérialisation JSON
     * @param UrlGeneratorInterface $urlGenerator Générateur d'URLs
     * @return JsonResponse Auteur créé au format JSON avec statut 201 (Created)
     */
    #[Route('/api/author', name: 'createAuthor', methods: ['POST'])]
    public function createAuthor(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        // Désérialisation du JSON reçu en objet Author
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');

        // Persistance de l'auteur en base de données
        $em->persist($author);
        $em->flush();

        // Sérialisation de l'auteur créé en JSON
        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBook']);

        // Génération de l'URL de l'auteur créé pour l'en-tête Location
        $location = $urlGenerator->generate('author_show', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Retour de la réponse avec statut 201 (Created) et en-tête Location
        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    /**
     * Met à jour un auteur existant
     * 
     * @param Request $request Requête HTTP contenant les nouvelles données
     * @param Author $currentAuthor Auteur existant à modifier (injection automatique par Symfony)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @param SerializerInterface $serializer Service de sérialisation JSON
     * @return JsonResponse Statut 204 (No Content) en cas de succès
     */
    #[Route('/api/author/{id}', name: 'updateAuthor', methods: ['PUT'])]
    public function updateAuthor(Request $request, Author $currentAuthor, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        // Désérialisation du JSON reçu en objet Author, en utilisant l'auteur existant comme base
        $updatedAuthor = $serializer->deserialize(
            $request->getContent(),
            Author::class,
            'json',
            [\Symfony\Component\Serializer\Normalizer\AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]
        );

        // Persistance des modifications en base de données
        $em->persist($updatedAuthor);
        $em->flush();

        // Retour d'un statut 204 (No Content) car la mise à jour ne retourne pas de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Supprime un auteur et tous ses livres associés (suppression en cascade)
     * 
     * IMPORTANT : Cette méthode supprime l'auteur ET tous ses livres
     * car un livre ne peut pas exister sans auteur (contrainte de base de données)
     * 
     * @param Author $author Auteur à supprimer (injection automatique par Symfony)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return JsonResponse Statut 204 (No Content) en cas de succès
     */
    #[Route('/api/author/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deleteAuthor(Author $author, EntityManagerInterface $em): JsonResponse
    {
        // Suppression de l'auteur (les livres seront supprimés automatiquement grâce à la cascade)
        $em->remove($author);

        // Validation des changements en base de données
        $em->flush();

        // Retour d'un statut 204 (No Content) car la suppression ne retourne pas de contenu
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
