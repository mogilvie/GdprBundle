<?php

namespace SpecShaper\GdprBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 * 
 * An entity to store the history of any changes/activity to PersonalData objects
 *
 * @todo Add getters and setters
 * @ORM\Entity(repositoryClass="SpecShaper\GdprBundle\Repository\HistoryRepository")
 */
class History
{
    const ACTION_CREATED = "CREATED";
    const ACTION_UPDATED = "UPDATED";
    const ACTION_DELETED = "DELETED";
    const ACTION_DISPOSED = "DISPOSED";
    const ACTION_READ = "READ";
    const ACTION_EXPORTED = "EXPORTED";
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * The entity class name.
     *
     * @ORM\Column(type="string")
     */
    protected $entity;

    /**
     * The entity field name.
     *
     * @ORM\Column(type="string")
     */
    protected $field;
    
    /**
     * The action performed on the PersonalData object
     *
     * @ORM\Column(type="string")
     */
    protected $action;

    /**
     * The date and time of the action.
     *
     * @ORM\Column(type="datetime")
     */
    protected $actionDate;
    
    /**
     * The user that initiated the action.
     * 
     * Null if the a system generated action.
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $actionBy;
    
  
}
