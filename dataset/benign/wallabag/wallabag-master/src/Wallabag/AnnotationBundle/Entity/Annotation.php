<?php

namespace Wallabag\AnnotationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\EntityTimestampsTrait;
use Wallabag\UserBundle\Entity\User;

/**
 * Annotation.
 *
 * @ORM\Table(name="annotation")
 * @ORM\Entity(repositoryClass="Wallabag\AnnotationBundle\Repository\AnnotationRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("none")
 */
class Annotation
{
    use EntityTimestampsTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @Assert\Length(
     *     max = 10000,
     *     maxMessage = "validator.quote_length_too_high"
     * )
     * @ORM\Column(name="quote", type="text")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $quote;

    /**
     * @var array
     *
     * @ORM\Column(name="ranges", type="array")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $ranges;

    /**
     * @Exclude
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @Exclude
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\CoreBundle\Entity\Entry", inversedBy="annotations")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="cascade")
     */
    private $entry;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
     * Set text.
     *
     * @param string $text
     *
     * @return Annotation
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get quote.
     *
     * @return string
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set quote.
     *
     * @param string $quote
     *
     * @return Annotation
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get ranges.
     *
     * @return array
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * Set ranges.
     *
     * @param array $ranges
     *
     * @return Annotation
     */
    public function setRanges($ranges)
    {
        $this->ranges = $ranges;

        return $this;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Annotation
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @VirtualProperty
     * @SerializedName("user")
     */
    public function getUserName()
    {
        return $this->user->getName();
    }

    /**
     * Set entry.
     *
     * @param Entry $entry
     *
     * @return Annotation
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
        $entry->setAnnotation($this);

        return $this;
    }

    /**
     * Get entry.
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @VirtualProperty
     * @SerializedName("annotator_schema_version")
     */
    public function getVersion()
    {
        return 'v1.0';
    }
}
