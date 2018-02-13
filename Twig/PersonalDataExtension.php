<?php

namespace SpecShaper\GdprBundle\Twig;

use SpecShaper\EncryptBundle\Encryptors\EncryptorInterface;
use SpecShaper\GdprBundle\Model\PersonalData;


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
    public function convertPersonalData($personalData)
    {

        $displayValue = $personalData->getData();

        if($personalData->isEncrypted()){
            $displayValue = $this->encryptor->decrypt($displayValue);
        }

        if($personalData->isPurged()){
            $displayValue = 'XXXX';
        }

        return  $displayValue;
    }

    public function getName()
    {
        return 'spec_shaper_gdpr_personal_data_extension';
    }
}
