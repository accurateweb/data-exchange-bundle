<?php

namespace Accurateweb\SynchronizationBundle\Model\Entity\Base;

abstract class BaseEntity
{

  private $values;
  private $attributes;

  /**
   * @param $source
   * @param null $parent
   * @return false|void
   */
  abstract public function parse($source, $parent = null);

  public function __construct()
  {
    $this->values = array();
    $this->attributes = array();
  }

  public function setValue($name, $value)
  {
    $this->values[$name] = $value;
  }

  public function getValue($name)
  {
    return isset($this->values[$name]) ? $this->values[$name] : null;
  }
  
  public function setValues($values)
  {
    $this->values = $values;
  }

  public function getValues()
  {
    return $this->values;
  }

  public function removeValue($name)
  {
    if (isset($this->values[$name]))
    {
      unset($this->values[$name]);
    }
  }

  public function getAttributes()
  {
    return $this->attributes;
  }

  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }

  public function getAttribute($name, $default = null)
  {
    return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
  }

  public function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }

}
