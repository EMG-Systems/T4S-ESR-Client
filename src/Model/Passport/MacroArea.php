<?php


namespace EmgSystems\Train4Sustain\Model\Passport;

use EmgSystems\Train4Sustain\Model\Expertise;

/**
 * Model representing a MacroArea in CQS.
 */
class MacroArea
{
    /**
     * The name of the Macro area.
     *
     * @var string
     */
    public string $name;

    /**
     * The code of the Macro area.
     *
     * @var string
     */
    public string $code;

    /**
     * A list of Expertises related to the Macro area.
     *
     * @var Expertise[]
     */
    public array $expertise;
}
