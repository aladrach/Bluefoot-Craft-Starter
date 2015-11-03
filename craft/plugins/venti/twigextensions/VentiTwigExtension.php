<?php
namespace Craft;

use \Twig_Extension;
use \Twig_Filter_Method;

class VentiTwigExtension extends \Twig_Extension
{
  protected $env;
  
  public function getName()
  {
    return 'VentiTwig';
  }

  public function initRuntime(\Twig_Environment $env)
  {
      $this->env = $env;
  }

  public function getFilters()
  {
    return array(
      'groupByDate' => new \Twig_Filter_Method($this, 'events'),
      'strToDate'   => new \Twig_Filter_Method($this, 'strtodate')
    );
  }

  public function getFunctions()
  {
    return array(
        'groupByDate' => new \Twig_Function_Method($this, 'events'),
        'strToDate'      => new \Twig_Function_Method($this, 'strtodate')
    );
  }

  public function events(array $source=array(), $params)
  {
    return craft()->venti_eventManage->groupByDate($source, $params);
  }

  function strtodate($str, $format)
  {
      return DateTime::createFromFormat($format, $str);
  }
}