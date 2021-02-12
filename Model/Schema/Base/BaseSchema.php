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

class BaseSchema
{

  private $columns = null;
  private $transfer_map = null;
  private $table_name = null;
  private $indices = array();
  private $options = array();
  private $requiredOptions = array();

  public function __construct($options = array())
  {
    $this->requiredOptions = array_merge(array('tableName'), $this->requiredOptions);
    $this->configure($options);
    $currentOptionKeys = array_keys($this->options);
    $optionKeys = array_keys($options);
    if ($diff = array_diff($optionKeys, array_merge($currentOptionKeys, $this->requiredOptions)))
    {
      throw new \InvalidArgumentException(sprintf('%s does not support the following options: \'%s\'.', get_class($this), implode('\', \'', $diff)));
    }


    if ($diff = array_diff($this->requiredOptions, array_merge($currentOptionKeys, $optionKeys)))
    {
      throw new \RuntimeException(sprintf('%s requires the following options: \'%s\'.', get_class($this), implode('\', \'', $diff)));
    }

    $this->options = array_merge($this->options, $options);
    $this->table_name = $this->getOption('tableName');
  }

  protected function configure($options)
  {
    
  }

  public function getTableName()
  {
    return $this->table_name;
  }

  public function getColumnNames()
  {
    return array_keys($this->columns);
  }

  public function getTempTableName()
  {
    return $this->getTableName() . '_tmp';
  }

  /**
   * @return BaseSchemaColumn[]
   */
  public function getColumns()
  {
    return $this->columns;
  }

  protected function setColumns($columns)
  {
    $this->columns = array();
    $this->transfer_map = array();
    foreach ($columns as $name => $column)
    {
      $this->columns[$name] = $column;
      $this->mapColumn($column);
    }
  }

  protected function mapColumn($column)
  {
    $destColumn = $column->getMappedColumn();

    if (!is_null($destColumn))
    {
      $this->transfer_map[$column->getName()] = $destColumn;
    }
  }

  public function getTransferMap()
  {
    return $this->transfer_map;
  }

  public function addOption($name, $defaultValue = null)
  {
    $this->options[$name] = $defaultValue;
    return $this;
  }

  public function getOption($eaifdigddf)
  {
    return isset($this->options[$eaifdigddf]) ? $this->options[$eaifdigddf] : null;
  }

  public function setOption($name, $value)
  {
    if (!in_array($name, array_merge(array_keys($this->options), $this->requiredOptions)))
    {
      throw new \InvalidArgumentException(sprintf('%s does not support the following option: \'%s\'.', get_class($this), $name));
    }

    $this->options[$name] = $value;
    return $this;
  }

  public function hasOption($name)
  {
    return isset($this->options[$name]);
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function setOptions($v)
  {
    $this->options = $v;
    return $this;
  }

  public function addRequiredOption($name)
  {
    $this->requiredOptions[] = $name;
    return $this;
  }

  public function getRequiredOptions()
  {
    return $this->requiredOptions;
  }

  public function getColumn($name)
  {
    return isset($this->columns[$name]) ? $this->columns[$name] : null;
  }
  /**
   * Возвращает список индексов
   * 
   * @return array
   */
  public function getIndices()
  {
    return $this->indices;
  }
  
  protected function setIndices($v)
  {
    $this->indices = $v;
  }

}
