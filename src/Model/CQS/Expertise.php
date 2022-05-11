<?php


namespace EmgSystems\Train4Sustain\Model\CQS;

/**
 * Model representing an Expertise in CQS.
 */
class Expertise
{
    /**
     * The id of the Expertise.
     *
     * @var int
     */
    public int $expertiseId;

    /**
     * The uuid of the Expertise.
     *
     * @var string
     */
    public string $uuid;

    /**
     * The code of the Expertise.
     *
     * @var string
     */
    public string $code;

    /**
     * The name of the Expertise.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the Expertise.
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * The id of the Macro Area the Expertise belongs to.
     *
     * @var int
     */
    public int $macroAreaId;

    /**
     * The code of the Macro Area the Expertise belongs to.
     *
     * @var string
     */
    public string $macroAreaCode;

    /**
     * The name of the Macro Area the Expertise belongs to.
     *
     * @var string
     */
    public string $macroAreaName;

    /**
     * The default rank of the Expertise in the list.
     *
     * @var int
     */
    public int $rank;
}
