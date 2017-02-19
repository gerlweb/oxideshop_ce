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
 * @copyright (C) OXID eSales AG 2003-2016
 * @version       OXID eShop CE
 */
namespace OxidEsales\EshopCommunity\Core\Autoload;

/**
 * This is an autoloader that performs several tricks to provide class aliases
 * for the new namespaced classes.
 *
 * The aliases are provided by a class map provider. But it is not sufficient
 * to
 */
class BcAliasAutoloader
{

    private $classMapProvider;
    private $backwardsCompatibilityClassMap;
    private $reverseBackwardsCompatibilityClassMap; // real class name => lowercase(old class name)
    private $virtualClassMap; // virtual class name => real class name
    private $composerAutoloader;

    public function __construct(\Composer\Autoload\ClassLoader $composerAutoloader)
    {
        $this->composerAutoloader = $composerAutoloader;
        $classMap = include_once __DIR__ . DIRECTORY_SEPARATOR . 'BackwardsCompatibilityClassMap.php';
        $this->backwardsCompatibilityClassMap = array_map('strtolower', $classMap);
    }

    /**
     * @param string $class
     *
     * @return null
     */
    public function autoload($class)
    {
        // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__  . ' TRYING TO LOAD ' . $class . PHP_EOL;

        $bcAlias = null;
        $virtualAlias = null;
        $realClass = null;

        if ($this->isRealClassRequest($class)) {
            $search = ['OxidEsales\\EshopCommunity\\', 'OxidEsales\\EshopProfessional\\', 'OxidEsales\\EshopEnterprise\\',];
            $replace = ['OxidEsales\\Eshop\\'];
            $virtualAlias = str_replace($search, $replace, $class);
            if (array_key_exists($virtualAlias, $this->backwardsCompatibilityClassMap)) {
                $this->composerAutoloader->loadClass($class);
                $realClass = '\\' . $class;
                $bcAlias = $this->backwardsCompatibilityClassMap[$virtualAlias];
                $this->createClassAlias($realClass, $bcAlias);
                $this->createClassAlias($realClass, $virtualAlias);
            }

            return false;
        }

        if ($this->isBcAliasRequest($class)) {
            $bcAlias = $class;
            $virtualAlias = $this->getVirtualAliasForBcAlias($class);
        }

        if ($this->isVirtualClassRequest($class)) {
            $virtualAlias = $class;
            $bcAlias = $this->getBcAliasForVirtualAlias($class);
        }

        if (!$realClass = $this->getRealClassForVirtualAlias($virtualAlias)) {
            return false;
        }

        $this->forceClassLoading($realClass);
        $this->createClassAlias($realClass, $bcAlias);
        $this->createClassAlias($realClass, $virtualAlias);

        return true; // Implies also generating of $bcAlias
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function isRealClassRequest($class)
    {
        $pattern = '/^(?i:oxidesales\\\\eshopcommunity|oxidesales\\\\eshopprofessional|oxidesales\\\\eshopenterprise)/';
        $result = preg_match($pattern, $class) === 1 ? true : false;

        return $result;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isBcAliasRequest($class)
    {
        $classMap = $this->getBackwardsCompatibilityClassMap();

        return in_array(strtolower($class), $classMap);
    }

    private function getVirtualAliasForBcAlias($class)
    {
        $classMap = array_flip($this->getBackwardsCompatibilityClassMap());

        return $classMap[strtolower($class)];
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isVirtualClassRequest($class)
    {
        return strpos($class, 'OxidEsales\\Eshop\\') === 0;
    }

    private function getBcAliasForVirtualAlias($class)
    {
        $classMap = $this->getBackwardsCompatibilityClassMap();
        if (key_exists($class, $classMap)) {
            return $classMap[$class];
        }
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private function getRealClassForVirtualAlias($class)
    {
        $realClass = '';
        $virtualClassMap = $this->getVirtualClassMap();

        if (key_exists($class, $virtualClassMap)) {
            $realClass = $virtualClassMap[$class];
        }

        return $realClass;
    }

    /**
     * @param string $class
     */
    private function forceClassLoading($class)
    {
        // Calling class_exists or interface_exists will trigger the autoloader
        class_exists($class);
        interface_exists($class);
    }

    /**
     * @return \OxidEsales\EshopCommunity\Core\ClassMapProvider
     */
    private function getClassMapProvider()
    {
        if (!$this->classMapProvider) {
            $editionSelector = new \OxidEsales\EshopCommunity\Core\Edition\EditionSelector();
            $this->classMapProvider = new \OxidEsales\EshopCommunity\Core\ClassMapProvider($editionSelector);
        }

        return $this->classMapProvider;
    }

    /**
     * @return array
     */
    private function getBackwardsCompatibilityClassMap()
    {
        return $this->backwardsCompatibilityClassMap;
    }

    /**
     * @return array
     */
    private function getReverseClassMap()
    {
        if (!$this->reverseBackwardsCompatibilityClassMap) {
            $this->reverseBackwardsCompatibilityClassMap = array_flip($this->getBackwardsCompatibilityClassMap());
        }

        return $this->reverseBackwardsCompatibilityClassMap;
    }

    /**
     * @return array
     */
    private function getVirtualClassMap()
    {
        if (!$this->virtualClassMap) {
            $this->virtualClassMap = $this->getClassMapProvider()->getOverridableVirtualNamespaceClassMap();
        }

        return $this->virtualClassMap;
    }

    /**
     * @return array
     */
    private function getDeclaredClasses()
    {
        return array_merge(get_declared_classes(), get_declared_interfaces(), get_declared_traits());
    }

    /**
     * @param $realClass
     * @param $alias
     */
    private function createClassAlias($realClass, $alias)
    {
        if ($alias && !in_array(strtolower($alias), $this->getDeclaredClasses())) {
            class_alias($realClass, $alias);
        }
    }

}
