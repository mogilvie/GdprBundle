<?php

namespace SpecShaper\GdprBundle\Utils;

use SpecShaper\GdprBundle\Model\PersonalData;
use SpecShaper\GdprBundle\Utils\Disposal\Anonymise;
use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseDate;
use SpecShaper\GdprBundle\Utils\Disposal\AnonymiseIP;
use SpecShaper\GdprBundle\Utils\Disposal\DisposalInterface;
use SpecShaper\GdprBundle\Utils\Disposal\RegexReplace;
use SpecShaper\GdprBundle\Utils\Disposal\SetNull;

class Disposer
{

    public function disposeByPersonalData(PersonalData $personalData)
    {
        $disposedData = $this->dispose(
            $personalData->getData(),
            $personalData->getDisposeBy(),
            $personalData->getDisposeByArgs()
        );

        $personalData->setData($disposedData);

        return $personalData;
    }

    public function dispose($data, $method, $args)
    {

        /** @var DisposalInterface $disposer */
        switch($method){
            case PersonalData::DISPOSE_BY_ANONYMISE_DATE:
                $disposer = new AnonymiseDate($args);
                break;
            case PersonalData::DISPOSE_BY_ANONYMISE_IP:
                $disposer = new AnonymiseIP($args);
                break;
            case PersonalData::DISPOSE_BY_ANONYMISE:
                $disposer = new Anonymise($args);
                break;
            case PersonalData::DISPOSE_BY_REGEX_REPLACE:
                $disposer = new RegexReplace($args);
                break;
            default:
                $disposer = new SetNull();
        }

        $anonValue = $disposer->dispose($data);

        return $anonValue;
    }
}