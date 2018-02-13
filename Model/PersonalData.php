<?php

namespace SpecShaper\GdprBundle\Model;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;

/**
 * Personal Data Object.
 *
 * @author Mark Ogilvie
 */
class PersonalData
{
    const ID_METHOD_DIRECT = 'DIRECT';
    const ID_METHOD_INDIRECT = 'INDIRECT';

    const DISPOSE_BY_DELETION = 'DELETE';
    const DISPOSE_BY_AGGREGATE = 'AGGREGATE';
    const DISPOSE_BY_ANONYMITY = 'ANONYMITY';

    const TRANSFER_METHOD_HTTP  = 'HTTP';
    const TRANSFER_METHOD_HTTPS  = 'HTTPS';
    const TRANSFER_METHOD_FTP = 'FTP';
    const TRANSFER_METHOD_FTPS = 'FTPS';
    const TRANSFER_METHOD_POST  = 'POST';
    const TRANSFER_METHOD_REGISTERED_POST  = 'REGISTERED_POST';
    const TRANSFER_METHOD_PHONE  = 'PHONE';

    const FORMAT_DATETIME = 'DATETIME';
    const FORMAT_STRING = 'STRING';
    const FORMAT_FLOAT = 'FLOAT';
    const FORMAT_INTEGER = 'INTEGER';

    /**
     * True if the information is classified as sensitive personal information.
     *
     * Gender identity, health details, political affiliations.
     *
     * @var string
     */
    public $data;

    /**
     * The format that the data is stored in.
     *
     * @var string
     */
    public $format;

    /**
     * The max length of the data.
     *
     * If a number then it is also considered the precision.
     *
     * @var integer
     */
    public $length;

    /**
     * The number of digits after the decimal of a number
     *
     * @var integer
     */
    public $scale;

    /**
     * True if the data has been purged.
     *
     * @var boolean
     */
    public $isPurged;

    /**
     * True if the information is classified as sensitive personal information.
     *
     * Gender identity, health details, political affiliations.
     *
     * @var boolean
     */
    public $isSensitive;

    /**
     * If the parameter should be encrypted.
     *
     * @var boolean
     */
    public $isEncrypted;

    /**
     * @var string The method by which the information might identify an individual
     */
    public $identifiableBy;

    /**
     * Who provided the data.
     *
     * Was it user supplied, client supplied, generated?
     *
     * @var string
     */
    public $providedBy;

    /**
     * How did we receive this information?
     *  @var string
     */
    public $methodOfReceipt;

    /**
     * How is the data protected in transit
     * @var string
     */
    public $receiptProtection;

    /**
     * Why do you need this data
     *
     * @var string A text note explaining why this information is required.
     */
    public $purposeFor;

    /**
     * How long the information is to be retained in the database for.
     *
     * @var string A interval period string such as P6Y
     */
    public $retainFor;

    /**
     * Disposal method.
     *
     * What happens at the end of the data's usefulness.
     * Dispose, aggregate or make anonymous
     *
     * @var string
     */
    public $disposeBy;

    /**
     * Keep Until
     *
     * The date when the data will be kep until
     *
     * @var \DateTimeInterface
     */
    public $keepUntil;

    /**
     * How do we return this data
     *
     * @var string
     */
    public $methodOfReturn;

    /**
     * How is the date protected when returned, rendered or displayed?
     *
     * @var string
     */
    public $returnProtection;

    public function __construct(){

        $this->isSensitive = false;
        $this->isEncrypted = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function serialize()
    {
        return (array) $this;

//        return [
//            'data' => $this->data,
//            'isSensitive' => $this->isSensitive,
//            'isEncrypted' => $this->isEncrypted,
//            'identifiableBy' => $this->identifiableBy,
//            'providedBy' => $this->providedBy,
//            'methodOfReceipt' => $this->methodOfReceipt,
//            'receiptProtection'=> $this->receiptProtection,
//            'retainFor' => $this->retainFor,
//            'disposeBy' => $this->disposeBy,
//            'keepUntil' => $this->keepUntil,
//            'methodOfReturn' =>$this->methodOfReturn,
//            'returnProtection' =>$this->returnProtection
//        ];
    }

    /**
     * @return bool
     */
    public function isPurged(): bool
    {
        return $this->isPurged;
    }

    /**
     * Set IsPurged.
     *
     * @param bool $isPurged
     *
     * @return PersonalData
     */
    public function setIsPurged(bool $isPurged): PersonalData
    {
        $this->isPurged = $isPurged;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSensitive(): bool
    {
        return $this->isSensitive;
    }

    /**
     * Set IsSensitive.
     *
     * @param bool $isSensitive
     *
     * @return PersonalData
     */
    public function setIsSensitive(bool $isSensitive): PersonalData
    {
        $this->isSensitive = $isSensitive;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    /**
     * Set IsEncrypted.
     *
     * @param bool $isEncrypted
     *
     * @return PersonalData
     */
    public function setIsEncrypted(bool $isEncrypted): PersonalData
    {
        $this->isEncrypted = $isEncrypted;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifiableBy(): string
    {
        return $this->identifiableBy;
    }

    /**
     * Set IdentifiableBy.
     *
     * @param string $identifiableBy
     *
     * @return PersonalData
     */
    public function setIdentifiableBy(string $identifiableBy): PersonalData
    {
        $this->identifiableBy = $identifiableBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvidedBy(): string
    {
        return $this->providedBy;
    }

    /**
     * Set ProvidedBy.
     *
     * @param string $providedBy
     *
     * @return PersonalData
     */
    public function setProvidedBy(string $providedBy): PersonalData
    {
        $this->providedBy = $providedBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getPurposeFor(): string
    {
        return $this->purposeFor;
    }

    /**
     * Set PurposeFor.
     *
     * @param string $purposeFor
     *
     * @return PersonalData
     */
    public function setPurposeFor(string $purposeFor): PersonalData
    {
        $this->purposeFor = $purposeFor;

        return $this;
    }

    /**
     * @return string
     */
    public function getRetainFor(): string
    {
        return $this->retainFor;
    }

    /**
     * Set RetainFor.
     *
     * @param string $retainFor
     *
     * @return PersonalData
     */
    public function setRetainFor(string $retainFor): PersonalData
    {
        $this->retainFor = $retainFor;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisposeBy(): string
    {
        return $this->disposeBy;
    }

    /**
     * Set DisposeBy.
     *
     * @param string $disposeBy
     *
     * @return PersonalData
     */
    public function setDisposeBy(string $disposeBy): PersonalData
    {
        $this->disposeBy = $disposeBy;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getKeepUntil(): \DateTimeInterface
    {
        return $this->keepUntil;
    }

    /**
     * Set KeepUntil.
     *
     * @param \DateTimeInterface $keepUntil
     *
     * @return PersonalData
     */
    public function setKeepUntil(\DateTimeInterface $keepUntil): PersonalData
    {
        $this->keepUntil = $keepUntil;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReturnFormat()
    {
        return $this->returnFormat;
    }

    /**
     * Set ReturnFormat.
     *
     * @param mixed $returnFormat
     *
     * @return PersonalData
     */
    public function setReturnFormat($returnFormat)
    {
        $this->returnFormat = $returnFormat;

        return $this;
    }

    /**
     * @return blob
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set Data.
     *
     * @param blob $data
     *
     * @return PersonalData
     */
    public function setData($data): PersonalData
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethodOfReceipt(): string
    {
        return $this->methodOfReceipt;
    }

    /**
     * Set MethodOfReceipt.
     *
     * @param string $methodOfReceipt
     *
     * @return PersonalData
     */
    public function setMethodOfReceipt(string $methodOfReceipt): PersonalData
    {
        $this->methodOfReceipt = $methodOfReceipt;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiptProtection(): string
    {
        return $this->receiptProtection;
    }

    /**
     * Set ReceiptProtection.
     *
     * @param string $receiptProtection
     *
     * @return PersonalData
     */
    public function setReceiptProtection(string $receiptProtection): PersonalData
    {
        $this->receiptProtection = $receiptProtection;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethodOfReturn(): string
    {
        return $this->methodOfReturn;
    }

    /**
     * Set MethodOfReturn.
     *
     * @param string $methodOfReturn
     *
     * @return PersonalData
     */
    public function setMethodOfReturn(string $methodOfReturn): PersonalData
    {
        $this->methodOfReturn = $methodOfReturn;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnProtection(): string
    {
        return $this->returnProtection;
    }

    /**
     * Set ReturnProtection.
     *
     * @param string $returnProtection
     *
     * @return PersonalData
     */
    public function setReturnProtection(string $returnProtection): PersonalData
    {
        $this->returnProtection = $returnProtection;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set Format.
     *
     * @param string $format
     *
     * @return PersonalData
     */
    public function setFormat(string $format): PersonalData
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * Set Length.
     *
     * @param int $length
     *
     * @return PersonalData
     */
    public function setLength(int $length): ?PersonalData
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     * Set Scale.
     *
     * @param int $scale
     *
     * @return PersonalData
     */
    public function setScale(int $scale): PersonalData
    {
        $this->scale = $scale;

        return $this;
    }



}
