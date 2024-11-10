<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * CustomFlag module for PrestaShop
 * Add custom flags to your products
 *
 * Created by Michał Przysiężny
 */

use PrestaShopBundle\Form\FormBuilderModifier;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class customflags extends Module
{
    public function __construct()
    {
        require_once __DIR__ . '/classes/CustomFlag.php';

        $this->name = 'customflags';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.1';
        $this->author = 'Michał Przysiężny';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Custom Flags', [], 'Modules.Customflags.Customflags');
        $this->description = $this->trans('Add custom flags to your products.', [], 'Modules.Customflags.Customflags');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', [], 'Modules.Customflags.Customflags');
    }

    public function install()
    {
        return parent::install() &&
            $this->installDb() &&
            $this->registerHook('actionProductFormBuilderModifier') &&
            $this->registerHook('actionCategoryFormBuilderModifier') &&
            $this->registerHook('actionProductFlagsModifier') &&
            $this->registerHook('actionAfterUpdateProductFormHandler') &&
            $this->registerHook('actionAfterCreateProductFormHandler') &&
            $this->registerHook('actionProductDelete') &&
            $this->registerHook('actionCategoryAdd') &&
            $this->registerHook('actionCategoryDelete');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDb();
    }

    private function installDb()
    {
        $sqlFlags = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'custom_flags` (
            `id_flag` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `condition` VARCHAR(255) DEFAULT NULL,
            `is_global` BOOLEAN NOT NULL DEFAULT 0,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sqlProductFlags = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'custom_flag_product` (
            `id_flag_product` INT AUTO_INCREMENT PRIMARY KEY,
            `id_flag` INT NOT NULL,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_custom_product_flag` FOREIGN KEY (`id_flag`) REFERENCES `' . _DB_PREFIX_ . 'custom_flags`(`id_flag`) ON DELETE CASCADE,
            CONSTRAINT `fk_custom_product` FOREIGN KEY (`id_product`) REFERENCES `' . _DB_PREFIX_ . 'product`(`id_product`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sqlCategoryFlags = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'custom_flag_category` (
            `id_flag_category` INT AUTO_INCREMENT PRIMARY KEY,
            `id_flag` INT NOT NULL,
            `id_category` INT(10) UNSIGNED NOT NULL,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_custom_flag_category_flag` FOREIGN KEY (`id_flag`) REFERENCES `' . _DB_PREFIX_ . 'custom_flags`(`id_flag`) ON DELETE CASCADE,
            CONSTRAINT `fk_custom_category` FOREIGN KEY (`id_category`) REFERENCES `' . _DB_PREFIX_ . 'category`(`id_category`) ON DELETE CASCADE
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sqlFlags) && Db::getInstance()->execute($sqlProductFlags) && Db::getInstance()->execute($sqlCategoryFlags);
    }

    private function uninstallDb()
    {
        $dropTableProductFlags = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'custom_flag_product`;';
        $dropTableCategoryFlags = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'custom_flag_category`;';
        $dropTableFlags = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'custom_flags`;';

        Db::getInstance()->execute($dropTableProductFlags);
        Db::getInstance()->execute($dropTableCategoryFlags);
        Db::getInstance()->execute($dropTableFlags);

        return true;
    }

    public function getContent()
    {
        $output = '';

        /**
         * Add custom flag to database
         */
        if (Tools::isSubmit('submitAddFlag')) {
            $error = false;

            $flagName = Tools::getValue('flag_name');
            $condition = Tools::getValue('flag_condition_enabled');

            if (empty($flagName)) {
                $output .= $this->displayError($this->trans('Invalid flag name.', [], 'Modules.Customflags.Customflags'));
            } else {
                $flag = new CustomFlag();
                $flag->name = $flagName;

                if (!empty($condition)) {
                    $conditionAvaliableMode = ['Quantity'];
                    $conditionAvaliableOperator = ['>', '<', '='];
                    $conditionMode = Tools::getValue('flag_condition_mode');
                    $conditionOperator = Tools::getValue('flag_condition_operator');
                    if (!in_array($conditionMode, $conditionAvaliableMode)) {
                        $output .= $this->displayError($this->trans('Invalid condition mode.', [], 'Modules.Customflags.Customflags'));
                        $error = true;
                    } elseif (!in_array($conditionOperator, $conditionAvaliableOperator)) {
                        $output .= $this->displayError($this->trans('Invalid condition operator.', [], 'Modules.Customflags.Customflags'));
                        $error = true;
                    } else {
                        $flag->condition = $conditionMode . " " . $conditionOperator . " " . (int)Tools::getValue('flag_condition_value');
                    }
                }

                if (!$error) {
                    if ($flag->add()) {
                        $output .= $this->displayConfirmation($this->trans('Flag has been successfully added.', [], 'Modules.Customflags.Customflags'));
                    } else {
                        $output .= $this->displayError($this->trans('An error occurred while adding the flag.', [], 'Modules.Customflags.Customflags'));
                    }
                }
            }
        }

        /**
         * Edit custom flag
         */
        if (Tools::isSubmit('submitEditFlag')) {
            $error = false;
            $idFlag = (int)Tools::getValue('flag_id');
            $flagName = Tools::getValue('flag_name');
            $condition = Tools::getValue('flag_condition_enabled');

            if (empty($flagName)) {
                $output .= $this->displayError($this->trans('Invalid flag name.', [], 'Modules.Customflags.Customflags'));
            } else {
                $flag = new CustomFlag();
                $flag->id_flag = $idFlag;
                $flag->name = $flagName;

                if (!empty($condition)) {
                    $conditionAvaliableMode = ['Quantity'];
                    $conditionAvaliableOperator = ['>', '<', '='];

                    $conditionMode = Tools::getValue('flag_condition_mode');
                    $conditionOperator = Tools::getValue('flag_condition_operator');
                    if (!in_array($conditionMode, $conditionAvaliableMode)) {
                        $output .= $this->displayError($this->trans('Invalid condition mode.', [], 'Modules.Customflags.Customflags'));
                        $error = true;
                    } elseif (!in_array($conditionOperator, $conditionAvaliableOperator)) {
                        $output .= $this->displayError($this->trans('Invalid condition operator.', [], 'Modules.Customflags.Customflags'));
                        $error = true;
                    } else {
                        $flag->condition = $conditionMode . " " . $conditionOperator . " " . (int)Tools::getValue('flag_condition_value');
                    }
                }

                if (!$error) {
                    if ($flag->updateFlag($flag->id_flag, $flag->name, $flag->condition)) {
                        $output .= $this->displayConfirmation($this->trans('Flag has been successfully updated.', [], 'Modules.Customflags.Customflags'));
                    } else {
                        $output .= $this->displayError($this->trans('An error occurred while updating the flag.', [], 'Modules.Customflags.Customflags'));
                    }
                }
            }
        }

        /**
         * Delete custom flag
         */
        if (Tools::isSubmit('submitDeleteFlag')) {
            $idFlag = (int)Tools::getValue('flag_id');
            $flag = new CustomFlag();

            if (!$flag->getFlag($idFlag)) {
                $output .= $this->displayError($this->trans('Flag does not exist.', [], 'Modules.Customflags.Customflags'));
            } else {
                if ($flag->removeFlag($idFlag)) {
                    $output .= $this->displayConfirmation($this->trans('Flag has been successfully deleted.', [], 'Modules.Customflags.Customflags'));
                } else {
                    $output .= $this->displayError($this->trans('An error occurred while deleting the flag.', [], 'Modules.Customflags.Customflags'));
                }
            }
        }

        /**
         * Set flag as global
         */
        if (Tools::isSubmit('submitUnsetGlobalFlag')) {
            $idFlag = (int)Tools::getValue('flag_id');

            $flag = new CustomFlag();
            if (!$flag->getFlag($idFlag)) {
                $output .= $this->displayError($this->trans('Flag does not exist.', [], 'Modules.Customflags.Customflags'));
            } else {
                if ($flag->updateFlagGlobal($idFlag, false)) {
                    $output .= $this->displayConfirmation($this->trans('Flag has been successfully unset as global.', [], 'Modules.Customflags.Customflags'));
                } else {
                    $output .= $this->displayError($this->trans('An error occurred while unsetting the flag as global.', [], 'Modules.Customflags.Customflags'));
                }
            }
        }

        /**
         * Unset flag as global
         */
        if (Tools::isSubmit('submitSetGlobalFlag')) {
            $idFlag = (int)Tools::getValue('flag_id');

            $flag = new CustomFlag();
            if (!$flag->getFlag($idFlag)) {
                $output .= $this->displayError($this->trans('Flag does not exist.', [], 'Modules.Customflags.Customflags'));
            } else {
                if ($flag->updateFlagGlobal($idFlag, true)) {
                    $output .= $this->displayConfirmation($this->trans('Flag has been successfully set as global.', [], 'Modules.Customflags.Customflags'));
                } else {
                    $output .= $this->displayError($this->trans('An error occurred while setting the flag as global.', [], 'Modules.Customflags.Customflags'));
                }
            }
        }


        $flags = CustomFlag::getFlags();
        $this->context->smarty->assign([
            'flags' => $flags,
            'module' => $this,
            'link' => $this->context->link,
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * Form for attach custom flags to product
     */
    public function hookActionProductFormBuilderModifier(array $params)
    {
        $formBuilder = $params['form_builder'];
        $idProduct = (int)$params['id'];
        $assignedFlags = CustomFlag::getProductFlags($idProduct);
        $flags = CustomFlag::getFlags();

        $choices = [];
        foreach ($flags as $flag) {
            $choices[$flag['name']] = $flag['id_flag'];
        }

        $formBuilder->add('custom_flags', ChoiceType::class, [
            'label' => $this->trans('Choose custom flags', [], 'Modules.Customflags.Customflags'),
            'choices' => $choices,
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'data' => array_column($assignedFlags, 'id_flag'),
        ]);
    }

    /**
     * Save custom flags assigned to product
     */
    public function hookActionAfterUpdateProductFormHandler($params)
    {
        $this->processFlags($params);
    }

    public function hookActionAfterCreateProductFormHandler($params)
    {
        $this->processFlags($params);
    }

    private function processFlags($params)
    {
        $idProduct = (int)$params['id'];
        $formData = $params['form_data'];

        CustomFlag::removeAllFlagsFromProduct($idProduct);

        if (!empty($formData['custom_flags'])) {
            foreach ($formData['custom_flags'] as $idFlag) {
                CustomFlag::assignFlagToProduct($idFlag, $idProduct);
            }
        }
    }

    /**
     * Show custom flags on product
     */
    public function hookActionProductFlagsModifier($params)
    {
        $product = $params['product'];
        $flags = &$params['flags'];

        /**
         * @var $productCount int - count of product in stock
         */
        $productCount = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);

        /**
         * Get all global flags and check if they should be displayed
         */
        $customGlobalFlags = CustomFlag::getGlobalFlags();
        if (!empty($customGlobalFlags)) {
            foreach ($customGlobalFlags as $customFlag) {
                if ($customFlag['condition'] != null) {
                    if (CustomFlag::checkIfCondition($customFlag['condition'], $productCount)) {
                        $flags[] = [
                            'type' => 'custom',
                            'label' => $customFlag['name'],
                        ];
                    }
                } else {
                    $flags[] = [
                        'type' => 'custom',
                        'label' => $customFlag['name'],
                    ];
                }
            }
        }

        /**
         * Get all custom flags assigned to product and check if they should be displayed
         */
        $idProduct = (int)$product['id_product'];
        $customFlags = CustomFlag::getProductFlags($idProduct);

        if (!empty($customFlags)) {
            foreach ($customFlags as $customFlag) {
                if ($customFlag['condition'] != null) {
                    if (CustomFlag::checkIfCondition($customFlag['condition'], $productCount)) {
                        $flags[] = [
                            'type' => 'custom',
                            'label' => $customFlag['name'],
                        ];
                    }
                } else {
                    $flags[] = [
                        'type' => 'custom',
                        'label' => $customFlag['name'],
                    ];
                }
            }
        }

        /**
         * Get all custom flags assigned to category and check if they should be displayed
         */
        $categories = Product::getProductCategories($idProduct);

        if (!empty($categories)) {
            foreach ($categories as $idCategory) {
                $categoryFlags = CustomFlag::getCategoryFlags($idCategory);

                if (!empty($categoryFlags)) {
                    foreach ($categoryFlags as $categoryFlag) {
                        {
                            if ($categoryFlag['condition'] != null) {
                                if (CustomFlag::checkIfCondition($categoryFlag['condition'], $productCount)) {
                                    $flags[] = [
                                        'type' => 'custom',
                                        'label' => $categoryFlag['name'],
                                    ];
                                }
                            } else {
                                $flags[] = [
                                    'type' => 'custom',
                                    'label' => $categoryFlag['name'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        /**
         * Remove duplicates from flags array
         */
        $customFlags = array_filter($flags, function ($item) {
            return isset($item['type']) && $item['type'] === 'custom';
        });
        
        $uniqueCustomFlags = array_unique($customFlags, SORT_REGULAR);
        $flags = array_merge(
            array_filter($flags, function ($item) {
                return !(isset($item['type']) && $item['type'] === 'custom');
            }),
            $uniqueCustomFlags
        );

    }

    /**
     * Remove all flags assigned to product
     */
    public function hookActionProductDelete($params)
    {
        $idProduct = (int)$params['id_product'];
        CustomFlag::removeAllFlagsFromProduct($idProduct);
    }

    /**
     * Form for attach custom flags to category
     */
    public function hookActionCategoryFormBuilderModifier(array $params)
    {
        $formBuilder = $params['form_builder'];
        $idCategory = (int)$params['id'];
        $assignedFlags = CustomFlag::getCategoryFlags($idCategory);
        $flags = CustomFlag::getFlags();

        $choices = [];
        foreach ($flags as $flag) {
            $choices[$flag['name']] = $flag['id_flag'];
        }

        $formBuilder->add('custom_flags', ChoiceType::class, [
            'label' => $this->trans('Choose custom flags', [], 'Modules.Customflags.Customflags'),
            'choices' => $choices,
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'data' => array_column($assignedFlags, 'id_flag'),
        ]);

        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($idCategory) {
            $data = $event->getData();
            $flags = $data['custom_flags'] ?? [];

            if ($idCategory) {
                CustomFlag::removeAllFlagsFromCategory($idCategory);

                foreach ($flags as $idFlag) {
                    CustomFlag::assignFlagToCategory($idFlag, $idCategory);
                }
            } else {
                $this->context->customFlags = $data['custom_flags'];
            }
        });
    }

    /**
     * Save custom flags assigned to created category
     */
    public function hookActionCategoryAdd(array $params)
    {
        $category = $params['category'];
        $idCategory = (int)$category->id;

        $flags = $this->context->customFlags;

        if (!empty($flags) && is_array($flags)) {
            foreach ($flags as $idFlag) {
                CustomFlag::assignFlagToCategory($idFlag, $idCategory);
            }
        }
        $this->context->customFlags = null;
    }

    /**
     * Remove all flags assigned to category
     */
    public function hookActionCategoryDelete($params)
    {
        $idCategory = $params['category']->id;
        CustomFlag::removeAllFlagsFromCategory($idCategory);
    }
}