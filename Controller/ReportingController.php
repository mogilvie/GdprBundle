<?php

namespace SpecShaper\GdprBundle\Controller;

use SpecShaper\GdprBundle\Utils\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Coverage controller.
 *
 * A controller to provide reports on the status of the GDPR information held in the databases.
 */
class ReportingController extends AbstractController
{
    /**
     * Coverage Action.
     *
     * Action to return a report of all tables and parameters held, and what the GDPR information is.
     */
    public function coverageAction(ReportService $reportService): StreamedResponse
    {
        $response = $reportService->getAllClassParameters();

        $date = new \DateTime('now');

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'GDPR Coverage Report-'.$date->format('d M Y').'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

//    /**
//     * @Route("/{table}/{parameter}/details", name="reporting_details", methods={"GET"})
//     */
//    public function detailsAction($table, $parameter)
//    {
//    }
}
