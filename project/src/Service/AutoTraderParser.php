<?php

namespace App\Service;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Rct567\DomQuery\DomQuery;
use Symfony\Component\Console\Output\ConsoleOutput;

class AutoTraderParser extends HttpService
{
    const ENDPOINT        = 'https://www.autotrader.co.uk';
    const ENDPOINT_SEARCH = '/car-search';
    const ENDPOINT_VIEW   = '/classified/advert/';
    const ENDPOINT_API    = '/json/fpa/initial/%s?guid=%s';
    const SEARCH_QUERIES  = [
        'minimum-badge-engine-size' => '2.0',
        'seller-type' => 'trade',
        'radius' => '1500',
        'year-from' => '2015',
        'aggregatedTrim' => 'S line',
        'postcode' => 'CH5 4WX',
        'maximum-mileage' => '80000',
        'model' => 'TT',
        'make' => 'AUDI',
        'body-type' => 'Coupe',
        'price-to' => '18000',
        'search-target' => 'usedcars',
        'sort' => 'datedesc',
        'exclude-writeoff-categories' => 'on',
    ];
    
    /** @var DomQuery */
    private $dom;
    /** @var ConsoleOutput */
    private $console;
    /** @var EntityManagerInterface */
    private $em;
    /** @var CarRepository */
    private $repository;
    
    public function __construct(EntityManagerInterface $em, CarRepository $repository)
    {
        $this->console    = new ConsoleOutput();
        $this->repository = $repository;
        $this->em         = $em;
    }
    
    public function parse()
    {
        //
        // Search
        //
        $url = self::ENDPOINT . self::ENDPOINT_SEARCH . '?' . http_build_query(self::SEARCH_QUERIES);
        $this->console->writeln("GET: <info>{$url}</info>");
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
        $this->console->writeln("Parsing search results");
        
        /** @var DomQuery $li */
        foreach ($this->dom->find('.search-page__result') as $li) {
            $link = trim($li->find('.listing-fpa-link')->attr('href'));
            $link = explode('?', $link)[0];
            
            $id = str_ireplace(self::ENDPOINT_VIEW, null, $link);
            
            // Check if we've already parsed these.
            /** @var Car $car */
            $car = $this->repository->findOneBy([ 'siteId' => $id ]);
            
            $this->parseListingPage($id, $car);
        }
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
        $json = json_decode($response->html);
   
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
            ->setTax($json->pageData->tracking->annual_tax)
            ->setFuel($json->pageData->tracking->fuel_type)
            ->setMiles($json->pageData->tracking->mileage)
            ->setGearbox($json->pageData->tracking->gearbox)
            ->setEngineSize($json->pageData->tracking->engine_size)
            ->setCheckStatus($json->pageData->tracking->vehicle_check_status)
            ->setSellerName($json->seller->name)
            ->setSellerRating($json->seller->ratingStars ?? 0)
            ->setSellerReviews($json->seller->ratingTotalReviews ?? 0);
        
        // calculate car score
        $scoredata = $this->score($car);
        $car->setScore(array_sum($scoredata))
            ->setScoredata($scoredata);
        
        $this->em->persist($car);
        $this->em->flush();
    }
    
    /**
     * Calculate score
     */
    private function score(Car $car)
    {
        $scoredata = [];
        
        //
        // Price
        //
        $brackets = [
            50 => 12000,
            35 => 13500,
            15 => 15000,
            5  => 18000
        ];
        
        foreach ($brackets as $points => $value) {
            if ($car->getPrice() <= $value) {
                $scoredata['Price'] = $points;
                break;
            }
        }
    
    
        //
        // Miles
        //
        $brackets = [
            50 => 20000,
            30 => 40000,
            20 => 55000,
            10 => 65000
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getMiles() <= $value) {
                $scoredata['Miles'] = $points;
                break;
            }
        }
    
        //
        // Valuation
        //
        $brackets = [
            20 => 'low',
            10 => 'good',
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getPriceValuation() == $value) {
                $scoredata['Valuation'] = $points;
                break;
            }
        }
    
        //
        // Year
        //
        $brackets = [
            50 => 2019,
            30 => 2018,
            20 => 2017,
            15 => 2016,
            10 => 2015,
            5  => 2014,
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getPriceValuation() == $value) {
                $scoredata['Year'] = $points;
                break;
            }
        }
        
        //
        // Tax
        //
        $brackets = [
            25 => 30,
            20 => 40,
            15 => 80,
            10 => 120,
            5  => 200,
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getTax() <= $value) {
                $scoredata['Miles'] = $points;
                break;
            }
        }
    
        //
        // Fuel
        //
        $brackets = [
            10 => 'Patrol',
            5  => 'Diesel',
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getGearbox() == $value) {
                $scoredata['Gear'] = $points;
                break;
            }
        }
    
        //
        // Engine Size
        //
        $brackets = [
            5 => '2.0',
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getEngineSize() == $value) {
                $scoredata['EngineSize'] = $points;
                break;
            }
        }
    
        //
        // Checks Passed
        //
        $brackets = [
            5 => 'PASSED',
        ];
    
        foreach ($brackets as $points => $value) {
            if ($car->getCheckStatus() == $value) {
                $scoredata['CheckStatus'] = $points;
                break;
            }
        }

        return $scoredata;
    }
}
