<?php


namespace EmgSystems\Train4Sustain\Model\CQS;

/**
 * Model representing a Dimension in CQS.
 */
class Dimension
{
    /**
     * The id of the Dimension.
     *
     * @var int
     */
    public int $dimensionId;

    /**
     * The uuid of the Dimension.
     *
     * @var string
     */
    public string $uuid;

    /**
     * The name of the Dimension.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the Dimension.
     *
     * @var string
     */
    public string $description;

    /**
     * The thumbnail of the Dimension in a relative url.
     *
     * @var string
     */
    public string $thumbnail;

    /**
     * The default rank of the Dimension in the list.
     *
     * @var int
     */
    public int $rank;
}
