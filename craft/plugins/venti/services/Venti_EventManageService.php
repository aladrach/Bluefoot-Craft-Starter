<?php

/**
 * Venti by TippingMedia
 *
 * @package   Venti
 * @author    Adam Randlett
 * @copyright Copyright (c) 2015, TippingMedia
 */


namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'venti/vendor/autoload.php');

class Venti_EventManageService extends BaseApplicationComponent
{

    /**
     * Variables
     *
     */

    protected $eventRecord;
    protected $ruleHolder;
    protected $_placeholderElements;


    /**
     * Constructor
     *
     */

    public function __construct($eventRecord = null)
    {
        $this->eventRecord = $eventRecord;
        if (is_null($this->eventRecord))
        {
            $this->eventRecord = Venti_EventRecord::model();
        }

    }



    /**
     * Save rule options to ruleHolder model
     *
     * @return  bool
     */

    public function saveRuleOptions(Venti_RuleModel $model)
    {
      if($model->dates = $this->getRecurDates($model->getAttribute('startDate'),$model->getAttribute('rrule')))
      { //$transformer->getComputedArray()){
        $this->ruleHolder = $model;
        return true;
      }
      else
      {
        throw new Exception(Craft::t('Recurr array not created."'));
      }

    }




    public function getCriteria($attributes = null)
    {

      $elementType = $this->getElementType(ElementType::Entry);


      if (!$elementType)
      {
        throw new Exception(Craft::t('No element type exists by the type “{type}”.', array('type' => $type)));
      }

      return new Venti_CriteriaModel($attributes, $elementType);
    }



    /**
     * Get multiple events
     *
     * @return  array
     */
    public function allEvents($attributes = null)
    {
      $criteria = $this->getCriteria($attributes);
      return $criteria;
    }




     /**
     * Get specific event by its eventid
     *
     * @return  array
     */
    public function getEventById($eventId)
    {

      if (!$eventId)
      {
        return null;
      }
      else
      {
        $criteria = $this->getCriteria(array("id" => $eventId));
      }
      
      return $criteria->first();
    }



     /**
     * Get specific event by its eid
     *
     * @return  array
     */
    public function getEvent($eid)
    {
      if (!$eid)
      {
        return null;
      }
      else
      {
        $criteria = $this->getCriteria(array("eid" => $eid));
      }
      
      return $criteria->first();
    }



    /**
     * Get field type data based on eventid
     *
     * @return  array
     */
    public function getEventFieldData($id)
    {
      $event = array();
      $eventRecord = Venti_EventRecord::model();
      if ($record = $this->eventRecord->findByAttributes(array("eventid" => $id))) {

         $event  = array(
          'startDate'   => $record->startDate,
          'endDate'     => $record->endDate,
          'allDay'      => (bool) $record->allDay,
          'repeat'      => (bool) $record->repeat,
          'summary'     => $record->summary,
          'rRule'       => $record->rRule
          );

         return $event;
      }
    }




     /**
     * Get next event.
     * if recurring returns next possible date
     *
     * @return  array
     */
      public function nextEvent($eventId = null)
      {
        $now = new DateTime('now', new \DateTimeZone(craft()->getTimeZone()));
        if ($eventId == null)
        {
          $criteria = $this->getCriteria(
            array(
              "startDate" => array('>'.$now)
            )
          );
        }
        else
        {
          $criteria = $this->getCriteria(
            array(
              "startDate" => array('and','>'.$now),
              "id" => $eventId
            )
          );
        }

        return $criteria->first();
      }





    /**
   * Preps a {@link DbCommand} object for querying for elements, based on a given element criteria.
   *
   * @param Venti_CriteriaModel &$criteria     The events criteria model
   *
   * @return DbCommand|false The DbCommand object, or `false` if the method was able to determine ahead of time that
   *                         there’s no chance any elements are going to be found with the given parameters.
   */
    public function buildEventsQuery(Venti_CriteriaModel $criteria)
    {

      $elementType = $criteria->getElementType();
      if (!$elementType->isLocalized())
      {
        // The criteria *must* be set to the primary locale
        $criteria->locale = craft()->i18n->getPrimarySiteLocaleId();
      }
      else if (!$criteria->locale)
      {
        // Default to the current app locale
        $criteria->locale = craft()->language;
      }

      $query = craft()->db->createCommand()
        ->select('venti.startDate, venti.endDate, venti.allDay, venti.isrepeat, venti.eid, venti.eventid, venti.repeat, venti.rRule, venti.summary, elements.id, elements.type, elements.enabled, elements.archived, elements.dateCreated, elements.dateUpdated, elements_i18n.slug, elements_i18n.uri, elements_i18n.enabled AS localeEnabled')
        ->from('venti_events venti')
        ->join('elements elements', 'elements.id = venti.eventid')
        ->join('elements_i18n elements_i18n', 'elements_i18n.elementId = venti.eventid')
        ->where('elements_i18n.locale = :locale', array(':locale' => $criteria->locale))
        ->limit($criteria->limit)
        ->offset($criteria->offset)
        ->order($criteria->order);


        if ($elementType->hasContent())
        {
          $contentTable = 'content';

          if ($contentTable)
          {
            $contentCols = 'content.id AS contentId';

            if ($elementType->hasTitles())
            {
              $contentCols .= ', content.title';
            }

            $fieldColumns = $this->getContentFieldColumnsForElementsQuery($criteria);

            foreach ($fieldColumns as $column)
            {
              $contentCols .= ', content.'.$column['column'];
            }

            $query->addSelect($contentCols);
            $query->join($contentTable.' content', 'content.elementId = elements.id');
            $query->andWhere('content.locale = :locale');
          }
        }


      if ($elementType->hasTitles() && $criteria->title)
      {
        $query->andWhere(DbHelper::parseParam('content.title', $criteria->title, $query->params));
      }

      if($criteria->id)
      {
          $query->andWhere(DbHelper::parseParam('venti.eventid', $criteria->id, $query->params));
      }

      if($criteria->eid)
      {
          $query->andWhere(DbHelper::parseParam('venti.eid', $criteria->eid, $query->params));
      }

      if($criteria->isrepeat)
      {
          $query->andWhere(DbHelper::parseParam('venti.isrepeat', $criteria->isrepeat, $query->params));
      }

      if($criteria->startDate)
      {
        $query->andWhere(DbHelper::parseDateParam('venti.startDate', $criteria->startDate, $query->params));
      }

      if($criteria->endDate)
      {
        $query->andWhere(DbHelper::parseDateParam('venti.endDate', $criteria->endDate, $query->params));
      }

      if($criteria->summary)
      {
        $query->andWhere(DbHelper::parseParam('venti.summary', $criteria->summary, $query->params));
      }

      if ($criteria->slug)
      {
        $query->andWhere(DbHelper::parseParam('elements_i18n.slug', $criteria->slug, $query->params));
      }

      if ($criteria->uri)
      {
        $query->andWhere(DbHelper::parseParam('elements_i18n.uri', $criteria->uri, $query->params));
      }

      if ($criteria->localeEnabled)
      {
        $query->andWhere('elements_i18n.enabled = 1');
      }

      if ($criteria->dateCreated)
      {
        $query->andWhere(DbHelper::parseDateParam('elements.dateCreated', $criteria->dateCreated, $query->params));
      }

      if ($criteria->dateUpdated)
      {
        $query->andWhere(DbHelper::parseDateParam('elements.dateUpdated', $criteria->dateUpdated, $query->params));
      }

      if ($criteria->archived)
      {
        $query->andWhere('elements.archived = 1');
      }
      else
      {
        $query->andWhere('elements.archived = 0');

        if ($criteria->status)
        {
          $statusConditions = array();
          $statuses = ArrayHelper::stringToArray($criteria->status);

          foreach ($statuses as $status)
          {
            $status = StringHelper::toLowerCase($status);

            // Is this a supported status?
            if (in_array($status, array_keys($this->getStatuses())))
            {
              if ($status == BaseElementModel::ENABLED)
              {
                $statusConditions[] = 'elements.enabled = 1';
              }
              else if ($status == BaseElementModel::DISABLED)
              {
                $statusConditions[] = 'elements.enabled = 0';
              }
              else
              {
                $elementStatusCondition = $this->getElementQueryStatusCondition($query, $status);

                if ($elementStatusCondition)
                {
                  $statusConditions[] = $elementStatusCondition;
                }
                else if ($elementStatusCondition === false)
                {
                  return false;
                }
              }
            }
          }

          if ($statusConditions)
          {
            if (count($statusConditions) == 1)
            {
              $statusCondition = $statusConditions[0];
            }
            else
            {
              array_unshift($statusConditions, 'or');
              $statusCondition = $statusConditions;
            }

            $query->andWhere($statusCondition);
          }
        }
      }



      // Relational params
      // ---------------------------------------------------------------------

      if ($criteria->relatedTo)
      {
        $relationParamParser = new ElementRelationParamParser();
        $relConditions = $relationParamParser->parseRelationParam($criteria->relatedTo, $query);

        if ($relConditions === false)
        {
          return false;
        }

        $query->andWhere($relConditions);

        // If there's only one relation criteria and it's specifically for grabbing target elements, allow the query
        // to order by the relation sort order
        if ($relationParamParser->isRelationFieldQuery())
        {
          $query->addSelect('sources1.sortOrder');
        }
      }



      // Search
      // ---------------------------------------------------------------------

      if ($criteria->search)
      {
        $elementIds = $this->_getElementIdsFromQuery($query);
        $scoredSearchResults = ($criteria->order == 'score');
        $filteredElementIds = craft()->search->filterElementIdsByQuery($elementIds, $criteria->search, $scoredSearchResults);

        // No results?
        if (!$filteredElementIds)
        {
          return array();
        }

        $query->andWhere(array('in', 'venti.eventid', $filteredElementIds));

        if ($scoredSearchResults)
        {
          // Order the elements in the exact order that SearchService returned them in
          $query->order(craft()->db->getSchema()->orderByColumnValues('venti.eventid', $filteredElementIds));
        }
      }

      return $query;
    }


    /**
   * Returns an element’s URI for a given locale.
   *
   * @param int    $elementId The element’s ID.
   * @param string $localeId  The locale to search for the element’s URI in.
   *
   * @return string|null The element’s URI, or `null`.
   */
  public function getElementUriForLocale($elementId, $localeId)
  {
    return craft()->db->createCommand()
      ->select('uri')
      ->from('elements_i18n')
      ->where(array('elementId' => $elementId, 'locale' => $localeId))
      ->queryScalar();
  }

  /**
   * Returns the locales that a given element is enabled in.
   *
   * @param int $elementId The element’s ID.
   *
   * @return array The locales that the element is enabled in. If the element could not be found, an empty array
   *               will be returned.
   */
  public function getEnabledLocalesForElement($elementId)
  {
    return craft()->db->createCommand()
      ->select('locale')
      ->from('elements_i18n')
      ->where(array('elementId' => $elementId, 'enabled' => 1))
      ->queryColumn();
  }




    /**
   * @inheritDoc IElementType::getStatuses()
   *
   * @return array|null
   */
  public function getStatuses()
  {
    return array(
      BaseElementModel::ENABLED => Craft::t('Enabled'),
      BaseElementModel::DISABLED => Craft::t('Disabled')
    );
  }




  /**
   * @inheritDoc IElementType::getElementQueryStatusCondition()
   *
   * @param DbCommand $query
   * @param string    $status
   *
   * @return false|string|void
   */
  public function getElementQueryStatusCondition(DbCommand $query, $status)
  {
  }



    /**
     * Finds elements.
     *
     * @param Venti_CriteriaModel $criteria An events criteria model that defines the parameters for the elements
     *         we should be looking for.
     *
     * @return array The matched elements;
     */
    public function findElements($criteria = null)
    {
      $elements = array();
      $contentTable = 'content';
      $fieldColumns = $this->getContentFieldColumnsForElementsQuery($criteria);
      $query = $this->buildEventsQuery($criteria);

      if ($query) {
        
        if ($criteria->offset)
        {
          $query->offset($criteria->offset);
        }

        if ($criteria->limit)
        {
          $query->limit($criteria->limit);
        }

        $results = $query->queryAll();

        if ($results) {

          $locale = $criteria->locale;
          $elementType = $criteria->getElementType();

          foreach($results as $result)
          {
            // Do we have a placeholder for this elmeent?
            if (isset($this->_placeholderElements[$result['id']][$locale]))
            {
              $element = $this->_placeholderElements[$result['id']][$locale];
            }
            else
            {
              // Make a copy to pass to the onPopulateElement event
              $originalResult = array_merge($result);

              if ($contentTable)
              {
                // Separate the content values from the main element attributes
                $content = array(
                  'id'        => (isset($result['contentId']) ? $result['contentId'] : null),
                  'elementId' => $result['id'],
                  'locale'    => $locale,
                  'title'     => (isset($result['title']) ? $result['title'] : null)
                );

                unset($result['title']);

                if ($fieldColumns)
                {
                  foreach ($fieldColumns as $column)
                  {
                    // Account for results where multiple fields have the same handle, but from
                    // different columns e.g. two Matrix block types that each have a field with the
                    // same handle

                    $colName = $column['column'];
                    $fieldHandle = $column['handle'];

                    if (!isset($content[$fieldHandle]) || (empty($content[$fieldHandle]) && !empty($result[$colName])))
                    {
                      $content[$fieldHandle] = $result[$colName];
                    }

                    unset($result[$colName]);
                  }
                }
              }

              $result['locale'] = $locale;
              $element = $this->populateElementModel($result);

              // Was an element returned?
              if (!$element || !($element instanceof BaseElementModel))
              {
                continue;
              }

              if ($contentTable)
              {
                $element->setContent($content);
              }
            }

            $elements[] = $element;
          }
          //$elements = $this->populateModelsFromArray($results);
        }
      }

      return $elements;
    }




    /**
     * Elements transfered into array of Venti_OutputModel
     *
     * @param array
     *
     * @return array of Venti_OutputModels
     */

    protected function populateModelsFromArray(Array $array)
    {
        $models = array();

        foreach($array as $row)
        {
            $models[] = Venti_OutputModel::populateModel($row);
        }

        return $models;
    }


     /**
     * Elements transfered into array of Venti_OutputModel
     *
     * @param array
     *
     * @return array of Venti_OutputModels
     */

    protected function populateElementModel(Array $array)
    {
        $model = Venti_OutputModel::populateModel($array);
        return $model;
    }




    /**
     * Grabs subset of events array based on offset and number of items.
     * @return  array
     */

    protected function pagination($events,$filterAttributes)
    {
      
      $offset = intval($filterAttributes['page']['offset']);
      $howmany = intval($filterAttributes['page']['howmany']);
      $total = $offset + $howmany;

      $output = array();
      $i = 0;
      foreach ($events as $event)
      {
        if( $i >= $offset && $i < $total )
        {
          array_push($output,$event);
        }
        $i++; 
      }

      return $output;
    }




    /**
     * Applies time from date1 to date2. 
     * Repeat dates need endDate but same time as startDate
     *
     * @return  DateTime
     */

    private function sameDateNewTime($date1,$date2)
    {
      #
      # Extract date parts from $date1 and $date2
      $etar = date_parse($date1->format("H:i:s"));
      $star = date_parse($date2->format("Y-m-d"));

      #
      # Merge time from $date1 to date of $date2
      $dateTimeString = $star['year'] . '-' . $star['month'] . '-' . $star['day'] . ' ' . $etar['hour'] . ':' . $etar['minute'] . ':' . $etar['second'];

      #
      # Generate new Craft DatTime object
      $newCraftDateTime = new DateTime($dateTimeString, new \DateTimeZone(craft()->getTimeZone()));
      
      return $newCraftDateTime;
    }



    /**
     * Get dates array based on recur template. 
     *
     * @return  array
     */

    public function getRecurDates($start, $rrule)
    {

      $timezone = "UTC"; //"UTC"; //'America/New_York' 'America/Denver' craft()->getTimeZone() 

      //convert time back to selected timezone.
      $startDateString = new \DateTime($start->format('c'), new \DateTimeZone(craft()->getTimeZone()));
      //$startDateString = $start->format(DateTime::MYSQL_DATETIME, DateTime::UTC);
      //
      $rule = new \Recurr\Rule($rrule, $startDateString, null, $timezone);
      $transformer = new \Recurr\Transformer\ArrayTransformer();
      $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
      $transformerConfig->enableLastDayOfMonthFix();
      $transformer->setConfig($transformerConfig);

      return $transformer->transform($rule);
    }



    /**
     * Saving event data to eventRecord
     *
     * @return  bool
     */

    public function saveEventData($id, $attributes)
    {

      $model = new Venti_EventModel();
      $model->eventid = $id;
      $model->setAttributes($attributes);
      $timezone = craft()->getTimeZone();

      #
      # Validate's the model 
      if ($model->validate()) 
      {
         
      
        #
        # Convert startDate to UTC time for database
        $startDateString = $model->startDate->format(DateTime::MYSQL_DATETIME, DateTime::UTC );
        $model->startDate = DateTime::createFromFormat(DateTime::MYSQL_DATETIME, $startDateString, DateTime::UTC);
        

        #
        # Convert endDate to UTC time for database
        $startEndString = $model->endDate->format(DateTime::MYSQL_DATETIME, DateTime::UTC );
        $model->endDate = DateTime::createFromFormat(DateTime::MYSQL_DATETIME, $startEndString, DateTime::UTC);
        

        #
        # If there is no current event record at specific id create new record
        if (null === ($record = $this->eventRecord->findByAttributes(array("eventid" => $id)))) 
        {
          $record = $this->eventRecord->create();
        }

        #
        # Map model attributes to event record attributes
        $record->setAttributes($model->getAttributes(),false);


        if($record->save())
        {
          
          $model->setAttribute('eventid', $record->getAttribute('eventid'));

          
          #
          # If repeat checkbox is selected (exists in attributes array) save recurring dates
          # if not selected see if there are dates in dateRecord associated with event id
          # if there are delete them.

          if(array_key_exists('repeat',$attributes))
          {
            
            if($attributes['repeat'] == 1)
            {

              //$dates = $this->getRecurDates($model->getAttribute('startDate'),$model->getAttribute('rRule'));
              $this->saveRecurringDates($model);

            }

          }
          else
          {

            if($dates = $this->eventRecord->findByAttributes(array("eventid" => $id, "isrepeat" => 1)))
            {
               $this->eventRecord->deleteAllByAttributes(array("eventid" => $id, "isrepeat" => 1));
            }

          }
          
          return true;

        }
        else
        {

          $model->addErrors($record->getErrors());
          craft()->userSession->setError(Craft::t('Event not saved.'));
          return false;
        }

      }
      else
      {
        craft()->userSession->setError(Craft::t("Event dates can't be empty."));
        return false;
      }

    }





    /**
     * Save recurring dates to dateRecord 
     *
     */

    public function saveRecurringDates($model)
    {


      $id = $model->getAttribute('eventid');
      $startdate = $model->getAttribute('startDate');
      $rule = $model->getAttribute('rRule');
      $dates = $this->getRecurDates($startdate,$rule);
      $dates = $dates->toArray();
      


      if(null === ($record = $this->eventRecord->findByAttributes(array("eventid" => $id, "isrepeat" => 1))))
      {

        foreach ($dates as $key => $value)
        {
          // first occurrence already stored in content table.
           if ($key > 0) {
            //gets startdate from Recur\Recurrece object
            $date = $value->getStart(); //DateTime
            $record = $this->eventRecord->create();
            $record->setAttributes($model->getAttributes());
            $record->setAttribute('startDate',$date);
            $record->setAttribute('endDate', $this->sameDateNewTime($model->endDate, $date));
            $record->setAttribute('isrepeat', 1);
            $record->setAttribute('summary', $model->summary);
            $record->setAttribute('rRule', $model->rRule);
            $record->setAttribute('allDay', $model->allDay);
            

            if(!$record->save())
            {
              craft()->userSession->setError(Craft::t('Repeat events not saved.'));
            }
          }
        }

      }
      else
      {

        $this->eventRecord->deleteAllByAttributes(array("eventid" => $id, "isrepeat" => 1));
        foreach ($dates as $key => $value)
        {
          // first occurrence already stored in content table.
          if ($key > 0) {
            
            //gets startdate from Recur\Recurrece object
            $date = $value->getStart();
    
            $record = $this->eventRecord->create();
            $record->setAttributes($model->getAttributes());
            $record->setAttribute('eventid',$id);
            $record->setAttribute('startDate',$date);
            $record->setAttribute('endDate', $this->sameDateNewTime($model->endDate, $date));
            $record->setAttribute('isrepeat',1);
            $record->setAttribute('rRule', $model->rRule);
            $record->setAttribute('summary', $model->summary);
            $record->setAttribute('allDay', $model->allDay);

          
            if(!$record->save())
            {
              craft()->userSession->setError(Craft::t('Repeat events not re-saved.'));
            }
          }
        }
      
      }

    }



    /**
     * EventsTwigExtension method for grouping by date
     *
     * @return  array
     */

    public function groupByDate($arr, $item)
    {
      $groups = array();

      foreach ($arr as $key => $object)
      {
        $value = $this->getDateFormat($item, $object);
        $groups[$value][] = $object;
      }

      return $groups;
    }




    /**
     * Get date format for groupByDate grouping
     *
     * @return  string
     */

    protected function getDateFormat($item, $object)
    {
      $sdate = $object['startDate'];

      #
      # Group by day of month and year
      if($item === 'day')
      {
        return $sdate->format('M d Y');
      }

      #
      # Group by Month of year
      if($item === 'month')
      {
        return $sdate->format('F Y');
      }

      #
      # Group by year
      if($item === 'year')
      {
        return $sdate->format('Y');
      }

      #
      # Month Day Year Hour
      if($item === 'time')
      {
        return $sdate->format('M d Y g');
      }

      #
      # Group by weekday 
      if($item=== 'weekday')
      {
        return $sdate->format('l');
      }

      #
      # Group by weekday name and hour
      if($item === 'weekdayhour')
      {
        return $sdate->format('l g');
      }

    }

    /**
   * Returns the unique element IDs that match a given element query.
   *
   * @param DbCommand $query
   *
   * @return array
   */
  private function _getElementIdsFromQuery(DbCommand $query)
  {
    // Get the matched element IDs, and then have the SearchService filter them.
    $elementIdsQuery = craft()->db->createCommand()
      ->select('elements.id, venti.eventid')
      ->from('Venti_event events')
      ->join('elements elements', 'elements.id = venti.eventid')
      ->group('elements.id');

    $elementIdsQuery->setWhere($query->getWhere());
    $elementIdsQuery->setJoin($query->getJoin());

    $elementIdsQuery->params = $query->params;
    return $elementIdsQuery->queryColumn();
  }

    /**
     * Returns an element type by its class handle.
     *
     * @param string $class The element type class handle.
     *
     * @return IElementType|null The element type, or `null`.
     */
    public function getElementType($class)
    {
      return craft()->components->getComponentByTypeAndClass(ComponentType::Element, $class);
    }

    /**
     *
     * @param ElementCriteriaModel $criteria
     *
     * @return FieldModel[]
     */
    public function getFieldsForElementsQuery(Venti_CriteriaModel $criteria)
    {
      $contentService = craft()->content;
      $originalFieldContext = $contentService->fieldContext;
      $contentService->fieldContext = 'global';

      $fields = craft()->fields->getAllFields();

      $contentService->fieldContext = $originalFieldContext;

      return $fields;
    }

    /**
     *
     * @param ElementCriteriaModel $criteria
     *
     * @deprecated Deprecated in 2.3. Element types should implement {@link getFieldsForElementsQuery()} instead.
     * @return array
     */
    public function getContentFieldColumnsForElementsQuery(Venti_CriteriaModel $criteria)
    {
      $columns = array();
      $fields = $this->getFieldsForElementsQuery($criteria);

      foreach ($fields as $field)
      {
        if ($field->hasContentColumn())
        {
          $columns[] = array(
            'handle' => $field->handle,
            'column' => ($field->columnPrefix ? $field->columnPrefix : 'field_') . $field->handle
          );
        }
      }

      return $columns;
    }
}