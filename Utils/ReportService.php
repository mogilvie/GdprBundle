<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 01/02/18
 * Time: 12:08.
 */

namespace SpecShaper\GdprBundle\Utils;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Column;
use Roromix\Bundle\SpreadsheetBundle\Factory;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{

    public function __construct(private EntityManagerInterface $entityManager, private Reader $reader)
    { }

    /**
     * Get all class parameters.
     *
     * Function to get all the classes from the database entity manager.
     * Iterates through each entity and gets all parameters.
     * If the parameter is a PersonalData parameter then get all the fields of the annotation.
     *
     * Returns an Excel via Streamed Response
     */
    public function getAllClassParameters(): StreamedResponse
    {
        $managedEntities = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $result = [];

        /** @var ClassMetadata $managedEntity */
        foreach ($managedEntities as $managedEntity) {
            $entityClass = $managedEntity->getName();

            $reflectionProperties = $managedEntity->getReflectionProperties();

            /** @var \ReflectionProperty $refProperty */
            foreach ($reflectionProperties as $refProperty) {
                foreach ($this->reader->getPropertyAnnotations($refProperty) as $key => $annotation) {
                    // Skip any anotation that is not a Column type.
                    if (!$annotation instanceof Column) {
                        continue;
                    }

                    $options = false;

                    // If the column type is personal data then store the options in the array.
                    if (PersonalDataType::NAME === $annotation->type) {
                        $options = (array) $annotation->options;
                    }

                    $result[$entityClass][$refProperty->getName()] = $options;
                }
            }
        }

        return $this->createSpreadsheet($result);
    }

    /**
     * Create Spreadsheet.
     *
     * Creates an Excel spreadsheet response from the array of entities and parameters.
     */
    private function createSpreadsheet(array $entities): StreamedResponse
    {
        $factory = new Factory();

        $spreadsheet = $factory->createSpreadsheet();

        $now = new \DateTime('now');

        $spreadsheet
            ->getProperties()->setCreator('Parolla')
            ->setLastModifiedBy('Parolla')
            ->setTitle('Personal Data Report-'.$now->format('Y-m-d'))
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

        foreach ($entities as $className => $fields) {
            foreach ($fields as $field => $personaDataValue) {
                $activeSheet->setCellValue($classColumn.$row, $className);
                $activeSheet->setCellValue($paramColumn.$row, $field);

                // If the entity field has personal data then add it to the spreadsheet.
                if (false !== $personaDataValue) {
                    // For each personal data field add the value.
                    foreach ($personaDataValue as $dataField => $value) {
                        // If the array of personal data fields doesn't contain this one, then add it.
                        if (!in_array($dataField, $personalDataColumnMap)) {
                            $personalDataColumnMap[] = $dataField;
                        }

                        // Get the column number for the data field in the array, and add the starting index.
                        $col = array_search($dataField, $personalDataColumnMap) + $privateColumnStart;

                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }

                        $activeSheet->setCellValueByColumnAndRow($col, $row, $value);
                    }
                }
                ++$row;
            }
        }

        // Add the headings for the personal data fields.
        foreach ($personalDataColumnMap as $key => $field) {
            $col = $key + $privateColumnStart;
            $activeSheet->setCellValueByColumnAndRow($col, $headingRow, $field);
        }

        $activeSheet->setTitle('Coverage Report');

        // Apply filters to all the columns.
        $activeSheet->setAutoFilter('A1:J1');

        // Size the first two columns for class name and property name
        $activeSheet->getColumnDimension('A')->setAutoSize(true);
        $activeSheet->getColumnDimension('B')->setAutoSize(true);

        // Set the heading row bold.
        $headingRowStyle = $activeSheet->getStyle('A1:J1');
        $headingRowStyle->getFont()->setBold(true);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // create the writer
        $writer = $factory->createWriter($spreadsheet);

        // create the response
        return $factory->createStreamedResponse($writer);
    }
}
