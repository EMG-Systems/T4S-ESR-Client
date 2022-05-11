<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Model;

use DateTime;

/**
 * Model representing a Qualification Scheme.
 */
class Scheme
{
    /**
     * The identifier of the qualification scheme in the api database.
     *
     * @var int
     */
    public int $qualificationSchemeId;

    /**
     * The name of the qualification scheme.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the qualification scheme.
     *
     * @var string
     */
    public string $description;

    /**
     * The url for more info on the qualification scheme.
     *
     * @var string
     */
    public string $url;

    /**
     *  The list of expertises the expert is owning.
     *
     * @var Expertise[]|null
     */
    public ?array $expertise;

    /**
     * The date and time when the Scheme was imported into the database.
     *
     * @var \DateTime
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public DateTime $created;

    /**
     * The date and time when the Scheme was last modified in the database.
     *
     * @var \DateTime
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public DateTime $updated;
}
