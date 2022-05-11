<?php

namespace EmgSystems\Train4Sustain\Model;

use PHPUnit\Framework\TestCase;

/**
 * Test class for Passport Model.
 *
 * @package emg-systems/t4s-api-client
 * @covers  \EmgSystems\Train4Sustain\Model\Passport
 */
class PassportTest extends TestCase
{

    public function testBuildFromObject()
    {
        $result = Passport::buildFromObject($this->getMockObject());
        $this->assertInstanceOf(Passport::class, $result);
        $this->assertCount(1, $result->dimensions);
        $this->assertEquals('Society', $result->dimensions[0]->name);
        $this->assertCount(1, $result->dimensions[0]->thematicFields);
        $this->assertEquals('Comfort and well being', $result->dimensions[0]->thematicFields[0]->name);
        $this->assertEquals('C', $result->dimensions[0]->thematicFields[0]->code);
        $this->assertCount(2, $result->dimensions[0]->thematicFields[0]->macroAreas);
        $this->assertEquals('Quality of air', $result->dimensions[0]->thematicFields[0]->macroAreas[0]->name);
        $this->assertEquals('CQ', $result->dimensions[0]->thematicFields[0]->macroAreas[0]->code);
    }

    /**
     * @return object
     */
    protected function getMockObject(): object
    {
        return json_decode('{
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
                    }
                }
            }
        }
    }
}');
    }
}
