<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Repository\AuthorRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'books', methods: ['GET'])]
    public function getAllBook(BookRepository $bookRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $idCache = "getAllBooks-" . $page . "-" . $limit;

        $bookList = $cachePool->get($idCache, function (ItemInterface $item) use ($bookRepository, $page, $limit) {
            $item->tag("booksCache");
            return $bookRepository->findAllWithPagination($page, $limit);
        });

        $jsonBookList = $serializer->serialize($bookList, 'json', ['groups' => 'getBook']);
        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/book/{id}', name: 'detailBook', methods: ['GET'])]
    public function getDetailBook(int $id, SerializerInterface $serializer, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->find($id);

        if ($book) {
            $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBook']);
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
        }

        // au lieu de retourner une réponse 404 j'aurai ce message->
        throw new NotFoundHttpException('Book object not found');
    }

    #[Route('/api/book/{id}/delete', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteBook(Book $book, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($book);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/books', name: 'createBook', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un livre')]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');



        $errors = $validator->validate($book);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($book);
        $em->flush();

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $book->setAuthor($authorRepository->find($idAuthor));

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBook']);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/book/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBook(Request $request, Book $currentBook, EntityManagerInterface $em, SerializerInterface $serializer, AuthorRepository $authorRepository, ValidatorInterface $validator): JsonResponse
    {
        $updatedBook = $serializer->deserialize(
            $request->getContent(),
            Book::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]
        );



        $errors = $validator->validate($updatedBook);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $updatedBook->setAuthor($authorRepository->find($idAuthor));

        $em->persist($updatedBook);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}