<?php

namespace SpecShaper\GdprBundle\Model;

use Symfony\Component\Intl\NumberFormatter\NumberFormatter;
use SpecShaper\GdprBundle\Exception\GdprException;

/**
 * Personal Data Object.
 *
 * @author Mark Ogilvie
 */
class PersonalData
{
    /**
     * How the data item can identify an individual.
     */
    const ID_METHOD_DIRECT = 'DIRECT';
    const ID_METHOD_INDIRECT = 'INDIRECT';

    /**
     * What to do with the data when it becomes expired.
     */
    const DISPOSE_BY_DELETION = 'DELETE';
    const DISPOSE_BY_SET_NULL = 'SET_NULL';
    const DISPOSE_BY_AGGREGATE = 'AGGREGATE';
    const DISPOSE_BY_ANONYMISE = 'ANONYMISE';

    /**
     * How the data is received or transmitted between the provider and the data store.
     */
    const TRANSFER_METHOD_HTTP  = 'HTTP';
    const TRANSFER_METHOD_HTTPS  = 'HTTPS';
    const TRANSFER_METHOD_FTP = 'FTP';
    const TRANSFER_METHOD_FTPS = 'FTPS';
    const TRANSFER_METHOD_PDF = "PDF";
    const TRANSFER_METHOD_ENCRYPTED_PDF = "ENCRYPTED_PDF";
    const TRANSFER_METHOD_EMAIL = "EMAIL";
    const TRANSFER_METHOD_POST  = 'POST';
    const TRANSFER_METHOD_REGISTERED_POST  = 'REGISTERED_POST';
    const TRANSFER_METHOD_PHONE  = 'PHONE';

    /**
     * What format the data is provided in, and should be displayed as by default.
     */
    const FORMAT_STRING = 'STRING';
    const FORMAT_DATE = 'DATE';
    const FORMAT_DATETIME = 'DATETIME';
    const FORMAT_CURRENCY = 'CURRENCY';
    const FORMAT_FLOAT = 'FLOAT';
    const FORMAT_INTEGER = 'INTEGER';

    /**
     * The data that is being stored.
     *
     * If encryption is enabled then this data is encrypted by the subscriber via the onFlush event.
     * It is decrypted by the onLoad event.
     *
     * When the original data becomes expired then it is replaced with the replacement value as specified
     * by the disposeBy field.
     *
     * Gender identity, health details, political affiliations.
     *
     * @var string
     */
    public $data;

    /**
     * The date time object that the data was created.
     *
     * Set in the subscriber via the GdprSubscriber via the UoW insert process.
     *
     * @var \DateTimeInterface
     */
    public $createdOn;

    /**
     * The date time object when the data was last updated.
     *
     * Set in the GdprSubscriber via the UoW update process
     *
     * @var \DateTimeInterface
     */
    public $updatedOn;

    /**
     * The format for the string data.
     * 
     * Use the constants provided in this class.
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
     * The number of digits after the decimal of a number.
     *
     * @var integer
     */
    public $scale;

    /**
     * True if the data isExpired.
     * 
     * When data becomes expired the original data is replaced with aggregated, nulled or anonymised data.
     *
     * @var boolean
     */
    public $isExpired;

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
     * The method that the data could be used to identify someone.
     * Either:
     * - DIRECT, such as a name, email address
     * - INDIRECT, such as a street address, job title
     *
     * Indirect information is where this particular piece of information might be combined with another
     * data set to identify an individual.
     *
     * @var string
     */
    public $idMethod;

    /**
     * Identifiable By
     *
     * A note on how this particular piece of data might be used to identify an invididual.
     *
     * @var string The method by which the information might identify an individual
     */
    public $identifiableBy;

    /**
     * Who typically provides the data.
     *
     * Was it user supplied, client supplied, generated?
     *
     * @var string
     */
    public $providedBy;

    /**
     * How did we receive this information?
     * 
     * The method of transfer from the supplier to this database.
     *
     * @var string
     */
    public $methodOfReceipt;

    /**
     * How is the data protected in transit.
     *
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
     * @var string A DateInterval string such as P6Y
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

    /**
     * Return a string of the data based on data format
     *
     * @return string
     */
    public function __toString()
    {
        // If the data is expired then return the raw data.
        if($this->isExpired === true){
            return $this->getData();
        }

        // If the data is current then format it according to type.
        switch($this->getFormat()){
            case PersonalData::FORMAT_DATE:
                $string = $this->getData()->format('d M Y');
                break;
            case PersonalData::FORMAT_DATETIME:
                $string = $this->getData()->format('d M Y h:m');
                break;
            case PersonalData::FORMAT_CURRENCY:
                $f = new NumberFormatter("en", NumberFormatter::CURRENCY);
                $string = $f->formatCurrency($this->getData(), "EUR");
                break;
            default:
                $string = $this->getData();
        }

        return $string;
    }

    public function __construct(){

        $this->isSensitive = false;
        $this->isEncrypted = false;
        $this->isExpired = false;

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

    }

    /**
     * @return bool
     */
    public function isExpired(): ?bool
    {
        return $this->isExpired;
    }

    /**
     * Set IsExpired.
     *
     * @param bool $isExpired
     *
     * @return PersonalData
     */
    public function setIsExpired(bool $isExpired): PersonalData
    {
        $this->isExpired = $isExpired;

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
    public function getIdMethod(): string
    {
        return $this->idMethod;
    }

    /**
     * Set IdMethod.
     *
     * @param string $idMethod
     *
     * @return PersonalData
     */
    public function setIdMethod(string $idMethod): PersonalData
    {
        $this->idMethod = $idMethod;

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
     * @param \DateInterval $retainFor
     *
     * @return PersonalData
     */
    public function setRetainFor(string $retainFor): PersonalData
    {
        $interval = new \DateInterval($retainFor);
        
        if (0 == $interval->format('s')) {
             throw new GdprException("RetainFor option period ". $retainFor . " is not a valid \DateTimeInterface duration string");
        }
        
        $this->retainFor = $interval;

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
    public function getFormat(): ?string
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

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedOn(): \DateTimeInterface
    {
        return $this->createdOn;
    }

    /**
     * Set CreatedOn.
     *
     * @param \DateTimeInterface $createdOn
     *
     * @return PersonalData
     */
    public function setCreatedOn(\DateTimeInterface $createdOn): PersonalData
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedOn(): \DateTimeInterface
    {
        return $this->updatedOn;
    }

    /**
     * Set UpdatedOn.
     *
     * @param \DateTimeInterface $updatedOn
     *
     * @return PersonalData
     */
    public function setUpdatedOn(\DateTimeInterface $updatedOn): PersonalData
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }



}
