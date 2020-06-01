<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Rct567\DomQuery\DomQuery;
use Symfony\Component\Console\Output\ConsoleOutput;

class AutoTraderUpdater extends HttpService
{
    /** @var ConsoleOutput */
    private $console;
    /** @var AutoTraderParser */
    private $atp;
    /** @var EntityManagerInterface */
    private $em;
    /** @var CarRepository */
    private $repository;
    /** @var Discord */
    private $discord;
    
    public function __construct(AutoTraderParser $atp, EntityManagerInterface $em, CarRepository $repository, Discord $discord)
    {
        $this->console    = new ConsoleOutput();
        $this->atp        = $atp;
        $this->repository = $repository;
        $this->em         = $em;
        $this->discord    = $discord;
    }
    
    public function update()
    {
        // Find all non hidden cars
        $cars = $this->repository->findBy(['hidden' => false]);
        
        /** @var Car $car */
        foreach ($cars as $car) {
            $id = $car->getSiteId();
    
            // set old car data
            $car->setPreviousdata(\GuzzleHttp\json_encode($car->toArray()));
    
            // grab the old hash
            $lastHash = $car->getHash();
    
            // get the latest car info
            $car = $this->atp->parseListingPage($id, $car);
    
            // grab the new hash
            $newHash = $car->getHash();
            
            // save car
            $this->em->persist($car);
            $this->em->flush();
    
            if ($lastHash != $newHash && !$car->isHidden()) {
                $this->console->writeln("!!! New Changes!");
                
                if (AutoTraderParser::DISCORD) {
                    $this->discord->sendMessage(712787184108830726, null, [
                        'title' => "(UPDATED)" . $car->getTitle(),
                        'description' => "This listing has been updated with new/changed information",
                        'url' => "http://tts.viion.co.uk/car/{$car->getId()}",
                        'color' => hexdec('75f542'),
                        'image' => [
                            'url' => $car->getImages()[0] ?? 'https://www.autotrader.co.uk/images/noimage/no_image_266x200.png'
                        ],
                        'fields' => [
                            [
                                'name' => 'Price',
                                'value' => number_format($car->getPrice()),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Price Evaluation',
                                'value' => ucwords($car->getPriceValuation()),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Score',
                                'value' => $car->getScore() . " / 100",
                                'inline' => true,
                            ],
                            [
                                'name' => 'Year',
                                'value' => $car->getYear(),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Miles',
                                'value' => number_format($car->getMiles()),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Check Status',
                                'value' => ucwords($car->getCheckStatus()),
                                'inline' => true,
                            ]
                        ]
                    ]
                    );
                }
        
                $this->console->writeln("Car: {$car->getTitle()} has been updated");
            }
        }
    }
}
