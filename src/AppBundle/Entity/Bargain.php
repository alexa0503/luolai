<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="t_bargain")
 */
class Bargain
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="WechatUser", inversedBy="bargains")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="bargains")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     */
    protected $item;
    /**
     * @ORM\Column(name="num",type="integer")
     */
    protected $num;
    /**
     * @ORM\Column(name="price",type="decimal", scale=2)
     */
    protected $price;
    /**
     * @ORM\Column(name="is_winner",type="boolean")
     */
    protected $isWinner = 0;
    /**
     * @ORM\Column(name="is_bought",type="boolean")
     */
    protected $isBought = 0;
    /**
     * @ORM\Column(name="exchange_code",type="string", length=40)
     */
    protected $exchangeCode;
    /**
     * @ORM\Column(name="create_time",type="datetime")
     */
    protected $createTime;
    /**
     * @ORM\Column(name="create_ip",type="string", length=60)
     */
    protected $createIp;
    /**
     * @ORM\OneToMany(targetEntity="BargainLog", mappedBy="bargain")
     */
    protected $logs;
    /**
     * @ORM\OneToMany(targetEntity="BuyLog", mappedBy="bargain")
     */
    protected $buyLogs;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->buyLogs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set num
     *
     * @param integer $num
     * @return Bargain
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
     * Set price
     *
     * @param string $price
     * @return Bargain
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
     * Set isWinner
     *
     * @param boolean $isWinner
     * @return Bargain
     */
    public function setIsWinner($isWinner)
    {
        $this->isWinner = $isWinner;

        return $this;
    }

    /**
     * Get isWinner
     *
     * @return boolean 
     */
    public function getIsWinner()
    {
        return $this->isWinner;
    }

    /**
     * Set isBought
     *
     * @param boolean $isBought
     * @return Bargain
     */
    public function setIsBought($isBought)
    {
        $this->isBought = $isBought;

        return $this;
    }

    /**
     * Get isBought
     *
     * @return boolean 
     */
    public function getIsBought()
    {
        return $this->isBought;
    }

    /**
     * Set exchangeCode
     *
     * @param string $exchangeCode
     * @return Bargain
     */
    public function setExchangeCode($exchangeCode)
    {
        $this->exchangeCode = $exchangeCode;

        return $this;
    }

    /**
     * Get exchangeCode
     *
     * @return string 
     */
    public function getExchangeCode()
    {
        return $this->exchangeCode;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return Bargain
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
     * @return Bargain
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
     * Set user
     *
     * @param \AppBundle\Entity\WechatUser $user
     * @return Bargain
     */
    public function setUser(\AppBundle\Entity\WechatUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\WechatUser 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set item
     *
     * @param \AppBundle\Entity\Item $item
     * @return Bargain
     */
    public function setItem(\AppBundle\Entity\Item $item = null)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \AppBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Add logs
     *
     * @param \AppBundle\Entity\BargainLog $logs
     * @return Bargain
     */
    public function addLog(\AppBundle\Entity\BargainLog $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param \AppBundle\Entity\BargainLog $logs
     */
    public function removeLog(\AppBundle\Entity\BargainLog $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * Get logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add buyLogs
     *
     * @param \AppBundle\Entity\BuyLog $buyLogs
     * @return Bargain
     */
    public function addBuyLog(\AppBundle\Entity\BuyLog $buyLogs)
    {
        $this->buyLogs[] = $buyLogs;

        return $this;
    }

    /**
     * Remove buyLogs
     *
     * @param \AppBundle\Entity\BuyLog $buyLogs
     */
    public function removeBuyLog(\AppBundle\Entity\BuyLog $buyLogs)
    {
        $this->buyLogs->removeElement($buyLogs);
    }

    /**
     * Get buyLogs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBuyLogs()
    {
        return $this->buyLogs;
    }
    public function increaseNum()
    {
        ++$this->num;
        return $this;
    }
}
