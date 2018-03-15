<?php

namespace SpecShaper\GdprBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ObjectType;
use SpecShaper\GdprBundle\Model\PersonalData;

/**
 * Personal Data Object.
 *
 * @author Mark Ogilvie
 */
final class PersonalDataType extends ObjectType
{
    const NAME = 'personal_data';

    const TYPE_CURRENCY = "currency";
    const TYPE_DATE = "date";
    const TYPE_DATE_TIME = "dateTime";
    const TYPE_DECIMAL = "decimal";
    const TYPE_FLOAT = "float";
    const TYPE_INTEGER = "integer";
    const TYPE_STRING = "string";
    const TYPE_TEXT = "text";
    const TYPE_BOOLEAN = "boolean";


//    public function getColumnDefinition(array $tableColumn, AbstractPlatform $platform)
//    {
//        $tableColumn += ['default' => null, 'null' => null, 'comment' => null];
//        $options = [
//            'length'        => 0,
//            'unsigned'      => null,
//            'fixed'         => null,
//            'default'       => $tableColumn['default'],
//            'notnull'       => (bool) ($tableColumn['null'] != 'YES'),
//            'scale'         => null,
//            'precision'     => null,
//            'autoincrement' => false,
//            'comment'       => empty($tableColumn['comment']) ? null : $tableColumn['comment'],
//        ];
//        $column = new Column($tableColumn['field'], $this, $options);
//        if (preg_match_all("/'([^']+)'/", $tableColumn['type'], $matches)) {
//            $column->setCustomSchemaOption('values', $matches[1]);
//        }
//        return $column;
//    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is read from the database. Make your conversions here, optionally using the $platform.
        if (null === $value) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        $personalData = unserialize($value);

        return $personalData;
    }


    /**
     * @param  \SpecShaper\GdprBundle\Model\PersonalData  $personalData
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is written to the database. Make your conversions here, optionally using the $platform.
//        if (null === $personalData) {
//            return null;
//        }
//
//        return $personalData->serialize();

        if (empty($value)) {
            return null;
        }

        return serialize($value);
    }

    private function convertArrayToPersonalData(array $value){
        $personalData = new PersonalData();

            $personalData
                ->setData($value['data'])
                ->setFormat($value['format'])
                ->setLength($value['length'])
                ->setScale($value['length'])
                ->setIsSensitive($value['isSensitive'])
                ->setIsEncrypted($value['isEncrypted'])
                ->setIdentifiableBy($value['identifiableBy'])
                ->setProvidedBy($value['providedBy'])
                ->setMethodOfReceipt($value['methodOfReceipt'])
                ->setReceiptProtection($value['receiptProtection'])
                ->setRetainFor($value['retainFor'])
                ->setDisposeBy($value['disposeBy'])
                ->setKeepUntil( $value['keepUntil'])
                ->setMethodOfReceipt($value['methodOfReturn'])
                ->setReturnProtection($value['returnProtection'])
            ;

        return $personalData;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

}
