<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ProductRepository;
use App\Utils\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function __construct(private readonly ProductRepository $repository){}

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('default/home.html.twig', [
            'articles' => $this->repository->findLast(6),
        ]);
    }

    #[Route('/biens', name: 'app_biens')]
    public function index(Request $req): Response
    {
        $search = new Search();

        return $this->render('default/index.html.twig', [
            'articles' => $this->repository->findAllPaginated($req->query->getInt('page', 1),$search),
            'form' => $this->createForm(SearchType::class, $search)
        ]);
    }
}
