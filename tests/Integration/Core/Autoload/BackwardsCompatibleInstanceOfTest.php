<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 * @version       OXID eShop CE
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Core\Autoload;

use OxidEsales\TestingLibrary\UnitTestCase;

class BackwardsCompatibleInstanceOfTest extends UnitTestCase
{

    /**
     * Test the backwards compatibility of class instances created with oxNew
     *
     * @dataProvider dataProviderTestInstanceOfOxNewClass
     *
     * @param string $realClassName
     * @param string $virtualClassName
     * @param string $backwardsCompatibleClassAlias
     * @param string $message
     *
     */
    public function testInstanceOfOxNewClass($realClassName, $virtualClassName, $backwardsCompatibleClassAlias, $message)
    {
        $object = oxNew($backwardsCompatibleClassAlias);

        $className = $backwardsCompatibleClassAlias;
        $this->assertInstanceOf($className, $object, $message);

        $className = $realClassName;
        $this->assertInstanceOf($className, $object, $message);

        $className = $virtualClassName;
        $this->assertInstanceOf($className, $object, $message);
    }

    public function dataProviderTestInstanceOfOxNewClass()
    {
        return [
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'virtualClassName'              => \OxidEsales\Eshop\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => 'oxArticle',
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using CamelCase class name)'
            ],
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'virtualClassName'              => \OxidEsales\Eshop\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => \oxArticle::class,
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using class constant)'
            ],
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'virtualClassName'              => \OxidEsales\Eshop\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => 'oxarticle',
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using lower case class name)'
            ],
        ];
    }

    /**
     * Test the backwards compatibility of class instances created with new
     *
     * @dataProvider dataProviderTestInstanceOfNewClass
     *
     * @param string $realClassName
     * @param string $virtualClassName
     * @param string $backwardsCompatibleClassAlias
     * @param string $message
     *
     */
    public function testInstanceOfNewClass($realClassName, $backwardsCompatibleClassAlias, $message)
    {

        $object = new $backwardsCompatibleClassAlias();

        $className = $backwardsCompatibleClassAlias;
        $this->assertInstanceOf($className, $object, $message);

        $className = $realClassName;
        $this->assertInstanceOf($className, $object, $message);
    }

    public function dataProviderTestInstanceOfNewClass()
    {
        return [
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => 'oxArticle',
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using CamelCase class name)'
            ],
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => \oxArticle::class,
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using class constant)'
            ],
            [
                'realClassName'                 => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'backwardsCompatibleClassAlias' => 'oxarticle',
                'message'                       => 'Object created with the backwards compatible class name must be an instance of the class name (using lower case class name)'
            ],
        ];
    }
}
