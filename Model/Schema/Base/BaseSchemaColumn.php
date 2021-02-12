<?php
/**
 *  (c) 2019 ИП Рагозин Денис Николаевич. Все права защищены.
 *
 *  Настоящий файл является частью программного продукта, разработанного ИП Рагозиным Денисом Николаевичем
 *  (ОГРНИП 315668300000095, ИНН 660902635476).
 *
 *  Алгоритм и исходные коды программного кода программного продукта являются коммерческой тайной
 *  ИП Рагозина Денис Николаевича. Любое их использование без согласия ИП Рагозина Денис Николаевича рассматривается,
 *  как нарушение его авторских прав.
 *   Ответственность за нарушение авторских прав наступает в соответствии с действующим законодательством РФ.
 */

namespace Accurateweb\SynchronizationBundle\Model\Schema\Base;

class BaseSchemaColumn
{
  private $name;
  private $type;
  private $size;
  private $mapWith;
  private $index;
  private $unique = false;
  private $default;

  public function __construct($name)
  {
    $this->name = $name;
    $this->setIndex(false);
  }

  public function __toString()
  {
    $string = $this->name . ' ' . $this->type;

    if ($this->size)
    {
      $string .= '(' . $this->size . ')';
    }

    return $string;
  }

  public static function fromArray($values)
  {
    $result = new BaseSchemaColumn($values['name']);
    $result->setType($values['type']);

    if (isset($values['size']))
    {
      $result->setSize($values['size']);;
    }

    if (isset($values['mapWith']))
    {
      $result->setMapWith($values['mapWith']);
    };

    if (isset($values['index']))
    {
      $result->setIndex($values['index']);

      if ($values['index'] == 'unique')
      {
        $result->setUnique(true);
      }
    }

    if (isset($values['default']) || array_key_exists('default', $values))
    {
      $result->setDefault($values['default']);
    }

    return $result;
  }

  public function getMapWith()
  {
    return $this->mapWith;
  }

  public function getMappedColumn()
  {
    if (is_null($this->mapWith))
    {
      return $this->getName();
    }

    if ($this->mapWith === 'false')
    {
      return null;
    }

    return $this->mapWith;

  }

  public function getName()
  {
    return $this->name;
  }

  public function getSize()
  {
    return $this->size;
  }

  public function getType()
  {
    return $this->type;
  }

  public function setMapWith($v)
  {
    $this->mapWith = $v;
  }

  public function setSize($v)
  {
    $this->size = $v;
  }

  public function setType($v)
  {
    $this->type = $v;
  }

  public function getIndex()
  {
    return $this->index;
  }

  public function setIndex($v)
  {
    $this->index = (bool)$v;
  }

  /**
   * @return bool
   */
  public function isUnique ()
  {
    return $this->unique;
  }

  /**
   * @param bool $unique
   * @return $this
   */
  public function setUnique ($unique)
  {
    $this->unique = $unique;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getDefault ()
  {
    return $this->default;
  }

  /**
   * @param mixed $default
   * @return $this
   */
  public function setDefault ($default)
  {
    $this->default = $default;
    return $this;
  }
}
