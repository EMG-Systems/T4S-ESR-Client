<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Model;

/**
 * Model describing a profession
 */
class Profession
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
}
