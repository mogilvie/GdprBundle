<?php

namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * RegexReplace.
 *
 * @author      Mark Ogilvie <m.ogilvie@parolla.ie>
 */
class RegexReplace implements DisposalInterface
{

    private $regexPattern;
    private $replaceWith;

    /**
     * RegexReplace constructor.
     *
     * @param string $regexPattern PHP Regex String /
     * @param string $replaceWith
     */
    public function __construct(array $args)
    {

        $this->regexPattern = $args['pattern'];
        $this->replaceWith = "*";

        if(array_key_exists('replaceWith', $args)){
            $this->replaceWith = $args['replaceWith'];
        }

    }

    /**
     * @param string $parameter
     * @return mixed|null|string
     */
    public function dispose($parameter){

        if(strlen($this->replaceWith) > 1){
            $replaceFunction = function ($matches) {
                return $this->replaceWith;
            };
        } else {
            $replaceFunction = function ($matches) {
                $length = strlen($matches[0]);
                return str_repeat($this->replaceWith, $length);
            };
        }

        $result = preg_replace_callback(
            $this->regexPattern,
            $replaceFunction,
            $parameter
        );

        return $result;
    }
}
