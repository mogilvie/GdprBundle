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
use Doctrine\ORM\Mapping\Column;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use SpecShaper\GdprBundle\Types\PersonalDataType;

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

                foreach ($this->reader->getPropertyAnnotations($refProperty) as $key => $annotation) {

                    // Skip any anotation that is not a Column type.
                    if (!$annotation instanceof Column) {
                        continue;
                    }

                    $options = false;

                    // If the column type is personal data then store the options in the array.
                    if ($annotation->type === PersonalDataType::NAME) {
                        $options = (array)$annotation->options;
                    }

                    $result[$entityClass][$refProperty->getName()] = $options;
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

        $row = 2;
        $headingRow = 1;
        $activeSheet = $spreadsheet->setActiveSheetIndex(0);
        $classColumn = 'A';
        $paramColumn = 'B';
        $privateColumnStart = 3;

        $activeSheet->setCellValue($classColumn.$headingRow, 'Entity class');
        $activeSheet->setCellValue($paramColumn.$headingRow, 'Parameter');

        // Create an array to hold the map of column index number to PersonalData field name.
        $personalDataColumnMap = [];

        foreach($entities as $className => $fields){

            foreach ($fields as $field => $personaDataValue) {
                $activeSheet->setCellValue($classColumn.$row, $className);
                $activeSheet->setCellValue($paramColumn.$row, $field);

                // If the entity field has personal data then add it to the spreadsheet.
                if($personaDataValue !== false){

                    // For each personal data field add the value.
                    foreach($personaDataValue as $dataField => $value){

                        // If the array of personal data fields doesnt contain this one, then add it.
                        if(!in_array($dataField, $personalDataColumnMap)){
                            $personalDataColumnMap[] = $dataField;
                        }

                        // Get the column number for the datafield in the array, and add the starting index.
                        $col = array_search($dataField, $personalDataColumnMap) + $privateColumnStart;

                        $activeSheet->setCellValueByColumnAndRow($col,$row, $value);
                    }
                }
                $row++;
            }

        }

        // Add the headings for the personal data fields.
        foreach($personalDataColumnMap as $key => $field){
            $col = $key + $privateColumnStart;
            $activeSheet->setCellValueByColumnAndRow($col,$headingRow, $field);
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