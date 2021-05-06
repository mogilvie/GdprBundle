<?php

namespace SpecShaper\GdprBundle\Twig;

use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;

class PersonalDataExtension extends \Twig_Extension
{

    const TYPE_CURRENCY = 'currency';
    const TYPE_DATE = 'date';
    const TYPE_DATE_TIME = 'date_time';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_FLOAT = 'float';
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_BOOLEAN = 'boolean';

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('personal_data', array($this, 'convertPersonalData'))
        );
    }

    /**
     * @param PersonalData $personalData
     * @return string
     */
    public function convertPersonalData($personalData, $type = null, $options = [])
    {
        // If the data is empty then return empty.
        if(empty($personalData)){
            return null;
        }
        
        // If value is not a personal data object then return it.
        if(!$personalData instanceof PersonalData){
            return $personalData;
        }

        // If the data is expired then return the expired value
        if($personalData->isExpired()){
            return $personalData->getData();
        }

        // Get the data from the PersonalData Object
        $displayValue = $personalData->getData();

        // Always decrypt, actual decruption will only happen if the data has the <ENC> suffix.
        $displayValue = $this->encryptor->decrypt($displayValue);

        // Format the number according to the data type and twig variable display options.
        switch($type){
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



        return  $displayValue;
    }

    public function getName()
    {
        return 'spec_shaper_gdpr_personal_data_extension';
    }
}
