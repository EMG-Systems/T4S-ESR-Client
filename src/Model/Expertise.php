<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Model;

/**
 * Model describing an expertise
 */
class Expertise
{
    /**
     * The codename of the profession in CQS.
     *
     * @var string
     */
    public string $code;

    /**
     * The name of the profession in CQS.
     *
     * @var string
     */
    public string $name;

    /**
     * The level of expertise the expert has achieved.
     *
     * @var int
     */
    public int $score;

    /**
     * The dimension the expertise belongs to.
     *
     * @var string
     */
    public string $dimension;
}
