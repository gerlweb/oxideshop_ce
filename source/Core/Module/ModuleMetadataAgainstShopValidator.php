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
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop CE
 */

namespace OxidEsales\EshopCommunity\Core\Module;

use oxModule;
use oxModuleList;
use oxModuleInstaller;
use OxidEsales\Eshop\Core\Contract\IModuleValidator;

/**
 * Module metadata equivalence with saved shop configuration validator class.
 * Validates metadata contents and checks if it was not changed after module activation.
 *
 * @internal Do not make a module extension for this class.
 * @see      http://oxidforge.org/en/core-oxid-eshop-classes-must-not-be-extended.html
 */
class ModuleMetadataAgainstShopValidator implements IModuleValidator
{

    /**
     * Validates module metadata.
     * Return true if module metadata is valid.
     * Return false if module metadata is not valid, or if metadata file does not exist.
     *
     * @param oxModule $oModule object to validate metadata.
     *
     * @return bool
     */
    public function validate(\OxidEsales\EshopCommunity\Core\Module\Module $oModule)
    {

        $blModuleExtensionsMatchShopInformation = $this->_moduleExtensionsInformationExistsInShop($oModule);
        $blModuleInformationMatchShopInformation = $blModuleExtensionsMatchShopInformation
                                                   && $this->_moduleFilesInformationExistInShop($oModule);
        $blModuleInformationMatchShopInformation = $blModuleInformationMatchShopInformation
                                                   && $this->_moduleHasAllExtensions($oModule);
        $blModuleInformationMatchShopInformation = $blModuleInformationMatchShopInformation
                                                   && $this->_moduleHasAllFiles($oModule);

        return $blModuleInformationMatchShopInformation;
    }

    /**
     * Check if all module extensions exists in shop information.
     *
     * @param oxModule $oModule module object
     *
     * @return bool
     */
    private function _moduleExtensionsInformationExistsInShop(\OxidEsales\EshopCommunity\Core\Module\Module $oModule)
    {
        $aModuleExtensions = $oModule->getExtensions();

        /** @var oxModuleInstaller $oModuleInstaller */
        $oModuleInstaller = oxNew('oxModuleInstaller');
        $aShopInformationAboutModulesExtendedClasses = $oModuleInstaller->getModulesWithExtendedClass();

        foreach ($aModuleExtensions as $sExtendedClassName => $sModuleExtendedClassPath) {
            $aExtendedClassInfo = $aShopInformationAboutModulesExtendedClasses[$sExtendedClassName];
            if (is_null($aExtendedClassInfo) || !is_array($aExtendedClassInfo)) {
                return false;
            }
            if (!in_array($sModuleExtendedClassPath, $aExtendedClassInfo)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if all module files exists in shop.
     *
     * @param oxModule $oModule module object
     *
     * @return bool
     */
    private function _moduleFilesInformationExistInShop(\OxidEsales\EshopCommunity\Core\Module\Module $oModule)
    {
        $aModuleFiles = $oModule->getFiles();

        /** @var oxModuleList $oModuleList */
        $oModuleList = oxNew('oxModuleList');
        $aShopInformationAboutModulesFiles = $oModuleList->getModuleFiles();

        $aMissingFiles = array_diff($aModuleFiles, $aShopInformationAboutModulesFiles);

        return (count($aMissingFiles)) === 0;
    }

    /**
     * Check if all module files exists by shop information.
     *
     * @param oxModule $oModule module object
     *
     * @return bool
     */
    private function _moduleHasAllExtensions(\OxidEsales\EshopCommunity\Core\Module\Module $oModule)
    {
        return true;
    }

    /**
     * Check if all PHP files exists by shop information.
     *
     * @param oxModule $oModule module object
     *
     * @return bool
     */
    private function _moduleHasAllFiles(\OxidEsales\EshopCommunity\Core\Module\Module $oModule)
    {
        return true;
    }
}
