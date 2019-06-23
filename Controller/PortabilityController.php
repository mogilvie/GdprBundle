<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 11:57
 */

namespace SpecShaper\GdprBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Portability controller.
 *
 * A controller to serve the files when an owner or an individual request electronic
 * versions of the information held in the database.
 *
 * @Route("/portability")
 */
class PortabilityController extends Controller
{
    /**
     * Owner Action
     *
     * Action to return the full information held pertaining to an organisation.
     *
     * @Route("/{id}/owner", name="portability_owner", methods={"GET"})
     *
     */
    public function ownerAction()
    {

    }

    /**
     * Individual Action
     *
     * Action to return all the information held pertaining to an individual.
     *
     * @Route("/{id}/individual", name="portability_individual", methods={"GET"})
     */
    public function individualAction()
    {

    }
}