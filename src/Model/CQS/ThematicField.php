<?php


namespace EmgSystems\Train4Sustain\Model\CQS;

/**
 * Model representing a ThematicField in CQS.
 */
class ThematicField
{
    /**
     * The id of the ThematicField.
     *
     * @var int
     */
    public int $thematicFieldId;

    /**
     * The uuid of the ThematicField.
     *
     * @var string
     */
    public string $uuid;

    /**
     * The code of the ThematicField.
     *
     * @var string
     */
    public string $code;

    /**
     * The name of the ThematicField.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the ThematicField.
     *
     * @var string
     */
    public string $description;

    /**
     * The id of the Dimension the ThematicField belongs to.
     *
     * @var int
     */
    public int $dimensionId;

    /**
     * The name of the Dimension the ThematicField belongs to.
     *
     * @var string
     */
    public string $dimensionName;

    /**
     * The default rank of the ThematicField in the list.
     *
     * @var int
     */
    public int $rank;
}
