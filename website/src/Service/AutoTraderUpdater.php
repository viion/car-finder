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
        // Find all fave characters
        $cars = $this->repository->findBy([
            'deleted' => false,
        ]);
        
        /** @var Car $car */
        foreach ($cars as $car) {
            $id = $car->getSiteId();
    
            // grab the old hash
            $lastHash = $car->getHash();
            
            $oldCar = clone $car;
    
            // get the latest car info
            $car = $this->atp->parseListingPage($id, $car);
            
            // grab the new hash
            $newHash = $car->getHash();
            
            if ($lastHash != $newHash && !$car->isDeleted()) {
                $changes = $this->detectChanges($oldCar, $car);
                
                $this->console->writeln("!!! New Changes!");
                $car->setNotes("Car has new changes since: ". date('Y-m-d H:i:s'));
                
                if (AutoTraderParser::DISCORD && $car->isFave()) {
                    $this->discord->sendMessage(712787184108830726, null, [
                        'title' => "UPDATED!!! -- " . $car->getTitle(),
                        'description' => $changes,
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
                                'name' => 'Miles',
                                'value' => number_format($car->getMiles()),
                                'inline' => true,
                            ],
                        ]
                    ]
                    );
                }
        
                $this->console->writeln("Car: {$car->getTitle()} has been updated");
            }
    
            // save car
            $this->em->persist($car);
            $this->em->flush();
        }
    }
    
    private function detectChanges(Car $carOld, Car $carNew)
    {
        $changes = [];
        
        $check = [
            'title',
            'description',
            'price',
            'priceValuation',
            'year',
            'tax',
            'fuel',
            'miles',
            'gearbox',
            'engineSize',
            'checkStatus',
        ];
        
        $carOld = $carOld->toArray();
        $carNew = $carNew->toArray();
        
        foreach ($check as $fieldName) {
            $oldValue = $carOld[$fieldName];
            $newValue = $carNew[$fieldName];
            
            if ($oldValue != $newValue) {
                $changes[] = "[{$fieldName}] {$oldValue} --> {$newValue}";
            }
        }
        
        if (count($carOld['images']) != count($carNew['images'])) {
            $changes[] = "New Images";
        }
        
        return implode("\n\n", $changes);
    }
}
