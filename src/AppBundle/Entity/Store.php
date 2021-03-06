<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Criteria;

/**
 * @ORM\Entity
 * @ORM\Table(name="t_store")
 */
class Store
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="title",type="string", length=200)
     */
    protected $title;
    /**
     * @ORM\Column(name="page_header_img",type="string", length=60,nullable=true)
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png", "image/tiff"},
     *     maxSizeMessage = "图片最大只能为5M.",
     *     mimeTypesMessage = "只能上传图片."
     * )
     */
    protected $pageHeaderImg;
    /**
     * @ORM\Column(name="store_name",type="string", length=200)
     */
    protected $storeName;
    /**
     * @ORM\Column(name="address",type="string", length=200)
     */
    protected $address;
    /**
     * @ORM\Column(name="tel",type="string", length=120)
     */
    protected $tel;
    /**
     * @ORM\Column(name="info",type="text")
     */
    protected $info;
    /**
     * @ORM\Column(name="description",type="text")
     */
    protected $description;
    /**
     * @ORM\Column(name="wx_title",type="string", length=200)
     */
    protected $wxTitle;
    /**
     * @ORM\Column(name="wx_desc",type="string", length=200,nullable=true)
     */
    protected $wxDesc;
    /**
     * @ORM\Column(name="wx_img",type="string", length=200)
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png", "image/tiff"},
     *     maxSizeMessage = "图片最大只能为5M.",
     *     mimeTypesMessage = "只能上传图片."
     * )
     */
    protected $wxImg;
    /**
     * @ORM\Column(name="password",type="string", length=40)
     */
    protected $password;
    /**
     * @ORM\Column(name="is_active",type="boolean")
     */
    protected $isActive = 1;
    /**
     * @ORM\Column(name="create_time",type="datetime")
     */
    protected $createTime;
    /**
     * @ORM\Column(name="create_ip",type="string", length=60)
     */
    protected $createIp;
    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="store")
     * @ORM\OrderBy({"orderId" = "ASC"})
     */
    protected $items;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Store
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set pageHeaderImg
     *
     * @param string $pageHeaderImg
     * @return Store
     */
    public function setPageHeaderImg($pageHeaderImg)
    {
        $this->pageHeaderImg = $pageHeaderImg;

        return $this;
    }

    /**
     * Get pageHeaderImg
     *
     * @return string 
     */
    public function getPageHeaderImg()
    {
        return $this->pageHeaderImg;
    }

    /**
     * Set storeName
     *
     * @param string $storeName
     * @return Store
     */
    public function setStoreName($storeName)
    {
        $this->storeName = $storeName;

        return $this;
    }

    /**
     * Get storeName
     *
     * @return string 
     */
    public function getStoreName()
    {
        return $this->storeName;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Store
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set tel
     *
     * @param string $tel
     * @return Store
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string 
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return Store
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Store
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set wxTitle
     *
     * @param string $wxTitle
     * @return Store
     */
    public function setWxTitle($wxTitle)
    {
        $this->wxTitle = $wxTitle;

        return $this;
    }

    /**
     * Get wxTitle
     *
     * @return string 
     */
    public function getWxTitle()
    {
        return $this->wxTitle;
    }

    /**
     * Set wxDesc
     *
     * @param string $wxDesc
     * @return Store
     */
    public function setWxDesc($wxDesc)
    {
        $this->wxDesc = $wxDesc;

        return $this;
    }

    /**
     * Get wxDesc
     *
     * @return string 
     */
    public function getWxDesc()
    {
        return $this->wxDesc;
    }

    /**
     * Set wxImg
     *
     * @param string $wxImg
     * @return Store
     */
    public function setWxImg($wxImg)
    {
        $this->wxImg = $wxImg;

        return $this;
    }

    /**
     * Get wxImg
     *
     * @return string 
     */
    public function getWxImg()
    {
        return $this->wxImg;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return Store
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
     * @return Store
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
     * Add items
     *
     * @param \AppBundle\Entity\Item $items
     * @return Store
     */
    public function addItem(\AppBundle\Entity\Item $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param \AppBundle\Entity\Item $items
     */
    public function removeItem(\AppBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * Get activeItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActiveItems()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('isActive', 1));
        return $this->items->matching($criteria);
        //return $this->items;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Store
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Store
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
