<?php

namespace SpecShaper\GdprBundle\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Column;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update data command.
 *
 * A command to convert a database to personal_data types, and update PersonalData entities to
 * match the entity filed annotations.
 *
 * terminal command = php bin/console gdpr:update
 *
 * @author Mark Ogilvie <mark.ogilvie@ogilvieconsulting.net>
 */
class UpdateDataCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AnnotationReader */
    private $reader;

    /** @var Connection */
    private $connection;

    private $personalDataFields = [];

    private $comparator;

    protected function configure()
    {
        $this
            ->setName('gdpr:update')
            ->setDescription('Command to convert a database entry to a personal data object.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->reader = $this->getContainer()->get('annotation_reader');
        $this->connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $this->comparator = new Comparator();

        // Populate the array with the entities and fields that use the personal_data column type.
        $this->getPersonalDataFields();

        // Create temporary data columns in each entity
        $this->createTempDataColumns();

        // Convert the existing data into a PersonalData object in temp column, and null original column.
        $this->createPersonalDataInTempColumn();

        // Change the original column to personal_data type
        $this->convertOriginalColumnDataType();

        // Copy the converted PersonalData back into the original column
        $this->reloadPersonalData();

        // Drop the temp columns
        $this->dropTempColumns();

    }

    private function createTempDataColumns()
    {

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make altetions to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Find the entity table in the schema.
            $table = $toSchema->getTable($tableName);

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Set the existing column to nullable and no max
                $column = $table->getColumn($propertyArray['columnName']);
                $column->setNotnull(false);

                // Create a new temporary column in the table to store the existing data as a PersonalData object.
                if (!$table->hasColumn($propertyArray['tempColName'])) {
                    $table->addColumn($propertyArray['tempColName'], Type::OBJECT);
                }

            }
        }

        $platform = $schemaManager->getDatabasePlatform();

        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);

        $queries = $schemaDiff->toSql($platform); // queries to get from one to another schema.

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    private function createPersonalDataInTempColumn()
    {

        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.id, t.'.$propertyArray['columnName'].' AS originalData')
                    ->from($propertyArray['tableName'], 't');

                $results = $queryBuilder->execute();

                // For each selected entity field create a personal data object and save it to the temporary field.
                foreach ($results as $result) {

                    // Get the original data from the query.
                    $personalData = $result['originalData'];

                    // Check if the string from the database contains the full PersonalData class name. If so then convert to an object
                    if(stripos($personalData, PersonalData::class)){
                        $personalData = unserialize($personalData);
                    }

                    // Check if the data is a PersonalData object, if not then convert it to one.
                    if (!$personalData instanceof PersonalData) {
                        $personalData = $this->createPersonalData($personalData);
                    }

                    // Update the database with the temporary data, set the original data to null.
                    $this->connection->update(
                        $tableName,
                        array(
                            $propertyArray['columnName'] => null,
                            $propertyArray['tempColName'] => serialize($personalData)
                        ),
                        array('id' => $result['id'])
                    );

                }
            }
        }
    }



    private function convertOriginalColumnDataType(){
        // Alter the existing column to object data type and nullable.

        $schemaManager = $this->connection->getSchemaManager();
        $fromSchema = $schemaManager->createSchema();

        $toSchema = clone $fromSchema;

        foreach ($this->getPersonalDataFields() as $entityClass => $field) {
            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Find the entity table in the schema.
            $table = $toSchema->getTable($tableName);

            foreach ($field as $propertyArray) {

                $origionalColName = $propertyArray['columnName'];

                $column = $table->getColumn($origionalColName);

                $type = Type::getType('personal_data');

                $column
                    ->setType($type)
                    ->setNotnull(false)
                    ->setLength(null)
                    ;

            }
        }

        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);
        $platform = $schemaManager->getDatabasePlatform();
        $queries = $schemaDiff->toSql($platform); // queries to get from one to another schema.

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }


    private function reloadPersonalData(){
        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.id, t.'.$propertyArray['tempColName'].' AS newPersonalData')
                    ->from($propertyArray['tableName'], 't');

                $results = $queryBuilder->execute();

                // For each selected entity field create a personal data object and save it to the temporary field.
                foreach ($results as $result) {

                    // Get the copied personal_data from the query.
                    $personalData = $result['newPersonalData'];

                    // Update the database with the temporary data, set the original data to null.
                    $this->connection->update(
                        $tableName,
                        array(
                            $propertyArray['columnName'] => $personalData
                        ),
                        array('id' => $result['id'])
                    );
                }
            }
        }
    }

    private function dropTempColumns(){

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make altetions to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->getPersonalDataFields() as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Find the entity table in the schema.
            $table = $toSchema->getTable($tableName);

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Drop the temporary column.
                if ($table->hasColumn($propertyArray['tempColName'])) {
                    $table->dropColumn($propertyArray['tempColName']);
                }

            }
        }

        $platform = $schemaManager->getDatabasePlatform();

        $schemaDiff = $this->comparator->compare($fromSchema, $toSchema);

        $queries = $schemaDiff->toSql($platform); // queries to get from one to another schema.

        // Run the queries.
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    private function getPersonalDataFields()
    {

        $managedEntities = $this->em->getMetadataFactory()->getAllMetadata();

        /** @var ClassMetadata $managedEntity */
        foreach ($managedEntities as $managedEntity) {

            // Ignore mapped supersclass entities.
            if (property_exists($managedEntity, 'isMappedSuperclass') && $managedEntity->isMappedSuperclass === true) {
                continue;
            }

            $entityClass = $managedEntity->getName();

            $reflectionProperites = $managedEntity->getReflectionProperties();

            /** @var \ReflectionProperty $refProperty */
            foreach ($reflectionProperites as $refProperty) {

                foreach ($this->reader->getPropertyAnnotations($refProperty) as $key => $annotation) {

                    // Skip any anotation that is not a Column type.
                    if (!$annotation instanceof Column) {
                        continue;
                    }

                    // Ignore any column that is not of a personal_data type.
                    if ($annotation->type !== PersonalDataType::NAME) {
                        continue;
                    }

                    // @todo throw an error if foreign keys or primary keys are attached?

                    // Get the table column name.
                    $columnName = $managedEntity->getColumnName($refProperty->getName());

                    // Store the field data information for later use.
                    $this->personalDataFields[$entityClass][$refProperty->getName()] = [
                        'tableName' => $managedEntity->getTableName(),
                        'columnName' => $columnName,
                        'tempColName' => $this->getTempColumnName($columnName),
                        'refProperty' => $refProperty,
                        'annotation' => $annotation,
                    ];

                }
            }
        }

        return $this->personalDataFields;
    }

    private function getTempColumnName($originalColumnName)
    {
        return 'gdpr_temp_'.$originalColumnName;
    }

    private function createPersonalData($data)
    {

        $personalData = new \SpecShaper\GdprBundle\Model\PersonalData();

        $personalData->setData($data);

        return $personalData;

    }
}
