<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Rct567\DomQuery\DomQuery;
use Symfony\Component\Console\Output\ConsoleOutput;

class AutoTraderParser extends HttpService
{
    const DISCORD = false;
    
    const ENDPOINT        = 'https://www.autotrader.co.uk';
    const ENDPOINT_SEARCH = '/car-search';
    const ENDPOINT_VIEW   = '/classified/advert/';
    const ENDPOINT_API    = '/json/fpa/initial/%s?guid=%s';
    const SEARCH_QUERIES  = [
        //'seller-type'                 => 'trade',
        'sort'                          => 'datedesc',
        'postcode'                      => 'ch54wx',
        'radius'                        => '1500',
        'make'                          => 'AUDI',
        'model'                         => 'TTS',
        //'aggregatedTrim'                => 'Black Edition',
        'price-to'                      => '30000',
        'year-from'                     => '2016',
        'maximum-mileage'               => '30000',
        'body-type'                     => 'Coupe',
        'exclude-writeoff-categories'   => 'on',
    ];
    
    /** @var DomQuery */
    private $dom;
    /** @var ConsoleOutput */
    private $console;
    /** @var EntityManagerInterface */
    private $em;
    /** @var CarRepository */
    private $repository;
    /** @var array */
    private $log;
    /** @var Discord */
    private $discord;
    
    public function __construct(EntityManagerInterface $em, CarRepository $repository, Discord $discord)
    {
        $this->console    = new ConsoleOutput();
        $this->repository = $repository;
        $this->em         = $em;
        $this->discord    = $discord;
    }
    
    public function parse(int $page = 1)
    {
        $queries = self::SEARCH_QUERIES;
        $queries['page'] = $page;
        
        //
        // Search
        //
        $url = self::ENDPOINT . self::ENDPOINT_SEARCH . '?' . http_build_query($queries);
        $this->console->writeln("GET: (Page: {$page}) <info>{$url}</info>");
        $response = $this->fetch($url);
        $this->console->writeln("Status Code: <comment>{$response->code}</comment>");

        //
        // Set dom
        //
        $this->console->writeln("Creating DomQuery");
        $this->dom = new DomQuery($response->html);
        
        //
        // Handle search
        //
        $this->parseSearchResults();
    }
    
    /**
     * Parse search results
     */
    public function parseSearchResults()
    {
        $this->log[] = "Parsing Audi TTS search results...";
        
        $this->console->writeln("Parsing search results");
        
        $newResults = 0;
        
        /** @var DomQuery $li */
        foreach ($this->dom->find('.search-page__result') as $li) {
            $isPromoted = $li->data('data-is-promoted-listing');
            
            // skip promotional entries
            if ($isPromoted && $isPromoted == 'true') {
                continue;
            }
            
            $link = trim($li->find('.listing-fpa-link')->attr('href'));
            $link = explode('?', $link)[0];
            
            $id = str_ireplace(self::ENDPOINT_VIEW, null, $link);
            
            // Check if we've already parsed these.
            /** @var Car $car */
            $car = $this->repository->findOneBy([ 'siteId' => $id ]);
            
            if ($car) {
                continue;
            }
    
            $car = $this->parseListingPage($id, $car);
            
            if ($car) {
                $newResults++;
    
                $this->log[] = "New Car: {$id}";
    
                if (self::DISCORD) {
                    $this->discord->sendMessage(712787184108830726, null, [
                        'title' => $car->getTitle(),
                        'description' => null,
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
                                'value' => number_format( $car->getMiles()),
                                'inline' => true,
                            ],
                            [
                                'name' => 'Check Status',
                                'value' => ucwords($car->getCheckStatus()),
                                'inline' => true,
                            ]
                        ]
                    ]);
                }
            }
        }
        
        $this->console->writeln("There were {$newResults} new results found.");
        $this->log[] = "Cars found: {$newResults}";
        
        file_put_contents(__DIR__ .'/log.log', json_encode([
            'last_scan' => time(),
            'log' => $this->log
        ]));
    }
    
    /**
     * Get recent parse log
     */
    public function getLog()
    {
        $log = json_decode(file_get_contents(__DIR__ .'/log.log'));
        $log->last_scan = Carbon::parse('@'. $log->last_scan)->diffForHumans();
        
        return $log;
    }
    
    /**
     * Parse an individual listing
     */
    public function parseListingPage($id, ?Car $car = null)
    {
        $url = sprintf(
            self::ENDPOINT . self::ENDPOINT_API,
            $id,
            Uuid::uuid4()->toString()
        );
        
        $this->console->writeln("GET: <info>{$url}</info>");
        $response = $this->fetch($url);
        $this->console->writeln("Status Code: <comment>{$response->code}</comment>");
        
        if ($response->code != 200) {
            $car->setNotes('Car hidden/removed as it did not return 200 response, which might mean its listing has been removed');
    
            $this->em->persist($car);
            $this->em->flush();
            
            return $car;
        }
        
        $json = json_decode($response->html);
        
        // ignore cars above our search price
        if ($json->pageData->tracking->vehicle_price > self::SEARCH_QUERIES['price-to']) {
            return false;
        }
   
        //
        // Time to get all the data!
        //
        $car = $car ?: new Car();
        $car->setSiteId($id)
            ->setTitle($json->advert->title)
            ->setDescription($json->advert->description)
            ->setImages($json->advert->imageUrls)
            ->setPrice($json->pageData->tracking->vehicle_price)
            ->setPriceValuation($json->pageData->tracking->great_value)
            ->setYear($json->pageData->tracking->vehicle_year)
            ->setTax($json->pageData->tracking->annual_tax ?? 0)
            ->setFuel($json->pageData->tracking->fuel_type)
            ->setMiles($json->pageData->tracking->mileage)
            ->setGearbox($json->pageData->tracking->gearbox)
            ->setEngineSize($json->pageData->tracking->engine_size)
            ->setCheckStatus($json->pageData->tracking->vehicle_check_status)
            ->setSellerName($json->seller->name ?? 'no seller name')
            ->setSellerRating($json->seller->ratingStars ?? 0)
            ->setSellerReviews($json->seller->ratingTotalReviews ?? 0);
        
        // calculate car score
        $scoredata = $this->score($car);
        $car->setScore(array_sum($scoredata))
            ->setScoredata($scoredata);
        
        $car->setHash();
        
        $this->em->persist($car);
        $this->em->flush();
        
        return $car;
    }
    
    /**
     * Calculate score
     */
    private function score(Car $car)
    {
        $scoredata = [];
        
        //
        // Price (max: 50 pts)
        //
        $brackets = [
            50 => 22000,
            45 => 23000,
            42 => 24000,
            40 => 25000,
            38 => 27000,
            35 => 28500,
            30 => 30000,
        ];
        
        foreach ($brackets as $points => $value) {
            if ($car->getPrice() <= $value) {
                $scoredata['Price'] = $points;
                break;
            }
        }
    
    
        //
        // Miles (max: 35 pts)
        //
        $brackets = [
            35 => 5000,
            32 => 10000,
            30 => 15000,
            28 => 20000,
            15 => 30000
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getMiles() <= $value) {
                $scoredata['Miles'] = $points;
                break;
            }
        }
    
        //
        // Year (max: 15 pts)
        //
        $brackets = [
            15 => 2019,
            14 => 2018,
            13 => 2017,
            12 => 2016,
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getYear() == $value) {
                $scoredata['Year'] = $points;
                break;
            }
        }

        return $scoredata;
    }
}
