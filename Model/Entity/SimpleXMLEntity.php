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

namespace Accurateweb\SynchronizationBundle\Model\Entity;

use Accurateweb\SynchronizationBundle\Model\Entity\Base\BaseEntity;

class SimpleXMLEntity extends BaseEntity
{
  public function escapeSQLString($sql)
  {
    return (string) $sql;
  }

  public function parse($source, $parent = null)
  {
    $values = array();
    foreach ($source as $name => $value)
    {
      $values[$name] = (string)$value;
    }
    $this->setValues($values);
    $attributes = array();

    if ($source->attributes())
    {
      foreach ($source->attributes() as $name => $value)
      {
        $attributes[$name] = (string) $value;
      }
    }

    $this->setAttributes($attributes);
  }

  public function extractValues($values)
  {
    $extracted = array();
    foreach ($values as $name => $value)
    {
      $extracted[$name] = $value instanceof SimpleXMLElement ? (string)$value : $value;
    }

    return $extracted;
  }

}
