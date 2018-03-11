<?php

namespace SpecShaper\GdprBundle\Twig;

use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Types\PersonalDataType;


class PersonalDataExtension extends \Twig_Extension
{

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
            case PersonalDataType::TYPE_CURRENCY:
                break;
            case PersonalDataType::TYPE_DATE:
            case PersonalDataType::TYPE_DATE_TIME:
                $displayValue = $displayValue->format($options['format']);
                break;
            case PersonalDataType::TYPE_DECIMAL:
                break;
            case PersonalDataType::TYPE_FLOAT:
                break;
            case PersonalDataType::TYPE_INTEGER:
                break;
            case PersonalDataType::TYPE_STRING:
                break;
            case PersonalDataType::TYPE_TEXT:
                break;
                case PersonalDataType::TYPE_BOOLEAN:
            break;
        }



        return  $displayValue;
    }

    public function getName()
    {
        return 'spec_shaper_gdpr_personal_data_extension';
    }
}
