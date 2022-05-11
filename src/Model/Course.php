<?php

namespace EmgSystems\Train4Sustain\Model;

use DateTime;

/**
 * Model describing a Course.
 */
class Course
{
    /**
     * The id of the Course.
     *
     * @var int
     */
    public int $courseId;

    /**
     * The uuid of the Course.
     *
     * @var string
     */
    public string $uuid;

    /**
     * The code of the Course.
     *
     * @var string
     */
    public string $code;

    /**
     * The name of the Course.
     *
     * @var string
     */
    public string $name;

    /**
     * The description of the Course.
     *
     * @var string
     */
    public string $description;

    /**
     * The status of the Course.
     *
     * @var string
     */
    public string $status;

    /**
     * The duration of the Course in human-readable format.
     *
     * @var string
     */
    public string $duration;

    /**
     * The country of the Course.
     *
     * @var string|null
     */
    public ?string $country;

    /**
     * The id of the QS the Course belongs to.
     *
     * @var int
     */
    public int $qualificationSchemeId;

    /**
     * The name of the QS the Course belongs to.
     *
     * @var string
     */
    public string $qualificationScheme;

    /**
     * The denomination of the professional qualification aquired by Course.
     *
     * @var string
     */
    public string $professionalQualificationTitle;

    /**
     * The target groups of the Course.
     *
     * @var string
     */
    public string $targetGroups;

    /**
     * The didactic method of the Course.
     *
     * @var string
     */
    public string $didacticMethod;

    /**
     * The prerequisites of the Course.
     *
     * @var string
     */
    public string $prerequisites;

    /**
     * The qualification renewal of the Course.
     *
     * @var string
     */
    public string $qualificationRenewal;

    /**
     * The qualification register of the Course.
     *
     * @var string
     */
    public string $qualificationRegister;

    /**
     * The reference legislation of the Course.
     *
     * @var string
     */
    public string $referenceLegislation;

    /**
     * The name of the providing institution of the Course.
     *
     * @var string
     */
    public string $providingInstitution;

    /**
     * The name of the sponsoring institution of the Course.
     *
     * @var string
     */
    public string $sponsoringInstitution;

    /**
     * The iso string of the language of this record.
     *
     * @var string
     */
    public string $alpha3;

    /**
     * The id of the Expert that created the Course.
     *
     * @var int|null
     */
    public ?int $createdBy;

    /**
     * The name of the Expert that created the Course.
     *
     * @var string|null
     */
    public ?string $createdByName;

    /**
     * The date of the Course creation.
     *
     * @var \DateTime
     */
    public DateTime $created;

    /**
     * The date of the last update of the Course.
     *
     * @var \DateTime
     */
    public DateTime $updated;

    /**
     * @var Passport|null
     */
    public ?Passport $competences = null;
}
