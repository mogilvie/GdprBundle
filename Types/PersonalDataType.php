<?php

namespace SpecShaper\GdprBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use SpecShaper\GdprBundle\Model\PersonalData;
use Doctrine\DBAL\Types\Type;

/**
 * Personal Data Object.
 *
 * @author Mark Ogilvie
 */
final class PersonalDataType extends Type
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
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

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
     * @param PersonalData            $value
     * @param AbstractPlatform $platform
     * @return mixed|null|string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {

        if (empty($value)) {
            return null;
        }

        if ($value instanceof PersonalData) {
            return serialize($value);
        }

        throw ConversionException::conversionFailed($value, self::NAME);

    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return bool
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
