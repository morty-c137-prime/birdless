<?php
/**
 * @author Xunnamius <me@xunn.io>
 */

namespace Application\View\Helper;

/**
 * Build nice page titles easily and logically.
 */
class TitleHelper
{
    private $separator = ' · ';
    private $titleParts = [];

    public function __invoke($path = null)
    {
        return $this->buildTitle();
    }

    /**
     * Returns a string representing the current state of the internal title
     * elements array.
     *
     * @return string
     */
    public function buildTitle()
    {
        return implode($this->getSeparator(), $this->titleParts);
    }

    /**
     * Append the arguments AS THEY ARE to the internal title elements array.
     *
     * @example
     * $titleHelper->append(4, 5, 6);
     * // title = 4 · 5 · 6
     */
    public function append(...$parts)
    {
        $this->titleParts = array_merge($this->titleParts, $parts);
        return $this;
    }

    /**
     * Prepends the arguments AS THEY ARE to the internal title elements array.
     *
     * @example
     * $titleHelper->append(4, 5, 6);
     * // title = 4 · 5 · 6
     * $titleHelper->prepend(1, 2, 3);
     * // title = 1 · 2 · 3 · 4 · 5 · 6
     */
    public function prepend(...$parts)
    {
        $this->titleParts = array_merge($parts, $this->titleParts);
        return $this;
    }

    /**
     * Returns the separator used to join the internal title elements array.
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets the separator used to join the internal title elements array.
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }
}
