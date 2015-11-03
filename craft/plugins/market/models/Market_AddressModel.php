<?php

namespace Craft;

use Market\Traits\Market_ModelRelationsTrait;

/**
 * Class Market_AddressModel
 *
 * @property int                  id
 * @property string               firstName
 * @property string               lastName
 * @property string               address1
 * @property string               address2
 * @property string               city
 * @property string               zipCode
 * @property string               phone
 * @property string               alternativePhone
 * @property string               company
 * @property string               stateName
 * @property int                  countryId
 * @property int                  stateId
 * @property int                  customerId
 *
 * @property Market_CountryModel  country
 * @property Market_StateModel    state
 * @property Market_CustomerModel customer
 *
 * @package Craft
 */
class Market_AddressModel extends BaseModel
{
    use Market_ModelRelationsTrait;

    /** @var int|string Either ID of a state or name of state if it's not present in the DB */
    public $stateValue;

    public function getCpEditUrl()
    {
        return UrlHelper::getCpUrl('market/customers/' . $this->customerId . '/addresses/' . $this->id);
    }

    public function getStateText()
    {
        return $this->stateName ?: ($this->stateId ? $this->state->name : '');
    }

    public function getCountryText()
    {
        if (!$this->countryId) {
            return "";
        }

        return craft()->market_country->getById($this->countryId)->name;
    }

    protected function defineAttributes()
    {
        return [
            'id'               => AttributeType::Number,
            'firstName'        => AttributeType::String,
            'lastName'         => AttributeType::String,
            'address1'         => AttributeType::String,
            'address2'         => AttributeType::String,
            'city'             => AttributeType::String,
            'zipCode'          => AttributeType::String,
            'phone'            => AttributeType::String,
            'alternativePhone' => AttributeType::String,
            'company'          => AttributeType::String,
            'stateName'        => AttributeType::String,
            'countryId'        => AttributeType::Number,
            'stateId'          => AttributeType::Number,
            'customerId'       => AttributeType::Number
        ];
    }
}