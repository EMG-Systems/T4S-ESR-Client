<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain;

use EmgSystems\Train4Sustain\Model\Course;
use EmgSystems\Train4Sustain\Model\CQS\Dimension;
use EmgSystems\Train4Sustain\Model\CQS\Expertise;
use EmgSystems\Train4Sustain\Model\CQS\ThematicField;
use EmgSystems\Train4Sustain\Model\Expert;
use EmgSystems\Train4Sustain\Model\Organisation;
use EmgSystems\Train4Sustain\Model\Passport;
use EmgSystems\Train4Sustain\Model\Project;
use EmgSystems\Train4Sustain\Model\Scheme;
use EmgSystems\Train4Sustain\Model\SchemeCompare;
use EmgSystems\Train4Sustain\Request\CurlException;
use EmgSystems\Train4Sustain\Request\CurlInterface;
use Exception;
use JsonException;
use JsonMapper;
use JsonMapper_Exception;

/**
 * Client of the T4S ESR public API
 *
 * @package emg-systems/t4s-api-client
 */
class Client
{
    protected CurlInterface $curl;

    /** @var JsonMapper Service used for mapping JSON responses to objects */
    protected JsonMapper $mapper;

    /** @var string HTTP protocol used for accessing the API endpoints */
    private string $protocol;

    /** @var string Base URL use for creating absolute endpoint URLs */
    private string $baseUrl;

    /**
     * API client constructor.
     *
     * @param CurlInterface $curl
     * @param string|null   $protocol
     * @param string|null   $baseUrl
     */
    public function __construct(CurlInterface $curl, ?string $protocol = null, ?string $baseUrl = null)
    {
        $config = json_decode(file_get_contents("config.json"));
        $this->protocol = $protocol ?? $config->API->protocol;
        $this->baseUrl = $baseUrl ?? $config->API->baseUrl;
        $this->curl = $curl;
        $this->initCurl();
    }

    /**
     * Checks whether the cURL extension is available.
     *
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('curl');
    }

    /**
     * Returns a filtered expert list based on the given filter criteria list.
     *
     * @param string|null $keyword            The keyword to search for in textual fields.
     * @param array|null  $thematicFieldIds   The thematic field ids to filter on.
     * @param array|null  $expertiseIds       A list of expertise Ids
     * @param int|null    $expertiseLevel     The minimum required level of competence among expertises
     * @param int         $take               The amount of experts to retrieve.
     * @param int         $skip               The amount of experts to skip from retrieval.
     * @param array       $sort               A list of associative arrays containing 'field' and 'dir' keys to manage
     *                                        sorting.
     *
     * @return Expert[]
     *
     * @throws Exception
     */
    public function getExperts(
        ?string $keyword = null,
        ?array $thematicFieldIds = null,
        ?array $expertiseIds = null,
        ?int $expertiseLevel = null,
        int $take = 10,
        int $skip = 0,
        array $sort = [['field' => 'created', 'dir' => 'desc']]
    ): array {
        $filter = [
            'keyword'        => $keyword,
            'thematicFields' => $thematicFieldIds,
            'expertise'      => $expertiseIds,
            'expertiseLevel' => $expertiseLevel,
            'take'           => $take,
            'skip'           => $skip,
            'sort'           => $sort
        ];
        $response = $this->execute('GET', 'expert/find.json', $filter);
        $this->getMapper()->undefinedPropertyHandler = function ($object, $propertyName, $jsonValue) {
            if ($propertyName == 'profileId') {
                $object->expertId = $jsonValue;
            }
        };
        return $this->getMapper()->mapArray($response, [], Expert::class);
    }

    /**
     * Returns one expert based on expertId.
     *
     * @param int $expertId
     *
     * @return Expert
     *
     * @throws Exception
     */
    public function getExpert(int $expertId): Expert
    {
        $response = $this->execute('GET', 'expert/' . $expertId . '.json');
        $this->getMapper()->undefinedPropertyHandler = function ($object, $propertyName, $jsonValue) {
            if ($propertyName == 'profileId') {
                $object->expertId = $jsonValue;
            }
        };
        return $this->getMapper()->map($response, new Expert());
    }

    /**
     * Returns the passport related to an expert based on expertId.
     *
     * @param int $expertId
     *
     * @return Passport
     *
     * @throws CurlException
     * @throws JsonException
     */
    public function getExpertPassport(int $expertId): Passport
    {
        $response = $this->execute('GET', 'expert/' . $expertId . '/passport.json');
        return Passport::buildFromObject($response);
    }

    /**
     * Returns a filtered project list based on the given filter criteria list.
     *
     * @param string|null $keyword            The keyword to search for in textual fields.
     * @param array|null  $thematicFieldIds   The thematic field ids to filter on.
     * @param array|null  $expertiseIds       A list of expertise Ids
     * @param int         $take               The amount of projects to retrieve.
     * @param int         $skip               The amount of projects to skip from retrieval.
     * @param array       $sort               A list of associative arrays containing 'field' and 'dir' keys to manage
     *                                        sorting.
     *
     * @return Project[]
     *
     * @throws Exception
     */
    public function getProjects(
        ?string $keyword = null,
        ?array $thematicFieldIds = null,
        ?array $expertiseIds = null,
        int $take = 10,
        int $skip = 0,
        array $sort = [['field' => 'created', 'dir' => 'desc']]
    ): array {
        $filter = [
            'keyword'        => $keyword,
            'thematicFields' => $thematicFieldIds,
            'expertise'      => $expertiseIds,
            'take'           => $take,
            'skip'           => $skip,
            'sort'           => $sort
        ];
        $response = $this->execute('GET', 'projects/find.json', $filter);
        return $this->getMapper()->mapArray($response, [], Project::class);
    }

    /**
     * Returns one project based on projectId.
     *
     * @param int $projectId
     *
     * @return Project
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     * @throws Exception
     */
    public function getProject(int $projectId): Project
    {
        $response = $this->execute('GET', 'projects/' . $projectId . '.json');
        if (!property_exists($response, 'project') || is_null($response->project)) {
            throw new Exception('Project not found!', 404);
        }
        return $this->getMapper()->map($response->project, new Project());
    }

    /**
     * Returns a filtered scheme list based on the given filter criteria list.
     *
     * @param string|null $keyword            The keyword to search for in textual fields.
     * @param array|null  $thematicFieldIds   The thematic field ids to filter on.
     * @param array|null  $expertiseIds       A list of expertise Ids
     * @param int         $take               The amount of schemes to retrieve.
     * @param int         $skip               The amount of schemes to skip from retrieval.
     * @param array       $sort               A list of associative arrays containing 'field' and 'dir' keys to manage
     *                                        sorting.
     *
     * @return Scheme[]
     *
     * @throws Exception
     */
    public function getSchemes(
        ?string $keyword = null,
        ?array $thematicFieldIds = null,
        ?array $expertiseIds = null,
        int $take = 10,
        int $skip = 0,
        array $sort = [['field' => 'created', 'dir' => 'desc']]
    ): array {
        $filter = [
            'keyword'        => $keyword,
            'thematicFields' => $thematicFieldIds,
            'expertise'      => $expertiseIds,
            'take'           => $take,
            'skip'           => $skip,
            'sort'           => $sort
        ];
        $response = $this->execute('GET', 'schemes/find.json', $filter);
        return $this->getMapper()->mapArray($response, [], Scheme::class);
    }

    /**
     * Returns one scheme based on schemeId.
     *
     * @param int $schemeId
     *
     * @return Scheme
     *
     * @throws Exception
     */
    public function getScheme(int $schemeId): Scheme
    {
        $response = $this->execute('GET', 'schemes/' . $schemeId . '.json');
        if (!property_exists($response, 'scheme') || is_null($response->scheme)) {
            throw new Exception('Scheme not found!', 404);
        }
        return $this->getMapper()->map($response->scheme, new Scheme());
    }

    /**
     * Compares two schemes based on schemeIds. Returns the scheme entities, and the result of comparison.
     *
     * @param int $schemeId
     * @param int $opponentSchemeId
     *
     * @return SchemeCompare
     *
     * @throws Exception
     */
    public function compareSchemes(int $schemeId, int $opponentSchemeId): SchemeCompare
    {
        $response = $this->execute('GET', 'schemes/compare/' . $schemeId . '/' . $opponentSchemeId . '.json');
        if (!property_exists($response, 'scheme') || is_null($response->scheme) || !property_exists($response,
                'opponentScheme') || is_null($response->opponentScheme)) {
            throw new Exception('Scheme not found!', 404);
        }
        return $this->getMapper()->map($response, new SchemeCompare());
    }

    /**
     * Returns a filtered organisation list based on the given filter criteria list.
     *
     * @param string|null $keyword            The keyword to search for in textual fields.
     * @param array|null  $thematicFieldIds   The thematic field ids to filter on.
     * @param array|null  $expertiseIds       A list of expertise Ids
     * @param int|null    $expertiseLevel     The minimum required level of competence among expertises
     * @param int         $take               The amount of organisations to retrieve.
     * @param int         $skip               The amount of organisations to skip from retrieval.
     * @param array       $sort               A list of associative arrays containing 'field' and 'dir' keys to manage
     *                                        sorting.
     *
     * @return Organisation[]
     *
     * @throws Exception
     */
    public function getOrganisations(
        ?string $keyword = null,
        ?array $thematicFieldIds = null,
        ?array $expertiseIds = null,
        ?int $expertiseLevel = null,
        int $take = 50,
        int $skip = 0,
        array $sort = [['field' => 'name', 'dir' => 'asc']]
    ): array {
        $filter = [
            'keyword'        => $keyword,
            'thematicFields' => $thematicFieldIds,
            'expertise'      => $expertiseIds,
            'expertiseLevel' => $expertiseLevel,
            'take'           => $take,
            'skip'           => $skip,
            'sort'           => $sort
        ];
        $response = $this->execute('GET', 'organisation/find.json', $filter);
        return $this->getMapper()->mapArray($response, [], Organisation::class);
    }

    /**
     * Returns one organisation based on organisationId.
     *
     * @param int $organisationId
     *
     * @return Organisation
     *
     * @throws Exception
     */
    public function getOrganisation(int $organisationId): Organisation
    {
        $response = $this->execute('GET', 'organisation/' . $organisationId . '.json');
        return $this->getMapper()->map($response, new Organisation());
    }

    /**
     * Retrieves the complete list of Dimensions in the CQS.
     *
     * @return array
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function getDimensions(): array
    {
        $response = $this->execute('GET', 'competence-quality-standard/home/get-dimensions.json');
        return $this->getMapper()->mapArray($response, [], Dimension::class);
    }

    /**
     * Retrieves a list of Thematic Fields. Either a complete list, or filtered by Dimension in case dimensionId
     * parameter is present.
     *
     * @param int|null $dimensionId
     *
     * @return array
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function getThematicFields(?int $dimensionId = null): array
    {
        $uriBase = 'competence-quality-standard/home/get-thematic-fields';
        $uriBase .= !is_null($dimensionId) ? '/' . $dimensionId : '';
        $response = $this->execute('GET', $uriBase . '.json');
        return $this->getMapper()->mapArray($response, [], ThematicField::class);
    }

    /**
     * Retrieves a list of Expertises. Either a complete list, or filtered by Thematic Field in case thematicFieldId
     * parameter is present.
     *
     * @param int|null $thematicFieldId
     *
     * @return array
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function getExpertises(?int $thematicFieldId = null): array
    {
        $uriBase = 'competence-quality-standard/home/get-expertises';
        $uriBase .= !is_null($thematicFieldId) ? '/' . $thematicFieldId : '';
        $response = $this->execute('GET', $uriBase . '.json');
        return $this->getMapper()->mapArray($response, [], Expertise::class);
    }

    /**
     * Returns a filtered Course list based on the given filter criteria list.
     *
     * @param string|null $keyword            The keyword to search for in textual fields.
     * @param array|null  $thematicFieldIds   The thematic field ids to filter on.
     * @param array|null  $expertiseIds       A list of expertise Ids
     * @param int         $take               The amount of experts to retrieve.
     * @param int         $skip               The amount of experts to skip from retrieval.
     * @param array       $sort               A list of associative arrays containing 'field' and 'dir' keys to manage
     *                                        sorting.
     *
     * @return Expert[]
     *
     * @throws Exception
     */
    public function getCourses(
        ?string $keyword = null,
        ?array $thematicFieldIds = null,
        ?array $expertiseIds = null,
        int $take = 10,
        int $skip = 0,
        array $sort = [['field' => 'created', 'dir' => 'desc']]
    ): array {
        $filter = [
            'keyword'        => $keyword,
            'thematicFields' => $thematicFieldIds,
            'expertise'      => $expertiseIds,
            'take'           => $take,
            'skip'           => $skip,
            'sort'           => $sort
        ];
        $response = $this->execute('GET', 'competence-quality-standard/e-inventory/course-search.json', $filter);
        return $this->getMapper()->mapArray($response, [], Course::class);
    }

    /**
     * Returns one Course based on courseId.
     *
     * @param int $courseId
     *
     * @return Course
     *
     * @throws Exception
     */
    public function getCourse(int $courseId): Course
    {
        $response = $this->execute('GET', 'e-inventory/course/' . $courseId . '.json');
        $this->getMapper()->undefinedPropertyHandler = function ($object, $propertyName, $jsonValue) {
            if ($propertyName == 'learningOutcomes') {
                $object->competences = Passport::buildFromObject($jsonValue);
            }
        };
        return $this->getMapper()->map($response, new Course());
    }

    /**
     * Translates relative URI to absolute URL
     *
     * @param $uri string Endpoint URI
     *
     * @return string Absolute URL
     */
    protected function getUrl(string $uri, ?array $filter): string
    {
        $url = "$this->protocol://$this->baseUrl/$uri";
        if (!empty($filter)) {
            $url .= '?' . http_build_query($filter);
        }
        return $url;
    }

    /**
     * Executes an HTTP request and returns the response as an array
     *
     * @param string     $httpMethod
     * @param string     $uri
     * @param array|null $filter
     *
     * @return object|object[]
     *
     * @throws CurlException
     * @throws JsonException
     * @throws Exception
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    protected function execute(string $httpMethod, string $uri, ?array $filter = null)
    {
        $this->curl->setOptions([
            CURLOPT_URL           => $this->getUrl($uri, $filter),
            CURLOPT_POST          => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($httpMethod)
        ]);
        $this->curl->exec();
        $header = strtolower($this->curl->getHeader());
        if (strpos($header, '200 ok') === false) {
            if (strpos($header, '404 not found') !== false) {
                throw new Exception('Not found!', 404);
            } else {
                throw new Exception('Unknown error!', 500);
            }

        }
        $result = json_decode($this->curl->getBody());
        if (is_null($result)) {
            throw new JsonException('Invalid response returned!');
        }
        return $result;
    }

    /**
     * Initializes curl resource.
     */
    protected function initCurl()
    {
        $this->curl->init();
        $this->curl->setOptions([
            CURLOPT_HTTPHEADER      => [
                'Accept: application/json'
            ],
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_AUTOREFERER     => true,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_MAXREDIRS       => 20,
            CURLOPT_HEADER          => true,
            CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT       => 'T4S ESR API Client (Curl)',
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30
        ]);
    }

    /**
     * @return JsonMapper
     */
    protected function getMapper(): JsonMapper
    {
        if (!isset($this->mapper) || !($this->mapper instanceof JsonMapper)) {
            $this->mapper = new JsonMapper();
        }
        return $this->mapper;
    }
}
