<?php
// src/AppBundle/Entity/User.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="t_user")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 * @UniqueEntity(fields="username", message="抱歉，该用户名已经被使用")
 * @UniqueEntity(fields="email", message="抱歉，该Email已经被使用")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     * @Assert\NotBlank(message = "用户名不能为空.")
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @Assert\NotBlank(message = "密码不能为空.", groups={"add"})
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @Assert\NotBlank(message = "Email不能为空.")
     * @Assert\Email(message = "Email不符合规则.")
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @Assert\NotBlank(message = "请选择用户状态.")
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = 1;
    /**
     * @ORM\Column(name="admin_stores", type="string", length=100)
     */
    private $adminStores;

    /**
     * @ORM\Column(name="create_time",  type="datetime")
     */
    private $createTime;

    /**
     * @ORM\Column(name="create_ip", type="string", length=60)
     */
    private $createIp;

    /**
     * @ORM\Column(name="last_update_ip", type="string", length=60)
     */
    private $lastUpdateIp;

    /**
     * @ORM\Column(name="last_update_time", type="datetime")
     */
    private $lastUpdateTime;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     *
     */
    private $roles;




    public function __construct()
    {
        $this->isActive = true;
        $this->roles = new ArrayCollection();
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid(null, true));
    }
    
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return 'UffXXGPwuhLzS6rV';
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            //$this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            //$this->salt
        ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
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

    /**
     * Set createTime
     * 
     * @param string $createTime
     * @return User
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return boolean 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set createIp
     * 
     * @param string $createTime
     * @return User
     */
    public function setCreateIp($createIp)
    {
        $this->createIp = $createIp;

        return $this;
    }

    /**
     * Get createIp
     *
     * @return boolean 
     */
    public function getCreateIp()
    {
        return $this->createIp;
    }
    
    /**
     * Set lastUpdateIp
     * 
     * @param string $lastUpdateIp
     * @return User
     */
    public function setLastUpdateIp($lastUpdateIp)
    {
        $this->lastUpdateIp = $lastUpdateIp;

        return $this;
    }

    /**
     * Get lastUpdateIp
     *
     * @return boolean 
     */
    public function getLastUpdateIp()
    {
        return $this->lastUpdateIp;
    }

     /**
     * Set lastUpdateTime
     * 
     * @param string $lastUpdateTime
     * @return User
     */
    public function setLastUpdateTime($lastUpdateTime)
    {
        $this->lastUpdateTime = $lastUpdateTime;

        return $this;
    }

    /**
     * Get lastUpdateTime
     *
     * @return boolean 
     */
    public function getLastUpdateTime()
    {
        return $this->lastUpdateTime;
    }

    /**
     * Add roles
     *
     * @param \AppBundle\Entity\Role $roles
     * @return User
     */
    public function addRole(\AppBundle\Entity\Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \AppBundle\Entity\Role $roles
     */
    public function removeRole(\AppBundle\Entity\Role $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Set adminStores
     *
     * @param string $adminStores
     * @return User
     */
    public function setAdminStores($adminStores)
    {
        $this->adminStores = $adminStores;
        return $this;
    }

    /**
     * Get adminStores
     *
     * @return string 
     */
    public function getAdminStores()
    {
        return $this->adminStores;
    }
}
