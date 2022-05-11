<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Model;

use DateTime;

/**
 * Model class representing a project.
 */
class Project
{
    /**
     * The project Id in the api database.
     *
     * @var int
     */
    public int $projectId;

    /**
     * The name of the project.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the project.
     *
     * @var string
     */
    public string $description;

    /**
     * The date when the project started or will be starting.
     *
     * @var \DateTime
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public DateTime $startDate;

    /**
     * The date when the project ended or will end.
     *
     * @var \DateTime
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public DateTime $endDate;

    /**
     * The name of the country that can be determined as the location of project, if any.
     *
     * @var string|null
     */
    public ?string $location;

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
     * The last time the entry was updated.
     *
     * @var \DateTime
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public DateTime $updated;
}
