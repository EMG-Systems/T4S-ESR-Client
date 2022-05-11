<?php /** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

namespace EmgSystems\Train4Sustain\test;

use DateTime;
use EmgSystems\Train4Sustain\Client;
use EmgSystems\Train4Sustain\Model\Course;
use EmgSystems\Train4Sustain\Model\CQS\Dimension;
use EmgSystems\Train4Sustain\Model\CQS\Expertise as CqsExpertise;
use EmgSystems\Train4Sustain\Model\CQS\ThematicField;
use EmgSystems\Train4Sustain\Model\Expert;
use EmgSystems\Train4Sustain\Model\Expertise;
use EmgSystems\Train4Sustain\Model\Organisation;
use EmgSystems\Train4Sustain\Model\Passport;
use EmgSystems\Train4Sustain\Model\Profession;
use EmgSystems\Train4Sustain\Model\Project;
use EmgSystems\Train4Sustain\Model\Scheme;
use EmgSystems\Train4Sustain\Model\SchemeCompare;
use EmgSystems\Train4Sustain\Request\CurlException;
use EmgSystems\Train4Sustain\Request\CurlInterface;
use Exception;
use JsonException;
use JsonMapper_Exception;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the API client
 *
 * @package emg-systems/t4s-api-client
 * @covers  \EmgSystems\Train4Sustain\Client
 * @uses \EmgSystems\Train4Sustain\Model\Passport
 */
final class ClientTest extends TestCase
{
    protected Client $client;


    protected CurlInterface $curlMock;

    protected array $experts;
    protected array $projects;
    protected array $schemes;
    protected object $compareResult;
    protected object $passportResult;
    protected array $organisations;
    protected array $dimensions;
    protected array $thematicFields;
    protected array $expertises;
    protected array $courses;

    /**
     * Setting up test resources
     */
    public function setUp(): void
    {
        $this->curlMock = $this->getMockForAbstractClass(CurlInterface::class);
    }

    public function testInstantiation()
    {
        $this->curlMock->expects(self::once())->method('init');
        $this->curlMock->expects(self::once())->method('setOptions')
            ->with($this->equalTo([
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
            ]));
        $this->setUpClient();
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testIsAvailableTrue()
    {
        $this->assertTrue(Client::isAvailable());
    }

    /**
     * @throws Exception
     */
    public function testGetExpertsAllDefault()
    {
        $this->setUpExperts();
        $filter = [
            'keyword'        => null,
            'thematicFields' => null,
            'expertise'      => null,
            'expertiseLevel' => null,
            'take'           => 10,
            'skip'           => 0,
            'sort'           => [
                [
                    'field' => 'created',
                    'dir'   => 'desc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/find.json', $filter)->willReturn($this->experts);
        $result = $client->getExperts();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Expert::class, $result);
        $this->assertEquals(21, $result[0]->expertId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->shortProfile);
        $this->assertNull($result[0]->country);
        $this->assertEquals('Hungary', $result[2]->country);

        $this->assertNull($result[0]->profession);
        $this->assertContainsOnlyInstancesOf(Profession::class, $result[1]->profession);
        $this->assertCount(5, $result[1]->profession);
        $this->assertNotEmpty($result[1]->profession[0]->code);
        $this->assertNotEmpty($result[1]->profession[0]->name);
        $this->assertIsString($result[1]->profession[0]->code);
        $this->assertIsString($result[1]->profession[0]->name);

        $this->assertNull($result[0]->expertise);
        $this->assertContainsOnlyInstancesOf(Expertise::class, $result[1]->expertise);
        $this->assertCount(1, $result[1]->expertise);
        $this->assertNotEmpty($result[1]->expertise[0]->code);
        $this->assertNotEmpty($result[1]->expertise[0]->name);
        $this->assertNotEmpty($result[1]->expertise[0]->dimension);
        $this->assertNotEmpty($result[1]->expertise[0]->score);
        $this->assertIsString($result[1]->expertise[0]->code);
        $this->assertIsString($result[1]->expertise[0]->name);
        $this->assertIsString($result[1]->expertise[0]->dimension);
        $this->assertIsInt($result[1]->expertise[0]->score);

        $this->assertInstanceOf(DateTime::class, $result[0]->created);
        $this->assertIsString($result[0]->alpha3);
    }

    /**
     * @throws Exception
     */
    public function testGetExpertsAllSet()
    {
        $this->setUpExperts();
        $filter = [
            'keyword'        => 'test',
            'thematicFields' => [1],
            'expertise'      => [2],
            'expertiseLevel' => 3,
            'take'           => 4,
            'skip'           => 1,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/find.json', $filter)->willReturn($this->experts);
        $client->getExperts('test', [1], [2], 3, 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetExpert()
    {
        $this->setUpExperts();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/21.json')->willReturn($this->experts[0]);
        $result = $client->getExpert(21);
        $this->assertInstanceOf(Expert::class, $result);
        $this->assertEquals(21, $result->expertId);
    }

    public function testGetExpertNotFound()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/121212.json')->willThrowException(new Exception('Not found!',
            404));
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not found!');
        $client->getExpert(121212);
    }

    /**
     * @throws Exception
     * @uses \EmgSystems\Train4Sustain\Model\Passport
     */
    public function testGetExpertPassport()
    {
        $this->setUpPassportResult();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/21/passport.json')->willReturn($this->passportResult);
        $result = $client->getExpertPassport(21);
        $this->assertInstanceOf(Passport::class, $result);
    }

    /**
     * @throws CurlException
     * @throws JsonException
     */
    public function testGetExpertPassportNotFound()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'expert/121212/passport.json')->willThrowException(new Exception('Not found!',
            404));
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not found!');
        $client->getExpertPassport(121212);
    }


    /**
     * @throws Exception
     */
    public function testGetOrganisationAllDefault()
    {
        $this->setUpOrganisations();
        $filter = [
            'keyword'        => null,
            'thematicFields' => null,
            'expertise'      => null,
            'expertiseLevel' => null,
            'take'           => 50,
            'skip'           => 0,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'organisation/find.json', $filter)->willReturn($this->organisations);
        $result = $client->getOrganisations();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Organisation::class, $result);
        $this->assertEquals(42, $result[0]->organisationId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->vatNumber);
        $this->assertIsString($result[0]->description);
        $this->assertIsString($result[0]->url);
        $this->assertIsString($result[0]->logo);
        $this->assertIsInt($result[0]->createdBy);


        $this->assertContainsOnlyInstancesOf(Profession::class, $result[0]->profession);
        $this->assertCount(4, $result[0]->profession);
        $this->assertNotEmpty($result[0]->profession[0]->code);
        $this->assertNotEmpty($result[0]->profession[0]->name);
        $this->assertIsString($result[0]->profession[0]->code);
        $this->assertIsString($result[0]->profession[0]->name);

        $this->assertContainsOnlyInstancesOf(Expertise::class, $result[0]->expertise);
        $this->assertCount(3, $result[0]->expertise);
        $this->assertNotEmpty($result[0]->expertise[0]->code);
        $this->assertNotEmpty($result[0]->expertise[0]->name);
        $this->assertNotEmpty($result[0]->expertise[0]->dimension);
        $this->assertNotEmpty($result[0]->expertise[0]->score);
        $this->assertIsString($result[0]->expertise[0]->code);
        $this->assertIsString($result[0]->expertise[0]->name);
        $this->assertIsString($result[0]->expertise[0]->dimension);
        $this->assertIsInt($result[0]->expertise[0]->score);
        $this->assertIsString($result[0]->alpha3);
    }

    /**
     * @throws Exception
     */
    public function testGetOrganisationsAllSet()
    {
        $this->setUpOrganisations();
        $filter = [
            'keyword'        => 'test',
            'thematicFields' => [1],
            'expertise'      => [2],
            'expertiseLevel' => 3,
            'take'           => 4,
            'skip'           => 1,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'organisation/find.json', $filter)->willReturn($this->organisations);
        $client->getOrganisations('test', [1], [2], 3, 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetOrganisation()
    {
        $this->setUpOrganisations();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'organisation/42.json')->willReturn($this->organisations[0]);
        $result = $client->getOrganisation(42);
        $this->assertInstanceOf(Organisation::class, $result);
        $this->assertEquals(42, $result->organisationId);
    }

    public function testGetOrganisationNotFound()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'organisation/121212.json')->willThrowException(new Exception('Not found!',
            404));
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not found!');
        $client->getOrganisation(121212);
    }

    /**
     * @throws Exception
     */
    public function testGetProjectsAllDefault()
    {
        $this->setUpProjects();
        $filter = [
            'keyword'        => null,
            'thematicFields' => null,
            'expertise'      => null,
            'take'           => 10,
            'skip'           => 0,
            'sort'           => [
                [
                    'field' => 'created',
                    'dir'   => 'desc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'projects/find.json', $filter)->willReturn($this->projects);
        $result = $client->getProjects();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Project::class, $result);
        $this->assertEquals(22, $result[0]->projectId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->description);
        $this->assertNull($result[0]->location);
        $this->assertEquals('Hungary', $result[1]->location);

        $this->assertNull($result[0]->expertise);
        $this->assertContainsOnlyInstancesOf(Expertise::class, $result[1]->expertise);
        $this->assertCount(1, $result[1]->expertise);
        $this->assertNotEmpty($result[1]->expertise[0]->code);
        $this->assertNotEmpty($result[1]->expertise[0]->name);
        $this->assertNotEmpty($result[1]->expertise[0]->dimension);
        $this->assertNotEmpty($result[1]->expertise[0]->score);
        $this->assertIsString($result[1]->expertise[0]->code);
        $this->assertIsString($result[1]->expertise[0]->name);
        $this->assertIsString($result[1]->expertise[0]->dimension);
        $this->assertIsInt($result[1]->expertise[0]->score);

        $this->assertIsString($result[0]->alpha3);
        $this->assertInstanceOf(DateTime::class, $result[0]->updated);
    }

    /**
     * @throws Exception
     */
    public function testGetProjectsAllSet()
    {
        $this->setUpProjects();
        $filter = [
            'keyword'        => 'test',
            'thematicFields' => [1],
            'expertise'      => [2],
            'take'           => 4,
            'skip'           => 1,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'projects/find.json', $filter)->willReturn($this->projects);
        $client->getProjects('test', [1], [2], 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetProject()
    {
        $this->setUpProjects();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'projects/22.json')->willReturn((object)['project' => $this->projects[0]]);

        $result = $client->getProject(22);
        $this->assertInstanceOf(Project::class, $result);
        $this->assertEquals(22, $result->projectId);
    }

    /**
     * @throws CurlException
     * @throws JsonMapper_Exception
     * @throws JsonException
     */
    public function testGetProjectNotFound()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'projects/121212.json')->willReturn((object)['project' => null]);
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Project not found!');
        $client->getProject(121212);
    }

    /**
     * @throws Exception
     */
    public function testGetSchemesAllDefault()
    {
        $this->setUpSchemes();
        $filter = [
            'keyword'        => null,
            'thematicFields' => null,
            'expertise'      => null,
            'take'           => 10,
            'skip'           => 0,
            'sort'           => [
                [
                    'field' => 'created',
                    'dir'   => 'desc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/find.json', $filter)->willReturn($this->schemes);
        $result = $client->getSchemes();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Scheme::class, $result);
        $this->assertEquals(28, $result[0]->qualificationSchemeId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->description);

        $this->assertNull($result[0]->expertise);
        $this->assertContainsOnlyInstancesOf(Expertise::class, $result[1]->expertise);
        $this->assertCount(count($this->schemes[1]->expertise), $result[1]->expertise);
        $this->assertNotEmpty($result[1]->expertise[0]->code);
        $this->assertNotEmpty($result[1]->expertise[0]->name);
        $this->assertNotEmpty($result[1]->expertise[0]->dimension);
        $this->assertIsString($result[1]->expertise[0]->code);
        $this->assertIsString($result[1]->expertise[0]->name);
        $this->assertIsString($result[1]->expertise[0]->dimension);
        $this->assertIsInt($result[1]->expertise[0]->score);

        $this->assertInstanceOf(DateTime::class, $result[0]->created);
        $this->assertInstanceOf(DateTime::class, $result[0]->updated);
    }

    /**
     * @throws Exception
     */
    public function testGetSchemesAllSet()
    {
        $this->setUpSchemes();
        $filter = [
            'keyword'        => 'test',
            'thematicFields' => [1],
            'expertise'      => [2],
            'take'           => 4,
            'skip'           => 1,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/find.json', $filter)->willReturn($this->schemes);
        $client->getSchemes('test', [1], [2], 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetScheme()
    {
        $this->setUpSchemes();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/28.json')->willReturn((object)['scheme' => $this->schemes[0]]);

        $result = $client->getScheme(28);
        $this->assertInstanceOf(Scheme::class, $result);
        $this->assertEquals(28, $result->qualificationSchemeId);
    }

    /**
     * @throws Exception
     */
    public function testGetSchemeNotFound()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/121212.json')->willReturn((object)['scheme' => null]);
        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Scheme not found!');
        $client->getScheme(121212);
    }

    /**
     * @throws Exception
     */
    public function testCompareSchemes()
    {
        $this->setUpSchemes();
        $this->setUpCompareResult();

        $compareResult = [
            'scheme'         => $this->schemes[1],
            'opponentScheme' => $this->schemes[0],
            'compare'        => $this->compareResult
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/compare/13/28.json')->willReturn((object)$compareResult);

        $result = $client->compareSchemes(13, 28);
        $this->assertInstanceOf(SchemeCompare::class, $result);
        $this->assertInstanceOf(Scheme::class, $result->scheme);
        $this->assertEquals($compareResult['scheme']->qualificationSchemeId, $result->scheme->qualificationSchemeId);
        $this->assertInstanceOf(Scheme::class, $result->opponentScheme);
        $this->assertEquals($compareResult['opponentScheme']->qualificationSchemeId,
            $result->opponentScheme->qualificationSchemeId);
        $this->assertIsArray($result->compare);
        $this->assertArrayHasKey('Process', $result->compare);
    }

    /**
     * @throws Exception
     */
    public function testCompareSchemesNotFound()
    {
        $this->setUpSchemes();
        $compareResult = [
            'scheme'         => null,
            'opponentScheme' => $this->schemes[0]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'schemes/compare/1313/2828.json')->willReturn((object)$compareResult);

        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Scheme not found!');
        $client->compareSchemes(1313, 2828);
    }

    /**
     * @return void
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function testGetDimensions()
    {
        $this->setUpDimensions();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client,
            'competence-quality-standard/home/get-dimensions.json')->willReturn($this->dimensions);
        $result = $client->getDimensions();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Dimension::class, $result);
        $this->assertEquals(4, $result[1]->dimensionId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->description);
        $this->assertIsString($result[0]->thumbnail);
        $this->assertIsString($result[0]->uuid);
        $this->assertIsInt($result[0]->rank);
    }

    /**
     * @return void
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function testGetThematicFields()
    {
        $this->setUpThematicFields();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client,
            'competence-quality-standard/home/get-thematic-fields.json')->willReturn($this->thematicFields);
        $result = $client->getThematicFields();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(ThematicField::class, $result);
        $this->assertEquals(22, $result[1]->thematicFieldId);
        $this->assertEquals(4, $result[1]->dimensionId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->description);
        $this->assertIsString($result[0]->code);
        $this->assertIsString($result[0]->dimensionName);
        $this->assertIsString($result[0]->uuid);
        $this->assertIsInt($result[0]->rank);
    }

    /**
     * @return void
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function testGetThematicFieldsByDimension()
    {
        $this->setUpThematicFields();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client,
            'competence-quality-standard/home/get-thematic-fields/4.json')->willReturn($this->thematicFields);
        $result = $client->getThematicFields(4);
        $this->assertIsArray($result);
    }

    /**
     * @return void
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function testGetExpertises()
    {
        $this->setUpExpertises();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client,
            'competence-quality-standard/home/get-expertises.json')->willReturn($this->expertises);
        $result = $client->getExpertises();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(CqsExpertise::class, $result);
        $this->assertEquals(68, $result[1]->expertiseId);
        $this->assertEquals(41, $result[1]->macroAreaId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->description);
        $this->assertIsString($result[0]->code);
        $this->assertIsString($result[0]->uuid);
        $this->assertIsInt($result[0]->rank);
        $this->assertIsString($result[0]->macroAreaName);
        $this->assertIsString($result[0]->macroAreaCode);
    }

    /**
     * @return void
     *
     * @throws CurlException
     * @throws JsonException
     * @throws JsonMapper_Exception
     */
    public function testGetExpertisesByThematicField()
    {
        $this->setUpExpertises();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client,
            'competence-quality-standard/home/get-expertises/22.json')->willReturn($this->expertises);
        $result = $client->getExpertises(22);
        $this->assertIsArray($result);
    }


    /**
     * @throws Exception
     */
    public function testGetCoursesAllDefault()
    {
        $this->setUpCourses();
        $filter = [
            'keyword'        => null,
            'thematicFields' => null,
            'expertise'      => null,
            'take'           => 10,
            'skip'           => 0,
            'sort'           => [
                [
                    'field' => 'created',
                    'dir'   => 'desc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'competence-quality-standard/e-inventory/course-search.json',
            $filter)->willReturn($this->courses);
        $result = $client->getCourses();

        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Course::class, $result);
        $this->assertEquals(34, $result[0]->courseId);
        $this->assertIsString($result[0]->name);
        $this->assertIsString($result[0]->code);
        $this->assertIsString($result[0]->description);
        $this->assertNull($result[1]->country);
        $this->assertEquals('Democratic Republic of the Congo', $result[0]->country);

        $this->assertNull($result[0]->createdBy);

        $this->assertInstanceOf(DateTime::class, $result[0]->created);
        $this->assertInstanceOf(DateTime::class, $result[0]->updated);
        $this->assertIsString($result[0]->alpha3);

        $this->assertNull($result[0]->competences);
    }


    /**
     * @throws Exception
     */
    public function testGetCoursesAllSet()
    {
        $this->setUpCourses();
        $filter = [
            'keyword'        => 'test',
            'thematicFields' => [1],
            'expertise'      => [2],
            'take'           => 4,
            'skip'           => 1,
            'sort'           => [
                [
                    'field' => 'name',
                    'dir'   => 'asc'
                ]
            ]
        ];
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'competence-quality-standard/e-inventory/course-search.json',
            $filter)->willReturn($this->courses);
        $client->getCourses('test', [1], [2], 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetCourse()
    {
        $this->setUpCourses();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->onlyMethods(['execute'])->getMock();
        $this->setUpExecute($client, 'e-inventory/course/34.json')->willReturn($this->courses[0]);

        $result = $client->getCourse(34);
        $this->assertInstanceOf(Course::class, $result);
        $this->assertEquals(34, $result->courseId);

        $this->assertInstanceOf(Passport::class, $result->competences);
        $this->assertCount(1, $result->competences->dimensions);
    }

    /**
     * @throws Exception
     */
    public function testExecuteList()
    {
        $filter = 'keyword=test&thematicFields[0]=1&expertise[0]=2&expertiseLevel=3&take=4&skip=1&sort[0][field]=name&sort[0][dir]=asc';
        $this->setUpExperts();
        $this->curlMock->expects($this->exactly(2))->method('setOptions')->withConsecutive(
            [$this->anything()],
            [
                $this->callback(function (array $options) use ($filter) {
                    TestCase::assertArrayHasKey(CURLOPT_URL, $options);
                    TestCase::assertArrayHasKey(CURLOPT_POST, $options);
                    TestCase::assertArrayHasKey(CURLOPT_CUSTOMREQUEST, $options);
                    TestCase::assertEquals('http://esr-train4sustain-pg.test/expert/find.json?' . $filter,
                        urldecode($options[CURLOPT_URL]));
                    return true;
                })
            ]
        );
        $this->curlMock->expects($this->once())->method('exec');
        $this->curlMock->expects($this->once())->method('getBody')->willReturn(json_encode($this->experts));
        $this->curlMock->method('getHeader')->willReturn('200 OK');

        $this->setUpClient();
        $result = $this->client->getExperts('test', [1], [2], 3, 4, 1, [
            [
                'field' => 'name',
                'dir'   => 'asc'
            ]
        ]);
        $this->assertCount(count($this->experts), $result);
    }

    /**
     * @throws Exception
     */
    public function testExecuteEntity()
    {
        $this->setUpExperts();
        $this->curlMock->method('getHeader')->willReturn('200 OK');
        $this->curlMock->expects($this->once())->method('getBody')->willReturn(json_encode($this->experts[0]));

        $this->setUpClient();
        $result = $this->client->getExpert(1);
        $this->assertEquals($this->experts[0]->profileId, $result->expertId);
    }

    /**
     * @throws Exception
     */
    public function testExecuteCurlError()
    {
        $exception = new CurlException('Curl Error', 123);
        $this->curlMock->expects($this->once())->method('exec')->willThrowException($exception);
        $this->curlMock->expects($this->never())->method('getBody');

        $this->expectException(CurlException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $this->expectExceptionCode($exception->getCode());
        $this->setUpClient()->getExperts();
    }

    /**
     * @throws Exception
     */
    public function testExecuteInvalidBody()
    {
        $exception = new CurlException('Unprocessable response', 1234);
        $this->curlMock->expects($this->once())->method('getBody')->willThrowException($exception);
        $this->curlMock->method('getHeader')->willReturn('200 OK');

        $this->expectException(CurlException::class);
        $this->expectExceptionMessage($exception->getMessage());
        $this->expectExceptionCode($exception->getCode());
        $this->setUpClient()->getExperts();
    }

    /**
     * @throws Exception
     */
    public function testExecuteInvalidJson()
    {
        $this->setUpExperts();
        $this->curlMock->expects($this->once())->method('getBody')->willReturn('{');
        $this->curlMock->method('getHeader')->willReturn('200 OK');

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Invalid response returned!');
        $this->setUpClient()->getExperts();
    }

    public function testExecuteNotFound()
    {
        $this->setUpClient();
        $this->curlMock->expects($this->once())->method('getHeader')->willReturn('HTTP/1.1 404 Not Found');
        $this->curlMock->expects($this->never())->method('getBody');

        $this->expectException(Exception::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Not found!');
        $this->client->getExpert(121212);
    }

    public function testExecuteUnkownError()
    {
        $this->setUpClient();
        $this->curlMock->expects($this->once())->method('getHeader')->willReturn('HTTP/1.1 400 Error');
        $this->curlMock->expects($this->never())->method('getBody');

        $this->expectException(Exception::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Unknown error!');
        $this->client->getExpert(121212);
    }


    /**
     * @return Client
     */
    protected function setUpClient(): Client
    {
        $this->client = new Client($this->curlMock, 'http', 'esr-train4sustain-pg.test');
        return $this->client;
    }

    /**
     * @param Client|MockObject $clientMock
     * @param string            $expectedUri
     * @param array|null        $params
     *
     * @return InvocationMocker
     */
    protected function setUpExecute(Client $clientMock, string $expectedUri, ?array $params = null): InvocationMocker
    {
        return $clientMock->expects(self::once())->method('execute')->with('GET', $expectedUri, $params);
    }

    /**
     * @return array
     */
    protected function setUpExperts(): array
    {
        $this->experts = json_decode('[
        {
            "profileId": 21,
            "userId": 21,
            "name": "George Doe",
            "emailAddress": "qweqwe@dsaasdasd.dsa",
            "shortProfile": "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.",
            "picture": "users\/george-doe-avatar.png",
            "pictureUri": "\/assets\/content\/users\/george-doe-avatar.png",
            "countryId": null,
            "country": null,
            "profession": null,
            "expertise": null,
            "isFavourite": null,
            "alpha3": "eng",
            "created": "2021-06-17T16:02:44+0200"
        },
        {
            "profileId": 20,
            "userId": 20,
            "name": "John Smith",
            "emailAddress": "sadasdasd@sadasdsad.ds",
            "shortProfile": "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem.",
            "picture": "users\/john-smith-avatar.png",
            "pictureUri": "\/assets\/content\/users\/john-smith-avatar.png",
            "countryId": null,
            "country": null,
            "profession": [
                {
                    "code": "AR",
                    "name": "Architects"
                },
                {
                    "code": "BEC",
                    "name": "Building Energy Consultants \/ Assessors"
                },
                {
                    "code": "CE",
                    "name": "Civil Engineers"
                },
                {
                    "code": "EE",
                    "name": "Environmental Engineers"
                },
                {
                    "code": "SC",
                    "name": "Sustainability Consultants \/ Assessors"
                }
            ],
            "expertise": [
                {
                    "code": "PC1",
                    "name": "Assessment methodology",
                    "score": 5,
                    "dimension": "Process"
                }
            ],
            "isFavourite": null,
            "alpha3": "eng",
            "created": "2021-06-17T16:02:25+0200"
        },
        {
            "profileId": 4,
            "userId": 4,
            "name": "Jane Smith",
            "emailAddress": "jane.smith@dum.my",
            "shortProfile": "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.",
            "picture": "users\/profile_4_1625674223.jpg",
            "pictureUri": "\/assets\/content\/users\/profile_4_1625674223.jpg",
            "countryId": 98,
            "country": "Hungary",
            "profession": [
                {
                    "code": "AR",
                    "name": "Architects"
                },
                {
                    "code": "BEC",
                    "name": "Building Energy Consultants \/ Assessors"
                },
                {
                    "code": "CE",
                    "name": "Civil Engineers"
                },
                {
                    "code": "EE",
                    "name": "Environmental Engineers"
                },
                {
                    "code": "SC",
                    "name": "Sustainability Consultants \/ Assessors"
                },
                {
                    "code": "UP",
                    "name": "Urban planner"
                }
            ],
            "expertise": [
                {
                    "code": "PC1",
                    "name": "Assessment methodology",
                    "score": 5,
                    "dimension": "Process"
                }
            ],
            "isFavourite": null,
            "alpha3": "eng",
            "created": "2021-05-25T12:34:13+0200"
        }]');
        return $this->experts;
    }

    /**
     * @return array
     */
    protected function setUpProjects(): array
    {
        $this->projects = json_decode(
            '[
        {
            "projectId": 22,
            "uuid": "b1a2a1ba-d98c-11eb-81c3-000c292f0389",
            "startDate": "2021-06-30T00:00:00+0200",
            "endDate": "2025-06-30T00:00:00+0200",
            "createdBy": 2,
            "countryId": null,
            "location": null,
            "name": "IMMUNION",
            "description": "This is a project that we have to deploy today",
            "alpha3": "eng",
            "contents": [
                {
                    "projectId": 22,
                    "alpha3": "eng",
                    "name": "IMMUNION",
                    "description": "This is a project that we have to deploy today",
                    "updated": "2021-06-30T12:19:46+0200"
                },
                {
                    "projectId": 22,
                    "alpha3": "hun",
                    "name": "",
                    "description": "",
                    "updated": "2021-06-30T12:19:46+0200"
                }
            ],
            "updated": "2021-06-30T12:19:46+0200",
            "created": null,
            "attachments": [],
            "expertise": null
        },
        {
            "projectId": 26,
            "uuid": "b7829327-d9b5-11eb-81c3-000c292f0389",
            "startDate": "2021-06-01T00:00:00+0200",
            "endDate": "2021-07-31T00:00:00+0200",
            "createdBy": 1,
            "countryId": 98,
            "location": "Hungary",
            "name": "Lorem ipsum",
            "description": "Aenean scelerisque dapibus feugiat. Aenean dapibus tortor volutpat nisi imperdiet tristique. Integer sed risus eu enim suscipit placerat eu in sem. Mauris finibus lorem vel sapien convallis, vitae laoreet purus tempor. Maecenas luctus urna sed velit molestie tristique. Proin cursus non magna vel cursus. Nunc ullamcorper quam ipsum, id finibus mi fringilla ac. Maecenas sed pellentesque erat. Donec dignissim massa hendrerit eleifend tincidunt.",
            "alpha3": "eng",
            "contents": [
                {
                    "projectId": 26,
                    "alpha3": "eng",
                    "name": "Lorem ipsum",
                    "description": "Aenean scelerisque dapibus feugiat. Aenean dapibus tortor volutpat nisi imperdiet tristique. Integer sed risus eu enim suscipit placerat eu in sem. Mauris finibus lorem vel sapien convallis, vitae laoreet purus tempor. Maecenas luctus urna sed velit molestie tristique. Proin cursus non magna vel cursus. Nunc ullamcorper quam ipsum, id finibus mi fringilla ac. Maecenas sed pellentesque erat. Donec dignissim massa hendrerit eleifend tincidunt.",
                    "updated": "2021-06-30T17:13:25+0200"
                },
                {
                    "projectId": 26,
                    "alpha3": "hun",
                    "name": "",
                    "description": "",
                    "updated": "2021-06-30T17:13:25+0200"
                }
            ],
            "updated": "2021-06-30T17:13:25+0200",
            "created": null,
            "attachments": [],
            "expertise": [
                {
                    "code": "MS2",
                    "name": "Environmental impact of construction materials",
                    "score": 3,
                    "dimension": "Environment"
                }
            ]
        }
    ]');
        return $this->projects;
    }

    /**
     * @return array
     */
    protected function setUpSchemes(): array
    {
        $this->schemes = json_decode(
            '[
        {
            "qualificationSchemeId": 28,
            "name": "20210708-1",
            "description": "",
            "url": "",
            "uuid": "ae3dbfd9-dfd1-11eb-81c3-000c292f0389",
            "createdBy": 3,
            "created": "2021-07-08T11:48:42+0200",
            "updated": "2021-07-08T11:48:42+0200",
            "contents": [
                {
                    "QualificationSchemeId": 28,
                    "alpha3": "eng",
                    "name": "20210708-1",
                    "description": "",
                    "updated": "2021-07-08T12:15:11+0200"
                },
                {
                    "QualificationSchemeId": 28,
                    "alpha3": "hun",
                    "name": "",
                    "description": "",
                    "updated": "2021-07-08T11:48:42+0200"
                }
            ],
            "expertise": null
        },
        {
            "qualificationSchemeId": 13,
            "name": "CasaClima Energy Consultant",
            "description": "Loremi psu,mdo lorsi tametCasaClima Energy Consultant",
            "url": "https:\/\/www.agenziacasaclima.it\/",
            "uuid": "cc6c4ddc-bef2-11eb-923a-000c292f0389",
            "createdBy": 1,
            "created": "2021-05-27T15:52:38+0200",
            "updated": "2021-06-30T17:54:22+0200",
            "contents": [
                {
                    "QualificationSchemeId": 13,
                    "alpha3": "eng",
                    "name": "CasaClima Energy Consultant",
                    "description": "Loremi psu,mdo lorsi tametCasaClima Energy Consultant",
                    "updated": "2021-06-28T14:47:08+0200"
                },
                {
                    "QualificationSchemeId": 13,
                    "alpha3": "hun",
                    "name": "",
                    "description": "L\u00f3rem ipszum dol\u00f3r szita meth 13",
                    "updated": "2021-06-28T14:52:29+0200"
                }
            ],
            "expertise": [
                {
                    "code": "EM2",
                    "name": "Domotic systems (homes)",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "EP0",
                    "name": "Heating and Cooling GENERAL",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "ER7",
                    "name": "Heating and cooling emission systems",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "MS3",
                    "name": "Recycled material",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "MS4",
                    "name": "Renewable materials",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "MS5",
                    "name": "Certified materials",
                    "score": 0,
                    "dimension": "Environment"
                },
                {
                    "code": "DI1",
                    "name": "Integrated Design Process",
                    "score": 0,
                    "dimension": "Process"
                },
                {
                    "code": "PP1",
                    "name": "Assessment methodology",
                    "score": 0,
                    "dimension": "Process"
                },
                {
                    "code": "PC1",
                    "name": "Assessment methodology",
                    "score": 0,
                    "dimension": "Process"
                },
                {
                    "code": "PC2",
                    "name": "Certification process",
                    "score": 0,
                    "dimension": "Process"
                },
                {
                    "code": "CQ3",
                    "name": "Mechanical ventilation",
                    "score": 0,
                    "dimension": "Society"
                },
                {
                    "code": "CT1",
                    "name": "Thermal Comfort Indoor",
                    "score": 0,
                    "dimension": "Society"
                },
                {
                    "code": "CV1",
                    "name": "Daylighting",
                    "score": 0,
                    "dimension": "Society"
                },
                {
                    "code": "CA1",
                    "name": "Passive building acoustic requirements",
                    "score": 0,
                    "dimension": "Society"
                },
                {
                    "code": "FS2",
                    "name": "Functional mix",
                    "score": 0,
                    "dimension": "Society"
                }
            ]
        }
    ]');
        return $this->schemes;
    }

    /**
     * @return object
     */
    protected function setUpCompareResult(): object
    {
        $this->compareResult = json_decode(
            '{
                    "Process": {
                        "name": "Process",
                        "thematicFields": {
                            "Building design": {
                                "name": "Building design",
                                "code": "D",
                                "macroAreas": {
                                    "Integrative design": {
                                        "name": "Integrative design",
                                        "code": "DI",
                                        "expertise": {
                                            "Integrated Design Process": {
                                                "name": "Integrated Design Process",
                                                "code": "DI1",
                                                "expertiseId": 88,
                                                "level": [
                                                    5
                                                ]
                                            }
                                        }
                                    }
                                }
                            },
                            "Sustainability certification system": {
                                "name": "Sustainability certification system",
                                "code": "P",
                                "macroAreas": {
                                    "Protocollo ITACA": {
                                        "name": "Protocollo ITACA",
                                        "code": "PP",
                                        "expertise": {
                                            "Assessment methodology": {
                                                "name": "Assessment methodology",
                                                "code": "PP1",
                                                "expertiseId": 115,
                                                "level": [
                                                    2,
                                                    3,
                                                    5
                                                ]
                                            }
                                        }
                                    },
                                    "CasaClima": {
                                        "name": "CasaClima",
                                        "code": "PC",
                                        "expertise": {
                                            "Assessment methodology": {
                                                "name": "Assessment methodology",
                                                "code": "PC1",
                                                "expertiseId": 138,
                                                "level": [
                                                    5
                                                ]
                                            },
                                            "Certification process": {
                                                "name": "Certification process",
                                                "code": "PC2",
                                                "expertiseId": 139,
                                                "level": [
                                                    5
                                                ],
                                                "opponentLevel": [
                                                    1,
                                                    2,
                                                    3,
                                                    5
                                                ]
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
            }');
        return $this->compareResult;
    }

    /**
     * @return object
     */
    protected function setUpPassportResult(): object
    {
        $this->passportResult = json_decode('
            {
    "Society": {
        "name": "Society",
        "thematicFields": {
            "Comfort and well being": {
                "name": "Comfort and well being",
                "code": "C",
                "macroAreas": {
                    "Quality of air": {
                        "name": "Quality of air",
                        "code": "CQ",
                        "expertise": {
                            "Natural ventilation": {
                                "name": "Natural ventilation",
                                "code": "CQ2",
                                "expertiseId": 53,
                                "level": 5
                            },
                            "Mechanical ventilation": {
                                "name": "Mechanical ventilation",
                                "code": "CQ3",
                                "expertiseId": 54,
                                "level": 5
                            }
                        }
                    },
                    "Thermal comfort": {
                        "name": "Thermal comfort",
                        "code": "CT",
                        "expertise": {
                            "Thermal Comfort Indoor": {
                                "name": "Thermal Comfort Indoor",
                                "code": "CT1",
                                "expertiseId": 56,
                                "level": 5
                            },
                            "Ambient thermal comfort conditions": {
                                "name": "Ambient thermal comfort conditions",
                                "code": "CT2",
                                "expertiseId": 57,
                                "level": 5
                            }
                        }
                    },
                    "Visual comfort": {
                        "name": "Visual comfort",
                        "code": "CV",
                        "expertise": {
                            "Daylighting": {
                                "name": "Daylighting",
                                "code": "CV1",
                                "expertiseId": 58,
                                "level": 5
                            },
                            "Interior lighting": {
                                "name": "Interior lighting",
                                "code": "CV2",
                                "expertiseId": 59,
                                "level": 5
                            },
                            "Illumination of outdoor spaces": {
                                "name": "Illumination of outdoor spaces",
                                "code": "CV3",
                                "expertiseId": 60,
                                "level": 5
                            }
                        }
                    }
                }
            }
        }
    },
    "Environment": {
        "name": "Environment",
        "thematicFields": {
            "Energy": {
                "name": "Energy",
                "code": "E",
                "macroAreas": {
                    "Energy management": {
                        "name": "Energy management",
                        "code": "EM",
                        "expertise": {
                            "Building management systems BMS": {
                                "name": "Building management systems BMS",
                                "code": "EM3",
                                "expertiseId": 12,
                                "level": 5
                            }
                        }
                    },
                    "Energy production": {
                        "name": "Energy production",
                        "code": "EP",
                        "expertise": {
                            "Heating and Cooling GENERAL": {
                                "name": "Heating and Cooling GENERAL",
                                "code": "EP0",
                                "expertiseId": 13,
                                "level": 5
                            },
                            "Geothermal energy systems": {
                                "name": "Geothermal energy systems",
                                "code": "EP1",
                                "expertiseId": 14,
                                "level": 5
                            },
                            "Solar thermal energy systems for heating gen.": {
                                "name": "Solar thermal energy systems for heating gen.",
                                "code": "EP8",
                                "expertiseId": 21,
                                "level": 5
                            }
                        }
                    },
                    "Energy reproduction": {
                        "name": "Energy reproduction",
                        "code": "ER",
                        "expertise": {
                            "Insulation": {
                                "name": "Insulation",
                                "code": "ER1",
                                "expertiseId": 23,
                                "level": 5
                            },
                            "Ventilation systems": {
                                "name": "Ventilation systems",
                                "code": "ER10",
                                "expertiseId": 33,
                                "level": 5
                            },
                            "Air tightness building": {
                                "name": "Air tightness building",
                                "code": "ER2",
                                "expertiseId": 25,
                                "level": 5
                            },
                            "Window and\/or glazing systems": {
                                "name": "Window and\/or glazing systems",
                                "code": "ER6",
                                "expertiseId": 29,
                                "level": 5
                            },
                            "Electric heating systems": {
                                "name": "Electric heating systems",
                                "code": "ER8",
                                "expertiseId": 31,
                                "level": 5
                            },
                            "Artificial lighting systems": {
                                "name": "Artificial lighting systems",
                                "code": "ER9",
                                "expertiseId": 32,
                                "level": 5
                            }
                        }
                    }
                }
            },
            "Materials": {
                "name": "Materials",
                "code": "M",
                "macroAreas": {
                    "Design for deconstruction, reuse and recycling": {
                        "name": "Design for deconstruction, reuse and recycling",
                        "code": "MD",
                        "expertise": {
                            "Materials and components for ease of disassembly": {
                                "name": "Materials and components for ease of disassembly",
                                "code": "MD1",
                                "expertiseId": 39,
                                "level": 5
                            }
                        }
                    }
                }
            }
        }
    },
    "Process": {
        "name": "Process",
        "thematicFields": {
            "Innovative design solutions": {
                "name": "Innovative design solutions",
                "code": "I",
                "macroAreas": {
                    "Building Information Modelling": {
                        "name": "Building Information Modelling",
                        "code": "IB",
                        "expertise": {
                            "Operation of BIM systems": {
                                "name": "Operation of BIM systems",
                                "code": "IB1",
                                "expertiseId": 96,
                                "level": 5
                            }
                        }
                    }
                }
            }
        }
    }
}
        ');
        return $this->passportResult;
    }

    /**
     * @return array
     */
    protected function setUpOrganisations(): array
    {
        $this->organisations = json_decode('[
    {
        "profession": [
            {
                "code": "AR",
                "name": "Architects"
            },
            {
                "code": "BEC",
                "name": "Building Energy Consultants \/ Assessors"
            },
            {
                "code": "CE",
                "name": "Civil Engineers"
            },
            {
                "code": "EE",
                "name": "Environmental Engineers"
            }
        ],
        "expertise": [
            {
                "code": "CT1",
                "name": "Thermal Comfort Indoor",
                "score": 5,
                "dimension": "Society"
            },
            {
                "code": "MS5",
                "name": "Certified materials",
                "score": 4,
                "dimension": "Environment"
            },
            {
                "code": "PC2",
                "name": "Certification process",
                "score": 3,
                "dimension": "Process"
            }
        ],
        "isFavourite": null,
        "alpha3": "eng",
        "organisationId": 42,
        "name": "Test organisation",
        "note": "",
        "vatNumber": "qwe123",
        "description": "This is a test organisation here.",
        "url": "organisation.test",
        "logo": "\/assets\/images\/avatar.png",
        "addressId": null,
        "createdBy": 54,
        "createdByName": null,
        "uuid": null,
        "updated": null,
        "address": null
    }
]');
        return $this->organisations;
    }

    /**
     * @return array
     */
    protected function setUpDimensions(): array
    {
        $this->dimensions = json_decode('
            [
    {
        "dimensionId": 1,
        "rank": 1,
        "thumbnail": "cqs\/dimensions\/environment.svg",
        "uuid": "f9acbcdb-b4a0-11eb-923a-000c292f0389",
        "created": "2021-05-14T12:41:44+0200",
        "name": "Environment",
        "alpha3": "eng",
        "description": "<p>Protect&nbsp;the planet from degradation, through sustainable consumption and production, managing natural resources and taking urgent action on climate change.<\/p>\n",
        "contents": [
            {
                "dimensionId": 1,
                "alpha3": "eng",
                "name": "Environment",
                "description": "<p>Protect&nbsp;the planet from degradation, through sustainable consumption and production, managing natural resources and taking urgent action on climate change.<\/p>\n",
                "updated": "2021-06-30T14:03:28+0200"
            },
            {
                "dimensionId": 1,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-14T14:45:49+0200"
            }
        ]
    },
    {
        "dimensionId": 4,
        "rank": 2,
        "thumbnail": "cqs\/dimensions\/society.svg",
        "uuid": "56c72c56-b4b2-11eb-923a-000c292f0389",
        "created": "2021-05-14T14:46:01+0200",
        "name": "Society",
        "alpha3": "eng",
        "description": "Let all human beings can fulfil their potential in a safe and healthy environment adapted to the climate change.",
        "contents": [
            {
                "dimensionId": 4,
                "alpha3": "eng",
                "name": "Society",
                "description": "Let all human beings can fulfil their potential in a safe and healthy environment adapted to the climate change.",
                "updated": "2021-06-30T14:04:33+0200"
            },
            {
                "dimensionId": 4,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-14T14:46:01+0200"
            }
        ]
    }]
        ');
        return $this->dimensions;
    }

    protected function setUpThematicFields(): array
    {
        $this->thematicFields = json_decode('
            [
    {
        "thematicFieldId": 19,
        "dimensionId": 4,
        "uuid": "685960d4-be03-11eb-923a-000c292f0389",
        "code": "A",
        "rank": 21,
        "name": "Accessibility",
        "alpha3": "eng",
        "description": "",
        "dimensionName": "Society",
        "contents": [
            {
                "thematicFieldId": 19,
                "alpha3": "eng",
                "name": "Accessibility",
                "description": "",
                "updated": "2021-05-26T11:19:00+0200"
            },
            {
                "thematicFieldId": 19,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-26T11:19:00+0200"
            }
        ]
    },
    {
        "thematicFieldId": 22,
        "dimensionId": 4,
        "uuid": "848dda90-be03-11eb-923a-000c292f0389",
        "code": "R",
        "rank": 18,
        "name": "Adaptation and resilience to climate change",
        "alpha3": "eng",
        "description": "",
        "dimensionName": "Society",
        "contents": [
            {
                "thematicFieldId": 22,
                "alpha3": "eng",
                "name": "Adaptation and resilience to climate change",
                "description": "",
                "updated": "2021-05-26T11:19:48+0200"
            },
            {
                "thematicFieldId": 22,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-26T11:19:48+0200"
            }
        ]
    },
    {
        "thematicFieldId": 24,
        "dimensionId": 15,
        "uuid": "a81dcc16-be03-11eb-923a-000c292f0389",
        "code": "D",
        "rank": 16,
        "name": "Building design",
        "alpha3": "eng",
        "description": "",
        "dimensionName": "Process",
        "contents": [
            {
                "thematicFieldId": 24,
                "alpha3": "eng",
                "name": "Building design",
                "description": "",
                "updated": "2021-05-26T12:00:46+0200"
            },
            {
                "thematicFieldId": 24,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-26T11:20:47+0200"
            }
        ]
    }]
        ');
        return $this->thematicFields;
    }

    protected function setUpExpertises(): array
    {
        $this->expertises = json_decode('
            [
    {
        "expertiseId": 40,
        "macroAreaId": 29,
        "macroAreaCode": "MS",
        "rank": 125,
        "code": "MS1",
        "uuid": "2587ee59-be19-11eb-923a-000c292f0389",
        "created": "2021-05-26T13:54:37+0200",
        "name": "(LCA)",
        "alpha3": "eng",
        "description": "",
        "macroAreaName": "Sustainable materials",
        "contents": [
            {
                "expertiseId": 40,
                "alpha3": "eng",
                "name": "(LCA)",
                "description": "",
                "updated": "2021-05-26T13:54:58+0200"
            },
            {
                "expertiseId": 40,
                "alpha3": "hun",
                "name": "",
                "description": "",
                "updated": "2021-05-26T13:54:58+0200"
            }
        ]
    },
    {
        "expertiseId": 68,
        "macroAreaId": 41,
        "macroAreaCode": "AB",
        "rank": 8,
        "code": "AB1",
        "uuid": "265581f6-be1d-11eb-923a-000c292f0389",
        "created": "2021-05-26T14:23:17+0200",
        "name": "Accessibility of public spaces",
        "alpha3": "eng",
        "description": null,
        "macroAreaName": "Barrier free accessibility",
        "contents": [
            {
                "expertiseId": 68,
                "alpha3": "eng",
                "name": "Accessibility of public spaces",
                "description": null,
                "updated": "2021-05-26T14:23:17+0200"
            },
            {
                "expertiseId": 68,
                "alpha3": "hun",
                "name": null,
                "description": null,
                "updated": "2021-05-26T14:23:17+0200"
            }
        ]
    },
    {
        "expertiseId": 25,
        "macroAreaId": 24,
        "macroAreaCode": "ER",
        "rank": 72,
        "code": "ER2",
        "uuid": "369ce29f-be18-11eb-923a-000c292f0389",
        "created": "2021-05-26T13:47:56+0200",
        "name": "Air tightness building",
        "alpha3": "eng",
        "description": null,
        "macroAreaName": "Energy reproduction",
        "contents": [
            {
                "expertiseId": 25,
                "alpha3": "eng",
                "name": "Air tightness building",
                "description": null,
                "updated": "2021-05-26T13:47:56+0200"
            },
            {
                "expertiseId": 25,
                "alpha3": "hun",
                "name": null,
                "description": null,
                "updated": "2021-05-26T13:47:56+0200"
            }
        ]
    }]
        ');
        return $this->expertises;
    }

    protected function setUpCourses(): array
    {
        $this->courses = json_decode('
[
    {
        "courseId": 34,
        "qualificationSchemeId": 28,
        "providingInstitutionId": null,
        "sponsoringInstitutionId": null,
        "countryId": 51,
        "createdBy": null,
        "createdByName": null,
        "eqfLevel": null,
        "code": "F01",
        "status": "approved",
        "qualificationScheme": "20210708-1",
        "uuid": "88e1e3dc-dfd5-11eb-81c3-000c292f0389",
        "created": "2021-07-08T12:16:18+0200",
        "updated": "2022-04-20T10:44:53+0200",
        "duration": "",
        "country": "Democratic Republic of the Congo",
        "name": "20210708-1\/b",
        "description": "a",
        "professionalQualificationTitle": "b",
        "targetGroups": "c",
        "didacticMethod": "d",
        "prerequisites": "e",
        "qualificationRenewal": "f",
        "qualificationRegister": "g",
        "referenceLegislation": "h",
        "providingInstitution": "i",
        "sponsoringInstitution": "j",
        "alpha3": "eng",
        "contents": [],
        "providingInstitutionModel": null,
        "sponsoringInstitutionModel": null,
        "learningOutcomes": {
            "Process": {
                "name": "Process",
                "thematicFields": {
                    "Sustainability certification system": {
                        "name": "Sustainability certification system",
                        "code": "P",
                        "macroAreas": {
                            "CasaClima": {
                                "name": "CasaClima",
                                "code": "PC",
                                "expertise": {
                                    "Certification process": {
                                        "name": "Certification process",
                                        "code": "PC2",
                                        "expertiseId": 139,
                                        "level": 0,
                                        "learningOutcomes": [
                                            {
                                                "id": 93,
                                                "name": "Facilitate and support project teams to achieve the target Protocollo CASACLIMA rating"
                                            },
                                            {
                                                "id": 116,
                                                "name": "Facilitate and support project teams to reach the target CasaClima Sustainability Protocol rating."
                                            },
                                            {
                                                "id": 90,
                                                "name": "Facilitate and support the project teams to achieve the target CASACLIMA Protocollo rating"
                                            },
                                            {
                                                "id": 119,
                                                "name": "Facilitate and support the project teams to reach the target CasaClima Sustainability Protocol rating."
                                            }
                                        ]
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    },
    {
        "courseId": 33,
        "qualificationSchemeId": 14,
        "providingInstitutionId": null,
        "sponsoringInstitutionId": 4,
        "countryId": null,
        "createdBy": null,
        "createdByName": null,
        "eqfLevel": null,
        "code": "e",
        "status": "approved",
        "qualificationScheme": "CasaClima Sustainability Consul",
        "uuid": "72642fc3-dfd5-11eb-81c3-000c292f0389",
        "created": "2021-07-08T12:15:40+0200",
        "updated": "2022-03-30T14:34:32+0200",
        "duration": "480000",
        "country": null,
        "name": "20210708-1\/a  ",
        "description": "<p>Qualifying training on the subject of energy transition.; Gesture practice on technical platforms.; Be operational in a promising field.; Define and estimate energy performance work on new and old buildings.; Plan and organize the site.; Carry out and supervise the energy performance work of the building&nbsp;&nbsp;<\/p>\n",
        "professionalQualificationTitle": "",
        "targetGroups": "",
        "didacticMethod": "",
        "prerequisites": "",
        "qualificationRenewal": "asd",
        "qualificationRegister": "dsadsa",
        "referenceLegislation": "",
        "providingInstitution": "fsd",
        "sponsoringInstitution": "sdf",
        "alpha3": "eng",
        "contents": [],
        "providingInstitutionModel": null,
        "sponsoringInstitutionModel": null,
        "learningOutcomes": []
    }]
        ');
        return $this->courses;
    }
}
