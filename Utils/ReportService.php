<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 12:08
 */

namespace SpecShaper\GdprBundle\Utils;


use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use SpecShaper\GdprBundle\Annotations\PersonalData;

class ReportService
{
    private $entityManager;
    private $reader;

    public function __construct(EntityManagerInterface $entityManager, Reader $reader)
    {
        $this->entityManager = $entityManager;
        $this->reader = $reader;

    }

    /**
     * Get all class parameters.
     *
     * Function to get all the classes from the data base entity manager.
     * Iterates through each entity and gets all paramters.
     * If the parameter is a PersonalData parameter then get all the fields of the annotation.
     *
     * Returns a Excel Streamed Response
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getAllClassParameters()
    {
        $managedEntities = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $result = [];

        /** @var ClassMetadata $managedEntity */
        foreach ($managedEntities as $managedEntity) {

            $entityClass = $managedEntity->getName();

            $reflectionProperites = $managedEntity->getReflectionProperties();

            /** @var \ReflectionProperty $refProperty */
            foreach ($reflectionProperites as $refProperty) {

                $result[$entityClass][$refProperty->getName()] = false;

                foreach ($this->reader->getPropertyAnnotations($refProperty) as $key => $annotation) {

                    if ($annotation instanceof PersonalData) {
                        $result[$entityClass][$refProperty->getName()] = (array)$annotation;
                    }
                }
            }
        }

        return $this->createSpreadsheet($result);

    }

    /**
     * Create Spreadsheet
     *
     * Creates an excel spreadhsheet response from the array of entities and parameters.
     *
     * @param $entities
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createSpreadsheet($entities){

        $factory = new Factory();

        $spreadsheet = $factory->createSpreadsheet();

        $now = new \DateTime('now');

        $spreadsheet
            ->getProperties()->setCreator("Parolla")
            ->setLastModifiedBy("Parolla")
            ->setTitle('Personal Data Report-' . $now->format('Y-m-d'))
            ->setSubject('Personal Data Report')
            ->setDescription('Personal Data Report')
            ->setKeywords('Personal Data Report')
            ->setCategory('Personal Data Report');


        $headingPopulated = false;

        $row = 2;
        $headingRow = 1;
        $activeSheet = $spreadsheet->setActiveSheetIndex(0);
        $classColumn = 'A';
        $paramColumn = 'B';
        $privateColumnStart = 'C';

        $activeSheet->setCellValue($classColumn.$headingRow, 'Entity class');
        $activeSheet->setCellValue($paramColumn.$headingRow, 'Parameter');
        /** @var Payslip $payslip */
        foreach($entities as $className => $parameters){

            foreach ($parameters as $parameterName => $personaData) {
                $activeSheet->setCellValue($classColumn.$row, $className);
                $activeSheet->setCellValue($paramColumn.$row, $parameterName);

                if($personaData !== false){
                    $column = $privateColumnStart;
                    foreach($personaData as $dataField => $value){


                        if($headingPopulated === false){
                            $activeSheet->setCellValue($column.$headingRow, $dataField);
                        }
                        $activeSheet->setCellValue($column.$row, $value);
                        $column++;
                    }
                }
                $row++;
            }

        }

        $activeSheet->setTitle('Coverage Report');

        // Apply filters to all the columns.
        $activeSheet->setAutoFilter('A1:J1');

        // Size the first two columns for class name and property name
        $activeSheet->getColumnDimension('A')->setAutoSize(true);
        $activeSheet->getColumnDimension('B')->setAutoSize(true);


        // Set the heading row bold.
        $headingRowSytle =  $activeSheet->getStyle('A1:J1');
        $headingRowSytle->getFont()->setBold(true);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // create the writer
        $writer = $factory->createWriter($spreadsheet);

        // create the response
        $response = $factory->createStreamedResponse($writer);

        return $response;

}

}