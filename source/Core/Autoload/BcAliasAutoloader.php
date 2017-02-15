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
    private $classMap;
    private $reverseClassMap; // real class name => lowercase(old class name)
    private $virtualClassMap; // virtual class name => real class name

    private $skipClasses = [];

    /**
     * @param string $class
     *
     * @return null
     */
    public function autoload($class)
    {

        if ($this->isSkipClass($class)) {
            echo __CLASS__ . '::' . __FUNCTION__ . ' SKIPPED ' . $class . PHP_EOL;
            return;
        }

        if ($this->isBcAliasRequest($class)) {
            $this->createBcAlias($class);

            return;
        }

        if ($this->isRealClassRequest($class)) {
            $this->createAliasForRealClass($class);

            return;
        }

        if ($this->isVirtualClassRequest($class)) {
            $realClass = $this->getRealClassForVirtualClass($class);
            if ($this->isRealClassRequest($class)) {
                $this->createAliasForRealClass($realClass);
            }
        }
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isBcAliasRequest($class)
    {
        $classMap = $this->getClassMap();

        return key_exists(strtolower($class), $classMap);
    }

    /**
     * @param string $class
     */
    private function createBcAlias($class)
    {
        $classMap = $this->getClassMap();
        $realClass = $classMap[strtolower($class)];
        $this->forceClassLoading($realClass);
        class_alias($realClass, $class);
        echo __CLASS__ . '::' . __FUNCTION__ . ' ALIAS CREATED ' . $realClass .' - '. $class . PHP_EOL;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function isRealClassRequest($class)
    {
        $reverseClassMap = $this->getReverseClassMap();

        return key_exists($class, $reverseClassMap);
    }

    /**
     * @param string $class
     */
    private function createAliasForRealClass($class)
    {
        $classMap = $this->getReverseClassMap();
        $alias = $classMap[$class];
        $this->forceClassLoading($class);
        class_alias($class, $alias);
        echo __CLASS__ . '::' . __FUNCTION__ . ' ALIAS CREATED ' . $class . ' - ' . $alias. PHP_EOL;
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
        if (!class_exists($class) and !interface_exists($class)) {
            $this->addSkipClass($class);
            spl_autoload_call($class);
            $this->removeSkipClass($class);
        }
        if (!class_exists($class) and !interface_exists($class)) {
            throw new Exception("Could not load class $class");
        }
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
    private function getClassMap()
    {
        if (!$this->classMap) {
            $this->classMap = array_merge(
                $this->getClassMapProvider()->getOverridableClassMap(),
                $this->getClassMapProvider()->getNotOverridableClassMap()
            );
        }

        return $this->classMap;
    }

    /**
     * @return array
     */
    private function getReverseClassMap()
    {
        if (!$this->reverseClassMap) {
            $this->reverseClassMap = array_flip($this->getClassMap());
        }

        return $this->reverseClassMap;
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
        return in_array($class, $this->skipClasses);
    }

    /**
     * @param string $class
     */
    private function addSkipClass($class)
    {
        $this->skipClasses[] = $class;
    }

    /**
     * @param string $class
     */
    private function removeSkipClass($class)
    {
        unset($this->skipClasses[array_search($class, $this->skipClasses)]);
    }
}

spl_autoload_register([new BcAliasAutoloader(), 'autoload']);
