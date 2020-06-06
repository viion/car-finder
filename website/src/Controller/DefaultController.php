<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Service\AutoTraderParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var CarRepository */
    private $repository;
    /** @var AutoTraderParser */
    private $parser;
    
    public function __construct(EntityManagerInterface $em, CarRepository $repository, AutoTraderParser $parser)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->parser = $parser;
    }
    
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $fave = $request->get('fave', 1) ?: 0;
        
        $sort = [
            'price' => 'desc'
        ];
        
        $filter = [
            'fave' => $fave,
        ];
        
        $log = $this->parser->getLog();
        
        $cars = $this->repository->findby($filter, $sort, 100, $request->get('offset') ?: 0);
        
        return $this->render('home.html.twig', [
            'cars' => $cars,
            'log' => $log,
            'fave' => $fave
        ]);
    }
    
    /**
     * @Route("/car/{car}", name="car")
     */
    public function car(Car $car)
    {
        $log = $this->parser->getLog();

        $this->em->persist($car);
        $this->em->flush();
        
        return $this->render('car.html.twig', [
            'car' => $car,
            'log' => $log,
        ]);
    }
    
    /**
     * @Route("/car/{car}/favourite", name="car_favourite")
     */
    public function fave(Car $car)
    {
        $car->setFave(!$car->isFave());
    
        $this->em->persist($car);
        $this->em->flush();
    
        return $this->json(['true']);
    }
    
    /**
     * @Route("/discord", name="discord")
     */
    public function discord()
    {
        return $this->render('discord_gateway.html');
    }
}
