<?php


namespace App\Controller\Api;

use App\Utils\Pageable;
use App\Utils\Search;
use App\Entity\{Product};
use App\Repository\{CategoryRepository, ProductRepository};
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController, OA\Tag(name: "Default"), Route('/', name: 'app_')]
class DefaultResource extends AbstractController
{
    public function __construct(
        private readonly CategoryRepository $crep,
        private readonly ProductRepository $repo,
    ){}

    #[
        Route('tags', name: 'tags', methods: ['GET']),
        OA\Response(
            response: 200, description: 'Returns the rewards of an product',
            content: new OA\JsonContent(type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Category')
            )
        )]
    public function getTag(): Response
    {
        return $this->json($this->crep->findAll(), Response::HTTP_OK, [], array('groups' => ['category:read']));
    }

    #[
        Route('products', name: 'all', methods: ['GET']),
        OA\Parameter(
            name: 'page',
            in: 'query',
            schema: new OA\Schema(type: 'integer'),
            example: 1,
        ),
        OA\Parameter(
            name: 'kword',
            description: 'The field used to filter query',
            in: 'query',
            schema: new OA\Schema(type: 'string'),
        ),
        OA\Parameter(
            name: 'limit',
            description: 'The field used to limit query',
            in: 'query',
            schema: new OA\Schema(type: 'integer'),
        ),
        OA\Response(
            response: 200, description: 'Returns the rewards of an product',
            content: new OA\JsonContent(type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Product')
            )
        )
    ]
    public function getAll(Request $req): Response
    {
        return $this->json(Pageable::createFrom($this->repo->findAllPaginated(
            $req->query->getInt('page', 1),
            Search::createFrom($req->query),
            $req->query->getInt("limit", 5)
        )), Response::HTTP_OK, [], array('groups' => ['product:read']));
    }

    #[Route('products/{id}', name: 'one', methods: ['GET']),
    OA\Response(response: 200, description: 'Successful response',
        content: new OA\JsonContent(ref: new Model(type: Product::class, groups: ['product:read']))
    )]
    public function getOne(Product $product): Response
    {
        return $this->json($product, Response::HTTP_OK, [], array('groups' => ['product:read']));
    }
}