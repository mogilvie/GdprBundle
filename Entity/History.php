<?php

namespace SpecShaper\GdprBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * History.
 *
 * An entity to store the history of any changes/activity to PersonalData objects
 *
 * @todo Add getters and setters
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
}
