<?php
/**
 * Created by PhpStorm.
 * User: Mark Ogilvie
 * Date: 21/10/17
 * Time: 18:22
 */

namespace SpecShaper\GdprBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("ALL")
 */
final class PersonalData extends Annotation
{
    const ID_METHOD_DIRECT = 'DIRECT';
    const ID_METHOD_INDIRECT = 'INDIRECT';

    const DISPOSE_BY_DELETION = 'DELETE';
    const DISPOSE_BY_AGGREGATE = 'AGGREGATE';
    const DISPOSE_BY_ANONYMITY = 'ANONYMITY';

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
     * Why is the data required to be stored?
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
}

