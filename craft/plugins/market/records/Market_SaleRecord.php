<?php

namespace Craft;

/**
 * Class Market_SaleRecord
 *
 * @property int                        id
 * @property string                     name
 * @property string                     description
 * @property DateTime                   dateFrom
 * @property DateTime                   dateTo
 * @property string                     discountType
 * @property float                      discountAmount
 * @property bool                       allGroups
 * @property bool                       allProducts
 * @property bool                       allProductTypes
 * @property bool                       enabled
 *
 * @property Market_ProductRecord[]     products
 * @property Market_ProductTypeRecord[] productTypes
 * @property UserGroupRecord[]          groups
 * @package Craft
 */
class Market_SaleRecord extends BaseRecord
{
    const TYPE_PERCENT = 'percent';
    const TYPE_FLAT = 'flat';

    public function getTableName()
    {
        return 'market_sales';
    }

    public function defineRelations()
    {
        return [
            'groups'       => [
                static::MANY_MANY,
                'UserGroupRecord',
                'market_sale_usergroups(saleId, userGroupId)'
            ],
            'products'     => [
                static::MANY_MANY,
                'Market_ProductRecord',
                'market_sale_products(saleId, productId)'
            ],
            'productTypes' => [
                static::MANY_MANY,
                'Market_ProductTypeRecord',
                'market_sale_producttypes(saleId, productTypeId)'
            ],
        ];
    }

    protected function defineAttributes()
    {
        return [
            'name'            => [AttributeType::Name, 'required' => true],
            'description'     => AttributeType::Mixed,
            'dateFrom'        => AttributeType::DateTime,
            'dateTo'          => AttributeType::DateTime,
            'discountType'    => [
                AttributeType::Enum,
                'values'   => [self::TYPE_PERCENT, self::TYPE_FLAT],
                'required' => true
            ],
            'discountAmount'  => [
                AttributeType::Number,
                'decimals' => 5,
                'required' => true
            ],
            'allGroups'       => [
                AttributeType::Bool,
                'required' => true,
                'default'  => 0
            ],
            'allProducts'     => [
                AttributeType::Bool,
                'required' => true,
                'default'  => 0
            ],
            'allProductTypes' => [
                AttributeType::Bool,
                'required' => true,
                'default'  => 0
            ],
            'enabled'         => [
                AttributeType::Bool,
                'required' => true,
                'default'  => 1
            ],
        ];
    }

}