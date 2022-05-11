<?php


namespace EmgSystems\Train4Sustain\Model\Passport;

/**
 * Model representing a ThematicField in CQS.
 */
class ThematicField
{
    /**
     * The name of the ThematicField area.
     *
     * @var string
     */
    public string $name;

    /**
     * The code of the ThematicField area.
     *
     * @var string
     */
    public string $code;

    /**
     * A list of Macro areas related to the Thematic Field.
     *
     * @var MacroArea[]
     */
    public array $macroAreas;
}
