<?php

namespace SpecShaper\GdprBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * History.
 *
 * An entity to store the history of any changes/activity to PersonalData objects
 *
 * @ORM\Entity(repositoryClass="SpecShaper\GdprBundle\Repository\HistoryRepository")
 */
class History
{
    public const ACTION_CREATED = 'CREATED';
    public const ACTION_UPDATED = 'UPDATED';
    public const ACTION_DELETED = 'DELETED';
    public const ACTION_DISPOSED = 'DISPOSED';
    public const ACTION_READ = 'READ';
    public const ACTION_EXPORTED = 'EXPORTED';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected int $id;

    /**
     * The entity class name.
     *
     * @ORM\Column(type="string")
     */
    protected string $entity;

    /**
     * The entity field name.
     *
     * @ORM\Column(type="string")
     */
    protected string $field;

    /**
     * The action performed on the PersonalData object.
     *
     * @ORM\Column(type="string")
     */
    protected string $action;

    /**
     * The date and time of the action.
     *
     * @ORM\Column(type="datetime")
     */
    protected DateTimeInterface $actionDate;

    /**
     * The user that initiated the action.
     *
     * Null if the a system generated action.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected string $actionBy;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return History
     */
    public function setId(int $id): History
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     * @return History
     */
    public function setEntity(string $entity): History
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return History
     */
    public function setField(string $field): History
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return History
     */
    public function setAction(string $action): History
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getActionDate(): DateTimeInterface
    {
        return $this->actionDate;
    }

    /**
     * @param DateTimeInterface $actionDate
     * @return History
     */
    public function setActionDate(DateTimeInterface $actionDate): History
    {
        $this->actionDate = $actionDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionBy(): string
    {
        return $this->actionBy;
    }

    /**
     * @param string $actionBy
     * @return History
     */
    public function setActionBy(string $actionBy): History
    {
        $this->actionBy = $actionBy;
        return $this;
    }

}
