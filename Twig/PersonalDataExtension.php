<?php

namespace SpecShaper\GdprBundle\Twig;

use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Model\PersonalData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PersonalDataExtension extends AbstractExtension
{
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_DATE = 'date';
    public const TYPE_DATE_TIME = 'date_time';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_FLOAT = 'float';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_TEXT = 'text';
    public const TYPE_BOOLEAN = 'boolean';

    private EncryptorInterface $encryptor;

    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('personal_data', [$this, 'convertPersonalData']),
        ];
    }

    /**
     * @param PersonalData $personalData
     *
     * @return string
     */
    public function convertPersonalData(mixed $personalData, ?string $type = null, ?array $options = []): ?string
    {
        // If the data is empty then return empty.
        if (empty($personalData)) {
            return null;
        }

        // If value is not a personal data object then return it.
        if (!$personalData instanceof PersonalData) {
            return $personalData;
        }

        // If the data is expired then return the expired value
        if ($personalData->isExpired()) {
            return $personalData->getData();
        }

        // Get the data from the PersonalData Object
        $displayValue = $personalData->getData();

        // Always decrypt, actual decruption will only happen if the data has the <ENC> suffix.
        $displayValue = $this->encryptor->decrypt($displayValue);

        // Format the number according to the data type and twig variable display options.
        switch ($type) {
            case self::TYPE_CURRENCY:
                break;
            case self::TYPE_DATE:
            case self::TYPE_DATE_TIME:
                $displayValue = $displayValue->format($options['format']);
                break;
            case self::TYPE_DECIMAL:
                break;
            case self::TYPE_FLOAT:
                break;
            case self::TYPE_INTEGER:
                break;
            case self::TYPE_STRING:
                break;
            case self::TYPE_TEXT:
                break;
                case self::TYPE_BOOLEAN:
            break;
        }

        return $displayValue;
    }

    public function getName(): string
    {
        return 'spec_shaper_gdpr_personal_data_extension';
    }
}
