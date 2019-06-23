<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 11:57
 */

namespace SpecShaper\GdprBundle\Controller;

use Roromix\Bundle\SpreadsheetBundle\Factory;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SpecShaper\GdprBundle\Utils\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Coverage controller.
 *
 * A controller to provide reports on the status of the GDPR information held in the databases.
 *
 * @Route("/reporting")
 */
class ReportingController extends Controller
{
    /**
     * Coverage Action
     *
     * Action to return a report of all tables and parameters held, and what the GDPR information is.
     *
     * @Route("/coverage", name="reporting_coverage", methods={"GET"})
     */
    public function coverageAction(ReportService $reportSerivce){

        $response = $reportSerivce->getAllClassParameters();

        $date = new \DateTime('now');

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'GDPR Coverage Report-'.$date->format('d M Y') . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;


    }

    /**
     *
     *
     * @Route("/{table}/{parameter}/details", name="reporting_details", methods={"GET"})

     */
    public function detailsAction($table, $parameter){

    }
}