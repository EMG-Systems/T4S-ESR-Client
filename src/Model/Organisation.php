<?php

namespace EmgSystems\Train4Sustain\Model;

use DateTime;

/**
 * Model describing an organisation.
 */
class Organisation
{
    /**
     * The id of the organisation.
     *
     * @var int
     */
    public int $organisationId;

    /**
     * The name of the organisation.
     *
     * @var string
     */
    public string $name;

    /**
     * The vat number of the organisation.
     *
     * @var string
     */
    public string $vatNumber;

    /**
     * The description of the organisation.
     *
     * @var string
     */
    public string $description;

    /**
     * The url of the organisation's website.
     *
     * @var string|null
     */
    public ?string $url;

    /**
     * The logo of the organisation.
     *
     * @var string|null
     */
    public ?string $logo;

    /**
     * The list of professions owned by the organisation.
     *
     * @var Profession[]|null
     */
    public ?array $profession;

    /**
     * The list of expertises the organisation is owning.
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
     * The id of the expert that is the owner of the organisation.
     *
     * @var int
     */
    public int $createdBy;

    /**
     * The date and time the organisation was created in the database.
     *
     * @var DateTime|null
     */
    public ?DateTime $updated;
}
