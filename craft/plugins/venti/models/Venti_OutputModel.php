<?php
namespace Craft;

class Venti_OutputModel extends BaseElementModel
{

    public function __toString()
    {
        return $this->name;
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'eid'       => AttributeType::Number,
            'eventid'       => AttributeType::Number,
            'startDate'     => AttributeType::DateTime,
            'endDate'       => AttributeType::DateTime,
            'allDay'        => AttributeType::Number,
            'repeat'        => AttributeType::Number,
            'rRule'         => AttributeType::String,
            'summary'       => AttributeType::String,
            'isrepeat'      => AttributeType::Number,
        ));
    }

}