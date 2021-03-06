<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Validator;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;

/**
 * @internal
 */
interface ModuleSettingValidatorInterface
{
    /**
     * @param ModuleSetting $moduleSetting
     * @param string        $moduleId
     * @param int           $shopId
     */
    public function validate(ModuleSetting $moduleSetting, string $moduleId, int $shopId);

    /**
     * @param ModuleSetting $moduleSetting
     * @return bool
     */
    public function canValidate(ModuleSetting $moduleSetting): bool;
}
