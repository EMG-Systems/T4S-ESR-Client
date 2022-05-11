<?php


namespace EmgSystems\Train4Sustain\Model\Passport;

/**
 * Model representing a Dimension in CQS.
 */
class Dimension
{
    /**
     * The name of the Dimension.
     *
     * @var string
     */
    public string $name;

    /**
     * The list of Thematic Fields related to the Dimension.
     *
     * @var ThematicField[]
     */
    public array $thematicFields;
}
