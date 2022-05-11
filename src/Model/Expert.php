<?php /** @noinspection PhpIllegalPsrClassPathInspection */

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

namespace EmgSystems\Train4Sustain\Model;

use DateTime;

/**
 * Model describing an expert.
 */
class Expert
{
    /**
     * The id of the expert.
     *
     * @var int
     */
    public int $expertId;

    /**
     * The name of the expert.
     *
     * @var string
     */
    public string $name;

    /**
     * The short profile description of the expert.
     *
     * @var string
     */
    public string $shortProfile;

    /**
     * The country of the expert.
     *
     * @var string|null
     */
    public ?string $country;

    /**
     * The list of professions owned by the expert.
     *
     * @var Profession[]|null
     */
    public ?array $profession;

    /**
     *  The list of expertises the expert is owning.
     *
     * @var Expertise[]|null
     */
    public ?array $expertise;

    /**
     * The iso representation of the language the data was retrieved in.
     *
     * @var string
     */
    public string $alpha3;

    /**
     * The date and time the expert was created in the database.ú
     *
     * @var \DateTime
     */
    public DateTime $created;
}
