<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="t_item")
 */
class Item
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="name",type="string", length=200)
     */
    protected $name;
    /**
     * @ORM\Column(name="img_url",type="string", length=200, nullable=true)
     */
    protected $imgUrl;
    /**
     * @ORM\Column(name="detail_img_url",type="string", length=200, nullable=true)
     */
    protected $detailImgUrl;
    /**
     * @ORM\Column(name="intro",type="string", length=200, nullable=true)
     */
    protected $intro;
    /**
     * @ORM\Column(name="num",type="integer")
     */
    protected $num;
    /**
     * @ORM\Column(name="win_num",type="integer")
     */
    protected $winNum;
    /**
     * @ORM\Column(name="bargain_num",type="integer")
     */
    protected $bargainNum;
    /**
     * @ORM\Column(name="price",type="decimal", scale=2)
     */
    protected $price;
    /**
     * @ORM\Column(name="discount_price",type="decimal", scale=2)
     */
    protected $discountPrice;
    /**
     * @ORM\Column(name="bargain_price",type="decimal", scale=2)
     */
    protected $bargainPrice;
    /**
     * @ORM\Column(name="create_time",type="datetime")
     */
    protected $createTime;
    /**
     * @ORM\Column(name="create_ip",type="string", length=60)
     */
    protected $createIp;
    /**
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="items")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id")
     */
    protected $store;
    /**
     * @ORM\OneToMany(targetEntity="Bargain", mappedBy="item")
     */
    protected $bargains;
    /**
     * @ORM\Column(name="order_id",type="integer")
     */
    protected $orderId;
    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = 0;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bargains = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set imgUrl
     *
     * @param string $imgUrl
     * @return Item
     */
    public function setImgUrl($imgUrl)
    {
        $this->imgUrl = $imgUrl;

        return $this;
    }

    /**
     * Get imgUrl
     *
     * @return string 
     */
    public function getImgUrl()
    {
        return $this->imgUrl;
    }

    /**
     * Set detailImgUrl
     *
     * @param string $detailImgUrl
     * @return Item
     */
    public function setDetailImgUrl($detailImgUrl)
    {
        $this->detailImgUrl = $detailImgUrl;

        return $this;
    }

    /**
     * Get detailImgUrl
     *
     * @return string 
     */
    public function getDetailImgUrl()
    {
        return $this->detailImgUrl;
    }

    /**
     * Set intro
     *
     * @param string $intro
     * @return Item
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * Get intro
     *
     * @return string 
     */
    public function getIntro()
    {
        return $this->intro;
    }

    /**
     * Set num
     *
     * @param integer $num
     * @return Item
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return integer 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set winNum
     *
     * @param integer $winNum
     * @return Item
     */
    public function setWinNum($winNum)
    {
        $this->winNum = $winNum;

        return $this;
    }

    /**
     * Get winNum
     *
     * @return integer 
     */
    public function getWinNum()
    {
        return $this->winNum;
    }

    /**
     * Set bargainNum
     *
     * @param integer $bargainNum
     * @return Item
     */
    public function setBargainNum($bargainNum)
    {
        $this->bargainNum = $bargainNum;

        return $this;
    }

    /**
     * Get bargainNum
     *
     * @return integer 
     */
    public function getBargainNum()
    {
        return $this->bargainNum;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return Item
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set discountPrice
     *
     * @param string $discountPrice
     * @return Item
     */
    public function setDiscountPrice($discountPrice)
    {
        $this->discountPrice = $discountPrice;

        return $this;
    }

    /**
     * Get discountPrice
     *
     * @return string 
     */
    public function getDiscountPrice()
    {
        return $this->discountPrice;
    }

    /**
     * Set bargainPrice
     *
     * @param string $bargainPrice
     * @return Item
     */
    public function setBargainPrice($bargainPrice)
    {
        $this->bargainPrice = $bargainPrice;

        return $this;
    }

    /**
     * Get bargainPrice
     *
     * @return string 
     */
    public function getBargainPrice()
    {
        return $this->bargainPrice;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return Item
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set createIp
     *
     * @param string $createIp
     * @return Item
     */
    public function setCreateIp($createIp)
    {
        $this->createIp = $createIp;

        return $this;
    }

    /**
     * Get createIp
     *
     * @return string 
     */
    public function getCreateIp()
    {
        return $this->createIp;
    }

    /**
     * Set store
     *
     * @param \AppBundle\Entity\Store $store
     * @return Item
     */
    public function setStore(\AppBundle\Entity\Store $store = null)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get store
     *
     * @return \AppBundle\Entity\Store 
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Add bargains
     *
     * @param \AppBundle\Entity\Bargain $bargains
     * @return Item
     */
    public function addBargain(\AppBundle\Entity\Bargain $bargains)
    {
        $this->bargains[] = $bargains;

        return $this;
    }

    /**
     * Remove bargains
     *
     * @param \AppBundle\Entity\Bargain $bargains
     */
    public function removeBargain(\AppBundle\Entity\Bargain $bargains)
    {
        $this->bargains->removeElement($bargains);
    }

    /**
     * Get bargains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBargains()
    {
        return $this->bargains;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    public function increaseWinNum()
    {
        ++$this->winNum;
        return $this;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     * @return Item
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return integer 
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
