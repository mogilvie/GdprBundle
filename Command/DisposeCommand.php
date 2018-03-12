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
 * Dispose Command
 *
 * A command to visit each PersonalData field in every database table and dispose of any data which has expired.
 *
 * terminal command = php bin/console gdpr:dispose
 *
 * @author Mark Ogilvie <mark.ogilvie@ogilvieconsulting.net>
 */
class DisposeCommand extends ContainerAwareCommand
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
            ->setName('gdpr:dispose')
            ->setDescription('Command to dispose of expired data in a personal_data field.')
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

        // Filter the personal_data fields for those that have expired
        $this->checkDisposalDates();

        // Dispose of data.
        $this->disposeData();

        // Generate a report of data disposed.
        $this->generateReport();
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
}
