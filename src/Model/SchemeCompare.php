<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Model;

/**
 * Model representing the compare result of two Qualification Schemes
 */
class SchemeCompare
{
    /**
     * One of the two compared Qualification Schemes.
     *
     * @var Scheme
     */
    public Scheme $scheme;

    /**
     * One of the two compared Qualification Schemes.
     *
     * @var Scheme
     */
    public Scheme $opponentScheme;

    /**
     * The result of the compare represented in an array. The structure of the array is as follows:
     * [
     *  {Name of Dimension}: [
     *      "name": {Name of Dimension},
     *      "thematicFields": [
     *          {Name of Thematic Field}: [
     *              "name": {Name of Thematic Field},
     *              "code": {Code of Thematic Field},
     *              "macroAreas": [
     *                  {Name of Macro Area}: [
     *                      "name": {Name of Macro Area},
     *                      "code": {Code of Macro Area},
     *                      "expertise": [
     *                          {Name of Expertise}: [
     *                              "name": {Name of Expertise},
     *                              "code": {Code of Expertise},
     *                              "expertiseId": {Id of Expertise in the api database},
     *                              "level": [
     *                                  {accessible level is Scheme},
     *                                  {accessible level is Scheme},
     *                                  ...
     *                              ],
     *                              ?"opponentLevel": [
     *                                  {accessible level is opponent Scheme},
     *                                  {accessible level is opponent Scheme},
     *                                  ...
     *                              ]
     *                          ],
     *                          {Name of Expertise}: [
     *                              ...
     *                          ],
     *                          ...
     *                      ]
     *                  ],
     *                  {Name of Macro Area}: [
     *                      ...
     *                  ],
     *                  ...
     *              ]
     *          ],
     *          {Name of Thematic Field}: [
     *              ...
     *          ],
     *          ...
     *      ]
     *  ],
     *  {Name of Dimension}: [
     *      ...
     *  ],
     *  ...
     * ]
     *
     * @var array
     */
    public array $compare;
}
