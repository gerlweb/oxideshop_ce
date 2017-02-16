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

    private $skipClasses = [];

    /**
     * @param string $class
     *
     * @return null
     */
    public function autoload($class)
    {
        // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__  . ' TRYING TO LOAD ' . $class . PHP_EOL;

        if ($this->isBcAliasRequest($class)) {
            // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' LOADED via isBcAliasRequest ' . $class . PHP_EOL;
            $this->createBcAlias($class);

            return true;
        }

        if ($this->isVirtualClassRequest($class)) {
            // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' LOADED via isVirtualClassRequest ' . $class . PHP_EOL;
            $realClass = $this->getRealClassForVirtualClass($class);
            $this->createAliasForRealClass($realClass, $class);

            return true;
        }

        // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' NOT FOUND ' . $class . PHP_EOL;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isBcAliasRequest($class)
    {
        $classMap = $this->getBackwardsCompatibilityClassMap();

        return key_exists(strtolower($class), $classMap);
    }

    /**
     * @param string $backwardsCompatibleClassName
     */
    private function createBcAlias($backwardsCompatibleClassName)
    {
        $classMap = $this->getBackwardsCompatibilityClassMap();
        $virtualClassName = $classMap[strtolower($backwardsCompatibleClassName)];
        $this->forceClassLoading($virtualClassName);
        // The class will always be skipped as by the former method a class aliasing would (class_exists) have been tiggered
        if (!$this->isSkipClass($backwardsCompatibleClassName)) {
            // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' CREATE ALIAS ' . $virtualClassName . ' - ' . $backwardsCompatibleClassName . PHP_EOL;
            class_alias($virtualClassName, $backwardsCompatibleClassName);
        }
    }


    /**
     * @param string $realClass
     */
    private function createAliasForRealClass($realClass, $virtualClass)
    {
        $backwardsCompatibleClass = $this->getBcClassForVirtualClass($virtualClass);
        $this->forceClassLoading($realClass);
        if ($backwardsCompatibleClass) {
            // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' CREATE ALIAS ' . $realClass . ' - ' . $backwardsCompatibleClass . PHP_EOL;
            // Calling This triggers loading of the class
            class_alias($realClass, $backwardsCompatibleClass);
            // $this->addSkipClass($backwardsCompatibleClass);
        }
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isVirtualClassRequest($class)
    {
        $virtualClassMap = $this->getVirtualClassMap();

        return key_exists($class, $virtualClassMap);
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private function getRealClassForVirtualClass($class)
    {
        $virtualClassMap = $this->getVirtualClassMap();

        return $virtualClassMap[$class];
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
        if (!$this->backwardsCompatibilityClassMap) {
            $this->backwardsCompatibilityClassMap = array_merge(
                $this->getClassMapProvider()->getOverridableClassMap(),
                $this->getClassMapProvider()->getNotOverridableClassMap()
            );
        }

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
     * @param string $class
     *
     * @return bool
     */
    private function isSkipClass($class)
    {
        return in_array(strtolower($class), $this->skipClasses);
    }

    /**
     * @param string $class
     */
    private function addSkipClass($class)
    {
        $this->skipClasses[] = strtolower($class);
    }

    /**
     * @param string $class
     */
    private function removeSkipClass($class)
    {
        unset($this->skipClasses[array_search($class, $this->skipClasses)]);
    }

    /**
     * @param $virtualClass
     *
     * @return mixed|string
     */
    private function getBcClassForVirtualClass($virtualClass)
    {
        $alias = '';
        if (!empty($virtualClass)) {
            $classMap = $this->getReverseClassMap();
            if (array_key_exists($virtualClass, $classMap)) {
                $alias = $classMap[$virtualClass];

                return $alias;
            } else {
                // Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__ . ' virtual class not found in bc classmap ' . $virtualClass . PHP_EOL;

                return $alias;
            }
        }

        return $alias;
    }
}

// Uncomment to debug:  echo __CLASS__ . '::' . __FUNCTION__  . ' TRYING TO LOAD ' . $class . PHP_EOL;
spl_autoload_register([new BcAliasAutoloader(), 'autoload']);
