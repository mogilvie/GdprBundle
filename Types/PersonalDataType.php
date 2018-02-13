<?php

namespace SpecShaper\GdprBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\ConversionException;
use SpecShaper\GdprBundle\Model\PersonalData;

/**
 * Personal Data Object.
 *
 * @author Mark Ogilvie
 */
final class PersonalDataType extends ArrayType
{
    const NAME = 'personal_data';
    /**
     * @param array            $fieldDeclaration
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);

    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is read from the database. Make your conversions here, optionally using the $platform.
        if (null === $value) {
            return null;
        }

        $personalData = new PersonalData();

        if(is_array($value) === false) {
            $personalData->setData($value);
        } else {
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
        }


        return $personalData;
    }


    /**
     * @param  \SpecShaper\GdprBundle\Model\PersonalData  $personalData
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     *
     * @return null
     */
    public function convertToDatabaseValue($personalData, AbstractPlatform $platform)
    {
        // This is executed when the value is written to the database. Make your conversions here, optionally using the $platform.
//        if (null === $personalData) {
//            return null;
//        }
//
//        return $personalData->serialize();

        if (empty($personalData)) {
            return null;
        }

        if ($personalData instanceof PersonalData) {
            return (string) $personalData->serialize();
        }

        throw ConversionException::conversionFailed($personalData, self::NAME);


    }


    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }



}
