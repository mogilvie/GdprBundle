<?php

namespace SpecShaper\GdprBundle\Command;

use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Column;
use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\EncryptBundle\Subscribers\DoctrineEncryptSubscriberInterface;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update data command.
 *
 * A command to convert a database to personal_data types, and update PersonalData entities to
 * match the entity filed annotations.
 *
 * terminal command = php bin/console gdpr:update
 * or
 * bin/console gdpr:update -t Parolla/SubscriptionBundle/Entity/Customer:billTo
 *
 * @author Mark Ogilvie <mark.ogilvie@ogilvieconsulting.net>
 */
class UpdateDataCommand extends Command
{

    private const TEMP_COL_PREFIX = 'gdpr_temp_';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Reader*/
    private $reader;

    /** @var Connection */
    private $connection;

    /**
     * @var array An array of the personal_data column types from the entities.
     */
    private $personalDataFields = [];

    private $numberOfColumns;

    /**
     * @var bool True if encryption has been disabled in the app config.
     */
    private $encryptionDisabled;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var Comparator
     */
    private $comparator;

    public function __construct(
        EntityManagerInterface $entityManager,
        Reader $reader,
        Connection $defaultConnection,
        EncryptorInterface $encryptor,
        $encryptionDisabled
    )
    {
        $this->em = $entityManager;
        $this->reader = $reader;
        $this->connection = $defaultConnection;
        $this->encryptor = $encryptor;
        $this->encryptionDisabled = $encryptionDisabled;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('gdpr:update')
            ->setDescription('Command to convert a database entry to a personal data object.')
            ->addOption('tables',
                't',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Select specific tables',
                [])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->comparator = new Comparator();

        $optionalEntityClasses = $this->getCommandProperties($input);

        // Populate the array with the entities and fields that use the personal_data column type.
        $this->setPersonalDataFields($optionalEntityClasses);

        // Create temporary data columns in each entity
        $this->createTempDataColumns($output);

        // Convert the existing data into a PersonalData object in temp column, and null original column.
        $this->createPersonalDataInTempColumn($output);

        // Change the original column to personal_data type
        $this->convertOriginalColumnDataType($output);

        // Copy the converted PersonalData back into the original column
        $this->reloadPersonalData($output);

        // Drop the temp columns
        $this->dropTempColumns($output);
        
        return Command::SUCCESS;
    }

    /**
     * Get any entities and properties from the command in an array.
     *
     * @param Input $input
     * @return array|bool
     */
    private function getCommandProperties(InputInterface $input): array|bool
    {

        // If command options are an empty array then return false.
        if(empty($input->getOption('tables'))) {
            return false;
        }

        $entityClasses = $input->getOption('tables');

        // Create an array to return classes and properties.
        $returnArray = [];

        // For each class, identify and add any specific properties.
        foreach($entityClasses as $entityClass){

            // Split command entity into class and property
            $classProperty = explode(':', $entityClass);

            // Class name
            $class = $classProperty[0];

            // If no class key exists then crate one.
            if(!array_key_exists($class, $entityClasses)){
                $entityClasses[$class] = [];
            }

            // If no property was appended to the class then return false, alse append the property
            if(!array_key_exists(1, $classProperty)){
                $returnArray[$class] = false;
            } else {
                $returnArray[$class][] = $classProperty[1];
            }
        }

        return $returnArray;
    }

    /**
     * Create Temp Data Columns.
     *
     * Clones the existing database, then modifies the schema of the clone to add personal data types.
     * Compares the original schema with the new modified schema
     * Create a schema diff between the two.
     * Execute the queries to alter the original database and create temporary personal data columns.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function createTempDataColumns( OutputInterface $output)
    {

        $classCount = 0;
        $propertyCount = 0;
        foreach($this->personalDataFields as $class => $properties){
            $classCount++;
            $propertyCount+= count($properties);
        }

        $this->numberOfColumns = $propertyCount;

        $output->writeln(sprintf('Creating temporary columns for %s classes and %s properties',$classCount, $propertyCount));

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make alterations to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->personalDataFields as $entityClass => $field) {

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

        // Execute the queries to modify the database.
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    /**
     * Create PersonalData object in a temporary column.
     *
     * Step through all the previously stored personal data columns.
     * If the original column data is already a personalData type then unserialize it and get the raw data.
     * Create a new PersonalData object using the current annotation information, serlialse and store in a temp
     * data column.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createPersonalDataInTempColumn( OutputInterface $output)
    {

        $output->writeln("Creating personal data objects in $this->numberOfColumns columns");

        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        $progressBar = new ProgressBar($output, $this->numberOfColumns);
        $progressBar->start();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->personalDataFields as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.'. $propertyArray['identifier'] .', t.'.$propertyArray['columnName'].' AS originalData')
                    ->from($propertyArray['tableName'], 't');

                $results = $queryBuilder->execute();

                // For each selected entity field create a personal data object and save it to the temporary field.
                foreach ($results as $result) {

                    // Get the original data from the query.
                    $personalData = $result['originalData'];

                    // Check if the string from the database contains the full PersonalData class name.
                    // If so then convert to an object and get the raw data.
                    if (stripos($personalData, PersonalData::class)) {
                        $personalDataObject = unserialize($personalData);
                        $personalData = $personalDataObject->getData();
                    }

                    // If the personal data should be encrypted then do so. Otherwise decrypt any existing value.
                    if (!$this->encryptionDisabled && $propertyArray['annotation']->options['isEncrypted'] === true) {
                        $personalData = $this->encryptor->encrypt($personalData);

                    } else {
                        $personalData = $this->encryptor->decrypt($personalData);
                    }

                    // Build a new PersonalData Object based on the current entity annotation fields.
                    $newPersonalDataObject = $this->createPersonalData($personalData);

                    // Update the database with the temporary data, set the original data to null.
                    $this->connection->update(
                        $tableName,
                        array(
                            $propertyArray['columnName'] => null,
                            $propertyArray['tempColName'] => serialize($newPersonalDataObject)
                        ),
                        array($propertyArray['identifier'] => $result[$propertyArray['identifier']])
                    );
                }
                $progressBar->advance();
            }

        }

        $progressBar->finish();
        $output->writeln(".");
    }

    /**
     * Convert Original Column data type to personal_data
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function convertOriginalColumnDataType( OutputInterface $output)
    {
        $output->writeln('Converting original columns to new personal data column type');

        // Alter the existing column to object data type and nullable.
        $schemaManager = $this->connection->getSchemaManager();
        $fromSchema = $schemaManager->createSchema();

        $toSchema = clone $fromSchema;

        foreach ($this->personalDataFields as $entityClass => $field) {
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

    /**
     * Reload PersonalData
     *
     * Move the PersonalData object from the temporary column back to the original column.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     */
    private function reloadPersonalData( OutputInterface $output)
    {
            $output->writeln('Reload personal data to original columns');

        // Get the query builder to load existing entity data.
        $queryBuilder = $this->connection->createQueryBuilder();

        $progressBar = new ProgressBar($output, $this->numberOfColumns);
        $progressBar->start();

        // Loop through all of the personal data fields in all entities.
        foreach ($this->personalDataFields as $entityClass => $field) {

            // Get the name of the entity table in the database.
            $tableName = $this->em->getClassMetadata($entityClass)->getTableName();

            // Loop through each personal_data field in the entity.
            foreach ($field as $propertyArray) {

                // Get all data for the current entity and field.
                $queryBuilder
                    ->select('t.'. $propertyArray['identifier'] .', t.'.$propertyArray['tempColName'].' AS newPersonalData')
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
                        array($propertyArray['identifier'] => $result[$propertyArray['identifier']])
                    );
                }
                $progressBar->advance();
            }

        }
        $progressBar->finish();
        $output->writeln(".");
    }

    /**
     * Drop the temporary columns from the schema and remove from the database.
     *
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function dropTempColumns( OutputInterface $output)
    {
        $output->writeln('Dropping temporary columns');

        $schemaManager = $this->connection->getSchemaManager();

        // Get a copy of the schema before any temp columns area created.
        $fromSchema = $schemaManager->createSchema();

        // Clone the schema to make altetions to.
        $toSchema = clone $fromSchema;

        // Loop through all of the personal data fields in all entities.
        foreach ($this->personalDataFields as $entityClass => $field) {

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

    /**
     * Get PersonalData fields.
     *
     * Visit every entity and identify fields that contain a personal_data annotation.
     * Store the field to an array for processing.
     *
     * @return array
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function setPersonalDataFields($entityClasses = false)
    {

        $managedEntities = $this->em->getMetadataFactory()->getAllMetadata();

        /** @var ClassMetadata $managedEntity */
        foreach ($managedEntities as $managedEntity) {

            // Ignore mapped superclass entities.
            if (property_exists($managedEntity, 'isMappedSuperclass') && $managedEntity->isMappedSuperclass === true) {
                continue;
            }

            $entityClass = str_replace('\\', '/', $managedEntity->getName());

            // If specific entity classes have been provided with the command, and this isnt one of them, then continue;
            if($entityClasses !== false
                && !array_key_exists(str_replace('\\', '/', $entityClass), $entityClasses)){
                continue;
            }

            $reflectionProperties = $managedEntity->getReflectionProperties();

            $targetProperties = false;

            if(!empty($entityClasses)){
                $targetProperties = $entityClasses[$entityClass];
            }

            /** @var \ReflectionProperty $refProperty */
            foreach ($reflectionProperties as $refProperty) {
                $this->getReferenceProperties($managedEntity, $refProperty, $targetProperties);
            }
        }

        return $this->personalDataFields;
    }

    private function getReferenceProperties(ClassMetadata $managedEntity, \ReflectionProperty $refProperty, $targetProperties){

        // If specific entity classes have been provided with the command
        if($targetProperties !== false){
            // If the class has command has specific properties

            if(!empty($targetProperties)){
                // If this property isn't one of them, then continue
                if(array_search($refProperty->getName(), $targetProperties) === false){
                    return;
                }
            }
        }

        foreach ($this->reader->getPropertyAnnotations($refProperty) as $key => $annotation) {

            // Skip any annotation that is not a Column type.
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
            $this->personalDataFields[$managedEntity->getName()][$refProperty->getName()] = [
                'tableName' => $managedEntity->getTableName(),
                'identifier' => $managedEntity->getSingleIdentifierColumnName(),
                'columnName' => $columnName,
                'tempColName' => $this->getTempColumnName($columnName),
                'refProperty' => $refProperty,
                'annotation' => $annotation,
            ];
        }
    }

    private function getTempColumnName($originalColumnName)
    {
        return self::TEMP_COL_PREFIX.$originalColumnName;
    }

    private function createPersonalData($data)
    {

        $personalData = new \SpecShaper\GdprBundle\Model\PersonalData();

        $personalData->setData($data);

        return $personalData;

    }
}
