<?php

namespace Etu\Core\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organization Group.
 *
 * @ORM\Table(name="etu_organization_groups")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class OrganizationGroup
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Assert\Length(min = "2", max = "50")
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(unique=true, fields={"name"}, separator="_", updatable=false,
     *     handlers={@Gedmo\SlugHandler(class="Gedmo\Sluggable\RelativeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="relationField", value="organization"),
     *          @Gedmo\SlugHandlerOption(name="relationSlugField", value="name"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="_")
     *          }
     *     )})
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $visible;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var Member[]
     *
     * @ORM\OneToMany(targetEntity="\Etu\Core\UserBundle\Entity\Member", mappedBy="group")
     * @ORM\JoinColumn()
     */
    protected $members;

    /**
     * @var OrganizationGroupAction[]
     *
     * @ORM\OneToMany(targetEntity="\Etu\Core\UserBundle\Entity\OrganizationGroupAction", mappedBy="group")
     * @ORM\JoinColumn()
     */
    protected $actions;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="\Etu\Core\UserBundle\Entity\Organization", inversedBy="groups")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $organization;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $deletedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->members = new \Doctrine\Common\Collections\ArrayCollection();
        $this->visible = true;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return OrganizationGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return OrganizationGroup
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OrganizationGroup
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set deletedAt.
     *
     * @param \DateTime|null $deletedAt
     *
     * @return OrganizationGroup
     */
    public function setDeletedAt($deletedAt = null)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt.
     *
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add member.
     *
     * @param \Etu\Core\UserBundle\Entity\Member $member
     *
     * @return OrganizationGroup
     */
    public function addMember(\Etu\Core\UserBundle\Entity\Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member.
     *
     * @param \Etu\Core\UserBundle\Entity\Member $member
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise
     */
    public function removeMember(\Etu\Core\UserBundle\Entity\Member $member)
    {
        return $this->members->removeElement($member);
    }

    /**
     * Get members.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set organization.
     *
     * @param \Etu\Core\UserBundle\Entity\Organization|null $organization
     *
     * @return OrganizationGroup
     */
    public function setOrganization(\Etu\Core\UserBundle\Entity\Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization.
     *
     * @return \Etu\Core\UserBundle\Entity\Organization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return OrganizationGroup
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Add action.
     *
     * @param \Etu\Core\UserBundle\Entity\OrganizationGroupAction $action
     *
     * @return OrganizationGroup
     */
    public function addAction(\Etu\Core\UserBundle\Entity\OrganizationGroupAction $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * Remove action.
     *
     * @param \Etu\Core\UserBundle\Entity\OrganizationGroupAction $action
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAction(\Etu\Core\UserBundle\Entity\OrganizationGroupAction $action)
    {
        return $this->actions->removeElement($action);
    }

    /**
     * Get actions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return OrganizationGroup
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}