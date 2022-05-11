<?php

namespace EmgSystems\Train4Sustain\Model;

use EmgSystems\Train4Sustain\Model\Passport\Dimension;
use EmgSystems\Train4Sustain\Model\Passport\MacroArea;
use EmgSystems\Train4Sustain\Model\Passport\ThematicField;

/**
 * Model describing a passport.
 */
class Passport
{
    /**
     * @param object $passportObject
     *
     * @return Passport
     */
    public static function buildFromObject(object $passportObject): Passport
    {
        $passport = new static();
        $passport->dimensions = [];
        foreach ((array)$passportObject as $dimensionName => $dimensionData) {
            $dimension = new Dimension();
            $dimension->name = $dimensionName;
            $dimension->thematicFields = [];
            foreach ((array)$dimensionData->thematicFields as $thematicFieldName => $thematicFieldData) {
                $thematicField = new ThematicField();
                $thematicField->name = $thematicFieldName;
                $thematicField->code = $thematicFieldData->code;
                $thematicField->macroAreas = [];
                $dimension->thematicFields[] = $thematicField;
                foreach ((array)$thematicFieldData->macroAreas as $macroAreaName => $macroAreaData) {
                    $macroArea = new MacroArea();
                    $macroArea->name = $macroAreaName;
                    $macroArea->code = $macroAreaData->code;
                    $macroArea->expertise = [];
                    $thematicField->macroAreas[] = $macroArea;
                    foreach ((array)$macroAreaData->expertise as $expertiseName => $expertiseData) {
                        $expertise = new Expertise();
                        $expertise->name = $expertiseName;
                        $expertise->code = $expertiseData->code;
                        $expertise->dimension = $dimensionName;
                        $expertise->score = $expertiseData->level;
                        $macroArea->expertise[] = $expertise;
                    }
                }
            }
            $passport->dimensions[] = $dimension;
        }
        return $passport;
    }

    /**
     * @var Dimension[]
     */
    public array $dimensions;
}
