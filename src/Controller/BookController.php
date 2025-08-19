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

class BookController extends AbstractController
{
    #[Route('/api/book/', name: 'book', methods: ['GET'])]
    public function getAllBook(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $bookList = $bookRepository->findAll();
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

    #[Route('/api/book', name: 'createBook', methods: ['POST'])]
    public function createBook(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $author = $authorRepository->find($idAuthor);
        $book->setAuthor($author);

        $em->persist($book);
        $em->flush();
        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBook']);
        $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    #[Route('/api/book/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBook(Request $request, Book $currentBook, EntityManagerInterface $em, SerializerInterface $serializer, AuthorRepository $authorRepository): JsonResponse
    {
        $updatedBook = $serializer->deserialize(
            $request->getContent(),
            Book::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]
        );

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $author = $authorRepository->find($idAuthor);
        $updatedBook->setAuthor($author);

        $em->persist($updatedBook);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}