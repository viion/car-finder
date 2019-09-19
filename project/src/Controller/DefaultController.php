<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /** @var CarRepository */
    private $repository;
    
    public function __construct(CarRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('app.html.twig');
    }
    
    /**
     * @Route("/cars")
     */
    public function cars(Request $request)
    {
        $cars    = [];
        $results = $this->repository->findby(
            [],
            [
                'score' => 'desc',
                'price' => 'asc',
                'added' => 'desc',
            ],
            50,
            $request->get('offset') ?: 0
        );
        
        /** @var Car $car */
        foreach ($results as $car) {
            $cars[] = $car->toArray();
        }
        
        return $this->json($cars);
    }
}
