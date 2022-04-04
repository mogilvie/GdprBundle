<?php

namespace SpecShaper\GdprBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Portability controller.
 *
 * A controller to serve the files when an owner or an individual request electronic
 * versions of the information held in the database.
 *
 * @Route("/portability")
 */
class PortabilityController extends AbstractController
{
    /**
     * Owner Action.
     *
     * Action to return the full information held pertaining to an organisation.
     *
     * @Route("/{id}/owner", name="portability_owner", methods={"GET"})
     */
    public function ownerAction()
    {
    }

    /**
     * Individual Action.
     *
     * Action to return all the information held pertaining to an individual.
     *
     * @Route("/{id}/individual", name="portability_individual", methods={"GET"})
     */
    public function individualAction()
    {
    }
}
