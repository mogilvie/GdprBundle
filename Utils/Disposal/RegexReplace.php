<?php

namespace SpecShaper\GdprBundle\Utils\Disposal;

/**
 * RegexReplace.
 *
 * @author      Mark Ogilvie <m.ogilvie@parolla.ie>
 */
class RegexReplace implements DisposalInterface
{
    private string $regexPattern;
    private string $replaceWith;

    public function __construct(array $args)
    {
        $this->regexPattern = $args['pattern'];
        $this->replaceWith = '*';

        if (array_key_exists('replaceWith', $args)) {
            $this->replaceWith = $args['replaceWith'];
        }
    }

    public function dispose(mixed $parameter): mixed
    {
        if (strlen($this->replaceWith) > 1) {
            $replaceFunction = function ($matches) {
                return $this->replaceWith;
            };
        } else {
            $replaceFunction = function ($matches) {
                $length = strlen($matches[0]);

                return str_repeat($this->replaceWith, $length);
            };
        }

        return preg_replace_callback(
            $this->regexPattern,
            $replaceFunction,
            $parameter
        );
    }
}
