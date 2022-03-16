<?php

namespace SpecShaper\GdprBundle\Model;

use DateInterval;
use DateTimeInterface;
use NumberFormatter;

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
    public const ID_METHOD_DIRECT = 'DIRECT';
    public const ID_METHOD_INDIRECT = 'INDIRECT';

    /**
     * What to do with the data when it becomes expired.
     */
    public const DISPOSE_BY_SET_NULL = 'SET_NULL';
    public const DISPOSE_BY_AGGREGATE = 'AGGREGATE';
    public const DISPOSE_BY_ANONYMISE = 'ANONYMISE';
    public const DISPOSE_BY_ANONYMISE_IP = 'ANONYMISE_IP';
    public const DISPOSE_BY_ANONYMISE_DATE = 'ANONYMISE_DATE';
    public const DISPOSE_BY_REGEX_REPLACE = 'REGEX_REPLACE';

    /**
     * How the data is received or transmitted between the provider and the data store.
     */
    public const TRANSFER_METHOD_HTTP = 'HTTP';
    public const TRANSFER_METHOD_HTTPS = 'HTTPS';
    public const TRANSFER_METHOD_FTP = 'FTP';
    public const TRANSFER_METHOD_FTPS = 'FTPS';
    public const TRANSFER_METHOD_PDF = 'PDF';
    public const TRANSFER_METHOD_ENCRYPTED_PDF = 'ENCRYPTED_PDF';
    public const TRANSFER_METHOD_EMAIL = 'EMAIL';
    public const TRANSFER_METHOD_POST = 'POST';
    public const TRANSFER_METHOD_REGISTERED_POST = 'REGISTERED_POST';
    public const TRANSFER_METHOD_PHONE = 'PHONE';

    /**
     * What format the data is provided in, and should be displayed as by default.
     */
    public const FORMAT_STRING = 'STRING';
    public const FORMAT_DATE = 'DATE';
    public const FORMAT_DATETIME = 'DATETIME';
    public const FORMAT_CURRENCY = 'CURRENCY';
    public const FORMAT_FLOAT = 'FLOAT';
    public const FORMAT_INTEGER = 'INTEGER';

    /**
     * Basis for collection of data.
     */
    /**
     * The processing is necessary to protect someone’s life.
     */
    public const BASIS_VITAL_INTEREST = 'VITAL_INTEREST';

    /**
     * The processing is necessary for you to perform a task in the public interest or for your official functions, and
     * the task or function has a clear basis in law.
     */
    public const BASIS_PUBLIC_INTEREST = 'PUBLIC_INTEREST';

    /**
     * The processing is necessary for a contract you have with the data subject, or
     * because they have asked you to take specific steps before entering into a contract.
     */
    public const BASIS_CONTRACT_NECESSITY = 'CONTRACT_NECESSTITY';

    /**
     * The processing is necessary for you to comply with the law (not including contractual obligations).
     */
    public const BASIS_LEGAL_REQUIREMENT = 'LEGAL_REQUIREMENT';

    /**
     * The data subject has given clear consent for you to process their personal data for a specific purpose.
     */
    public const BASIS_CONSENT = 'CONSENT';

    /**
     * The processing is necessary for your legitimate interests or the legitimate interests of a third party unless
     * there is a good reason to protect the individual’s personal data which overrides those
     * legitimate interests. (This cannot apply if you are a public authority processing data to perform your official tasks.).
     */
    public const BASIS_LEGITIMATE_INTEREST = 'LEGITIMATE_INTEREST';

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
     */
    public ?string $data = null;

    /**
     * The date time object that the data was created.
     *
     * Set in the subscriber via the GdprSubscriber via the UoW insert process.
     */
    public ?DateTimeInterface $createdOn;

    /**
     * The date time object when the data was last updated.
     *
     * Set in the GdprSubscriber via the UoW update process
     */
    public DateTimeInterface $updatedOn;

    /**
     * The format for the string data.
     *
     * Use the constants provided in this class.
     */
    public ?string $format = null;

    /**
     * The max length of the data.
     *
     * If a number then it is also considered the precision.
     */
    public ?int $length = null;

    /**
     * The number of digits after the decimal of a number.
     */
    public ?int $scale = null;

    /**
     * True if the data isExpired.
     *
     * When data becomes expired the original data is replaced with aggregated, nulled or anonymised data.
     *
     */
    public bool $isExpired;

    /**
     * True if the information is classified as sensitive personal information.
     *
     * Gender identity, health details, political affiliations.
     */
    public bool $isSensitive;

    /**
     * If the parameter should be encrypted.
     *
     */
    public bool $isEncrypted;

    /**
     * The method that the data could be used to identify someone.
     * Either:
     * - DIRECT, such as a name, email address
     * - INDIRECT, such as a street address, job title.
     *
     * Indirect information is where this particular piece of information might be combined with another
     * data set to identify an individual.
     */
    public ?string $idMethod;

    /**
     * What is the basis for collection of this data?
     *
     * Use constants:
     *   - The vital interest of the individual
     *   - The public interest
     *   - Contractual necessity
     *   - Compliance with legal obligations
     *   - Unambiguous consent of the individual
     *   - Legitimate interest of the data controller
     */
    public string $basisOfCollection;

    /**
     * Identifiable By.
     *
     * A note on how this particular piece of data might be used to identify an invididual.
     *
     * @var string The method by which the information might identify an individual
     */
    public string $identifiableBy;

    /**
     * Who typically provides the data.
     *
     * Was it user supplied, client supplied, generated?
     */
    public ?string $providedBy;

    /**
     * How did we receive this information?
     *
     * The method of transfer from the supplier to this database.
     */
    public array $methodOfReceipt;

    /**
     * How is the data protected in transit.
     */
    public array $receiptProtection;

    /**
     * Why do you need this data.
     *
     * @var string a text note explaining why this information is required
     */
    public ?string $purposeFor;

    /**
     * How long the information is to be retained in the database for.
     *
     * @var DateInterval A DateInterval string such as P6Y
     */
    public ?DateInterval $retainFor;

    /**
     * Disposal method.
     *
     * What happens at the end of the data's usefulness.
     * Dispose, aggregate or make anonymous
     */
    public ?string $disposeBy;

    /**
     * Disposal method arguments.
     */
    public array $disposeByArgs;

    /**
     * Keep Until.
     *
     * The date when the data will be kept until
     */
    public ?DateTimeInterface $keepUntil;

    /**
     * How do we return this data.
     */
    public array $methodOfReturn;

    /**
     * How is the date protected when returned, rendered or displayed?
     */
    public array $returnProtection;

    /**
     * Return a string of the data based on data format.
     */
    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString()
    {
        if (null === $this->getData()) {
            return '';
        }

        // If the data is expired then return the raw data.
        if (true === $this->isExpired) {
            return (string) $this->getData();
        }

        // If the data is current then format it according to type.
        switch ($this->getFormat()) {
            case PersonalData::FORMAT_DATE:
                $string = $this->getData()->format('d M Y');
                break;
            case PersonalData::FORMAT_DATETIME:
                $string = $this->getData()->format('d M Y h:m');
                break;
            case PersonalData::FORMAT_CURRENCY:
                $f = NumberFormatter::create('en', NumberFormatter::CURRENCY);
                $string = $f->formatCurrency($this->getData(), 'EUR');
                break;
            default:
                $string = (string) $this->getData();
        }

        return $string;
    }

    public function __construct()
    {
        $this->isSensitive = false;
        $this->isEncrypted = false;
        $this->isExpired = false;
        $this->disposeByArgs = [];

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->isExpired;
    }

    public function setIsExpired(bool $isExpired): PersonalData
    {
        $this->isExpired = $isExpired;

        return $this;
    }

    public function getIdMethod(): ?string
    {
        return $this->idMethod;
    }

    public function setIdMethod(?string $idMethod): PersonalData
    {
        $this->idMethod = $idMethod;

        return $this;
    }

    public function isSensitive(): bool
    {
        return $this->isSensitive;
    }

    public function setIsSensitive(bool $isSensitive): PersonalData
    {
        $this->isSensitive = $isSensitive;

        return $this;
    }

    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    public function setIsEncrypted(bool $isEncrypted): PersonalData
    {
        $this->isEncrypted = $isEncrypted;

        return $this;
    }

    public function getBasisOfCollection(): ?string
    {
        return $this->basisOfCollection;
    }

    public function setBasisOfCollection(string $basisOfCollection): PersonalData
    {
        $this->basisOfCollection = $basisOfCollection;

        return $this;
    }

    public function setIdentifiableBy(string $identifiableBy): PersonalData
    {
        $this->identifiableBy = $identifiableBy;

        return $this;
    }

    public function getProvidedBy(): ?string
    {
        return $this->providedBy;
    }

    public function setProvidedBy(string $providedBy): PersonalData
    {
        $this->providedBy = $providedBy;

        return $this;
    }

    public function getPurposeFor(): ?string
    {
        return $this->purposeFor;
    }

    public function setPurposeFor(string $purposeFor): PersonalData
    {
        $this->purposeFor = $purposeFor;

        return $this;
    }

    public function getRetainFor(): ?DateInterval
    {
        return $this->retainFor;
    }

    public function setRetainFor(string $retainFor): PersonalData
    {
        $interval = new DateInterval($retainFor);

        $this->retainFor = $interval;

        return $this;
    }

    public function getDisposeBy(): ?string
    {
        return $this->disposeBy;
    }

    public function setDisposeBy(string $disposeBy): PersonalData
    {
        $this->disposeBy = $disposeBy;

        return $this;
    }

    public function getKeepUntil(): ?DateTimeInterface
    {
        return $this->keepUntil;
    }

    public function setKeepUntil(?DateTimeInterface $keepUntil): PersonalData
    {
        $this->keepUntil = $keepUntil;

        return $this;
    }

    public function getReturnFormat(): ?string
    {
        // return $this->returnFormat;

        return null;
    }

    /**
     * Set ReturnFormat.
     *
     * @param mixed $returnFormat
     *
     * @return PersonalData
     */
    public function setReturnFormat(?string $returnFormat)
    {
        // $this->returnFormat = $returnFormat;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): PersonalData
    {
        $this->data = $data;

        return $this;
    }

    public function getMethodOfReceipt(): array
    {
        return $this->methodOfReceipt;
    }

    public function setMethodOfReceipt(array $methodOfReceipt): PersonalData
    {
        $this->methodOfReceipt = $methodOfReceipt;

        return $this;
    }

    public function getReceiptProtection(): ?array
    {
        return $this->receiptProtection;
    }

    public function setReceiptProtection(array $receiptProtection): PersonalData
    {
        $this->receiptProtection = $receiptProtection;

        return $this;
    }

    public function getMethodOfReturn(): ?array
    {
        return $this->methodOfReturn;
    }

    public function setMethodOfReturn(array $methodOfReturn): PersonalData
    {
        $this->methodOfReturn = $methodOfReturn;

        return $this;
    }

    public function getReturnProtection(): ?array
    {
        return $this->returnProtection;
    }

    public function setReturnProtection(array $returnProtection): PersonalData
    {
        $this->returnProtection = $returnProtection;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): PersonalData
    {
        $this->format = $format;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): ?PersonalData
    {
        $this->length = $length;

        return $this;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(int $scale): PersonalData
    {
        $this->scale = $scale;

        return $this;
    }

    public function getCreatedOn(): ?DateTimeInterface
    {
        return $this->createdOn;
    }

    public function setCreatedOn(DateTimeInterface $createdOn): PersonalData
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getUpdatedOn(): ?DateTimeInterface
    {
        return $this->updatedOn;
    }

    public function setUpdatedOn(DateTimeInterface $updatedOn): PersonalData
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    public function getDisposeByArgs(): ?array
    {
        return $this->disposeByArgs;
    }

    public function setDisposeByArgs(array $disposeByArgs): PersonalData
    {
        $this->disposeByArgs = $disposeByArgs;

        return $this;
    }
}
