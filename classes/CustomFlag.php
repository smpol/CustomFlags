<?php

class CustomFlag extends ObjectModel
{
    public $id_flag;
    public $name;
    public $condition;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'custom_flags',
        'primary' => 'id_flag',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 255],
            'condition' => ['type' => self::TYPE_STRING, 'required' => false, 'size' => 255],
            'date_add' => ['type' => self::TYPE_DATE],
            'date_upd' => ['type' => self::TYPE_DATE],
        ],
    ];

    public static function getFlags()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'custom_flags`';
        return Db::getInstance()->executeS($sql);
    }

    public static function getFlag($id_flag)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'custom_flags` WHERE id_flag = ' . (int)$id_flag;
        return Db::getInstance()->getRow($sql);
    }

    public static function removeFlag($id_flag)
    {
        return Db::getInstance()->delete('custom_flags', 'id_flag = ' . (int)$id_flag);
    }

    public static function getProductFlags($id_product)
    {
        $sql = 'SELECT cf.* FROM `' . _DB_PREFIX_ . 'custom_flag_product` cfp
                INNER JOIN `' . _DB_PREFIX_ . 'custom_flags` cf ON cfp.id_flag = cf.id_flag
                WHERE cfp.id_product = ' . (int)$id_product;
        return Db::getInstance()->executeS($sql);
    }

    public static function assignFlagToProduct($id_flag, $id_product)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'custom_flag_product` (id_flag, id_product) VALUES (' . (int)$id_flag . ', ' . (int)$id_product . ')';
        return Db::getInstance()->execute($sql);
    }

    public static function removeAllFlagsFromProduct($id_product)
    {
        return Db::getInstance()->delete('custom_flag_product', 'id_product = ' . (int)$id_product);
    }

    public static function getCategoryFlags($id_category)
    {
        $sql = 'SELECT cf.* FROM `' . _DB_PREFIX_ . 'custom_flag_category` cfc
                INNER JOIN `' . _DB_PREFIX_ . 'custom_flags` cf ON cfc.id_flag = cf.id_flag
                WHERE cfc.id_category = ' . (int)$id_category;
        return Db::getInstance()->executeS($sql);
    }

    public static function removeAllFlagsFromCategory($id_category)
    {
        return Db::getInstance()->delete('custom_flag_category', 'id_category = ' . (int)$id_category);
    }

    public static function assignFlagToCategory($id_flag, $id_category)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'custom_flag_category` (id_flag, id_category) VALUES (' . (int)$id_flag . ', ' . (int)$id_category . ')';
        return Db::getInstance()->execute($sql);
    }

    public static function updateFlag($id_flag, $name, $condition, $global = false)
    {
        return Db::getInstance()->update('custom_flags', [
            'name' => pSQL($name),
            'condition' => pSQL($condition),
            'is_global' => (bool)$global,
            'date_upd' => date('Y-m-d H:i:s'),
        ], 'id_flag = ' . (int)$id_flag);
    }

    public static function updateFlagGlobal($id_flag, $global = false)
    {
        return Db::getInstance()->update('custom_flags', [
            'is_global' => (bool)$global,
            'date_upd' => date('Y-m-d H:i:s'),
        ], 'id_flag = ' . (int)$id_flag);
    }

    public static function getGlobalFlags()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'custom_flags` WHERE is_global = 1';
        return Db::getInstance()->executeS($sql);
    }

    public static function checkIfCondition($condition, $countOfProducts)
    {
        $condition = substr($condition, strpos($condition, ' ') + 1);
        $operator = substr($condition, 0, 1);
        $condition = substr($condition, 2);
        $condition = (int)$condition;

        switch ($operator) {
            case '>':
                return $countOfProducts > $condition;
            case '<':
                return $countOfProducts < $condition;
            case '=':
                return $countOfProducts == $condition;
        }
        return false;
    }
}
