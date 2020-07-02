<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * - This has UpperCase variables as its game content
 * @ORM\Table(
 *     name="cars",
 *     indexes={
 *          @ORM\Index(name="site_id", columns={"site_id"}),
 *          @ORM\Index(name="added", columns={"added"}),
 *          @ORM\Index(name="deleted", columns={"deleted"}),
 *          @ORM\Index(name="fave", columns={"fave"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CarRepository")
 */
class Car
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="guid")
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $siteId;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $added;
    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $hash;

    // -- CAR --
    
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $score = 0;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $scoredata = '';
    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $images;
    /**
     * @var integer
     * @ORM\Column(type="integer", length=11)
     */
    private $price;
    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $priceValuation;
    /**
     * @var integer
     * @ORM\Column(type="integer", length=4)
     */
    private $year;
    /**
     * @var integer
     * @ORM\Column(type="integer", length=4)
     */
    private $tax;
    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $fuel;
    /**
     * @var integer
     * @ORM\Column(type="integer", length=10)
     */
    private $miles;
    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $gearbox;
    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $engineSize;
    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $checkStatus;
    /**
     * @var string
     * @ORM\Column(type="string", length=128)
     */
    private $sellerName;
    /**
     * @var string
     * @ORM\Column(type="string", length=8)
     */
    private $sellerRating;
    /**
     * @var integer
     * @ORM\Column(type="integer", length=10)
     */
    private $sellerReviews;
    /**
     * @var bool
     * @ORM\Column(type="boolean", length=10)
     */
    private $deleted = false;
    /**
     * @var bool
     * @ORM\Column(type="boolean", length=10)
     */
    private $fave = false;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $notes = '';
    /**
     * var array
     * @ORM\Column(type="array")
     */
    private $history = [];
    
    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->added = time();
    }
    
    /**
     * Grab an array of all the data
     */
    public function toArray()
    {
        $data = get_object_vars($this);
        $data['images'] = json_decode($data['images']);
        $data['scoredata'] = json_decode($data['scoredata']);
        return $data;
    }
    
    public function setHash()
    {
        $this->hash = sha1(implode('_', [
            $this->siteId,
            $this->title,
            $this->description,
            $this->images,
            $this->price,
            $this->year,
            $this->tax,
            $this->fuel,
            $this->miles,
            $this->gearbox,
            $this->engineSize,
            $this->checkStatus,
            $this->sellerName,
            $this->sellerRating,
        ]));
        
        return $this;
    }
    
    public function getHash(): string
    {
        return $this->hash;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function setId(string $id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getSiteId(): string
    {
        return $this->siteId;
    }
    
    public function setSiteId(string $siteId)
    {
        $this->siteId = $siteId;
        
        return $this;
    }
    
    public function getAdded(): int
    {
        return $this->added;
    }
    
    public function setAdded(int $added)
    {
        $this->added = $added;
        
        return $this;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function setTitle(string $title)
    {
        $this->title = $title;
        
        return $this;
    }
    
    public function getScore(): int
    {
        return $this->score;
    }
    
    public function setScore(int $score)
    {
        $this->score = $score;
        
        return $this;
    }
    
    public function getScoredata(): array
    {
        return json_decode($this->scoredata, true);
    }
    
    public function setScoredata(array $scoredata)
    {
        $this->scoredata = json_encode($scoredata);
        
        return $this;
    }
    
    public function getDescription()
    {
        $description = $this->description;
        $description = explode('. ', $description);
        $description = array_chunk($description, 2);
        
        foreach ($description as $i => $desc) {
            $description[$i] = implode($desc);
        }
    
        $description = implode("<br/><br/>", $description);
        
        return $description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    public function getImages()
    {
        return json_decode($this->images, true);
    }
    
    public function setImages($images)
    {
        $this->images = json_encode($images);
        
        return $this;
    }
    
    public function getPrice()
    {
        return $this->price;
    }
    
    public function setPrice($price)
    {
        $this->price = $price;
        
        return $this;
    }
    
    public function getPriceValuation()
    {
        return $this->priceValuation;
    }
    
    public function setPriceValuation($priceValuation)
    {
        $this->priceValuation = $priceValuation;
        
        return $this;
    }
    
    public function getYear()
    {
        return $this->year;
    }
    
    public function setYear($year)
    {
        $this->year = $year;
        
        return $this;
    }
    
    public function getTax()
    {
        return $this->tax;
    }
    
    public function setTax($tax)
    {
        $this->tax = $tax;
        
        return $this;
    }
    
    public function getFuel()
    {
        return $this->fuel;
    }
    
    public function setFuel($fuel)
    {
        $this->fuel = $fuel;
        
        return $this;
    }
    
    public function getMiles()
    {
        return $this->miles;
    }
    
    public function setMiles($miles)
    {
        $this->miles = $miles;
        
        return $this;
    }
    
    public function getGearbox()
    {
        return $this->gearbox;
    }
    
    public function setGearbox($gearbox)
    {
        $this->gearbox = $gearbox;
        
        return $this;
    }
    
    public function getEngineSize()
    {
        return $this->engineSize;
    }
    
    public function setEngineSize($engineSize)
    {
        $this->engineSize = $engineSize;
        
        return $this;
    }
    
    public function getCheckStatus()
    {
        return $this->checkStatus;
    }
    
    public function setCheckStatus($checkStatus)
    {
        $this->checkStatus = $checkStatus;
        
        return $this;
    }
    
    public function getSellerName()
    {
        return $this->sellerName;
    }
    
    public function setSellerName($sellerName)
    {
        $this->sellerName = $sellerName;
        
        return $this;
    }
    
    public function getSellerRating()
    {
        return $this->sellerRating;
    }
    
    public function setSellerRating($sellerRating)
    {
        $this->sellerRating = $sellerRating;
        
        return $this;
    }
    
    public function getSellerReviews()
    {
        return $this->sellerReviews;
    }
    
    public function setSellerReviews($sellerReviews)
    {
        $this->sellerReviews = $sellerReviews;
        
        return $this;
    }
    
    public function getNotes(): string
    {
        return $this->notes;
    }
    
    public function setNotes(string $notes)
    {
        $this->notes = $notes;
        
        return $this;
    }
    
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    
    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;
        
        return $this;
    }
    
    public function isFave(): bool
    {
        return $this->fave;
    }
    
    public function setFave(bool $fave)
    {
        $this->fave = $fave;
        
        return $this;
    }
    
    public function getHistory()
    {
        return $this->history;
    }
    
    public function setHistory($history)
    {
        $this->history = $history;
        
        return $this;
    }
    
    public function addHistory($history)
    {
        $this->history[] = $history;
        
        return $this;
    }
}
