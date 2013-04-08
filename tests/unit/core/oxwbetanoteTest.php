<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @package   tests
 * @copyright (C) OXID eSales AG 2003-2013
 * @version OXID eShop CE
 * @version   SVN: $Id: oxwbetanoteTest.php 56456 13.4.8 13.19Z tadas.rimkus $
 */


require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';

/**
 * Testing oxvoucherserie class
 */
class Unit_Core_oxbetanoteTest extends OxidTestCase {

    /**
     * Initialize the fixture.
     *
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Provides links and expected links
     *
     * @return array
     */
    public function linkProvider()
    {
        return array(
            array( null, null ),
            array( 'http://testlink', 'http://testlink' ),
            array( '', '' )
        );
    }
    /**
     * @dataProvider linkProvider
     */
    public function testgetBetaNoteLink( $sValuetoSet, $sExpected )
    {
        $oBetaNote = new oxwBetaNote();

        $oBetaNote->setBetaNoteLink( $sValuetoSet );

        $this->assertEquals( $sExpected, $oBetaNote->getBetaNoteLink() );
    }
}