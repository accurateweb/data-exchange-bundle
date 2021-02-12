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
use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchema;
use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchemaColumn;

class EntityCollection implements \Iterator
{
  /**
   * @var BaseSchema
   */
  protected $schema;
  
  private $entities = array(),
          $dispatcher,
          $limit,
          $subjectName;

  public function __construct($schema)
  {
    $this->schema = $schema;
  }

  public function add($e)
  {
    if (is_array($e))
    {
      $this->entities = array_merge($this->entities, $e);
    }
    else if ($e instanceof BaseEntity)
    {
      $this->entities[] = $e;
    }
    else
    {
      throw new \InvalidArgumentException('Argument must be either instance of BaseEntity or an array of BaseEntity');
    }
    
    if ($this->dispatcher && $this->limit >= 0 && $this->count() >= $this->limit)
    {
     // $this->dispatcher->notify(new sfEvent($this, 'entitycollection.limit', array('subjectName' => $this->subjectName)));
    }
  }

  /**
   * Очищает коллекцию сущностей
   */
  public function clear()
  {
    $this->entities = array();
  }
  
  public function toSQL()
  {
    if (count($this->entities) === 0)
    {
      return 'SELECT 1;';
    }

    $columnNames = $this->schema->getColumnNames();
    $sql = sprintf('INSERT INTO %s (`%s`) VALUES' . PHP_EOL, $this->schema->getTempTableName(), implode('`, `', $columnNames));
    $sqlParts = array();

    foreach ($this->entities as $entity)
    {
      $processedValues = array();
      $values = $entity->getValues();

      foreach ($this->schema->getColumns() as $columnName => $columnSchema)
      {
        $nullValue = $columnSchema->getDefault()?'DEFAULT':'NULL';
        $processedValues[] = isset($values[$columnName]) ? $this->processValue($values[$columnName]) : $nullValue;
      }


      $sqlParts[] = sprintf('(%s)', implode(',', $processedValues));
    }

    $sql .= implode(',' . PHP_EOL, $sqlParts) . ';';
    return $sql;
  }

  public function getPreSql()
  {
    $sqlParts = array();
    $indices = $this->schema->getIndices();
    $tempTableName = $this->schema->getTempTableName();
    $uniques = [];

    /**
     * @var  $name
     * @var BaseSchemaColumn $column
     */
    foreach ($this->schema->getColumns() as $name => $column)
    {
      /*
       * `columnName` varchar(255)
       */
      $columnSize = $column->getSize()?sprintf('(%s)', $column->getSize()):'';
      $columnPart = sprintf('`%s` %s%s', $column->getName(), $column->getType(), $columnSize);

      if ($column->getDefault())
      {
        $columnPart = sprintf('%s NOT NULL DEFAULT \'%s\'', $columnPart, $column->getDefault());
      }
      else
      {
        $columnPart = sprintf('%s DEFAULT NULL', $columnPart);
      }

      $sqlParts[] = $columnPart;

      if ($column->getIndex())
      {
        $indices[] = $name;

        if ($column->isUnique())
        {
          $uniques[] = $name;
        }
      }
    }

    $columnDefinitions = implode(',' . PHP_EOL, $sqlParts);
    $sql = sprintf('DROP TABLE IF EXISTS %s;', $tempTableName) . PHP_EOL;
    $sql .= sprintf('CREATE TEMPORARY TABLE IF NOT EXISTS %s (%s)', $tempTableName, $columnDefinitions) . PHP_EOL;

    if ($this->schema->hasOption('collate'))
    {
      $sql .= sprintf('collate = %s', $this->schema->getOption('collate')).PHP_EOL;
    }

    $sql .= ';';

    foreach ($indices as $i => $idxColumn)
    {
      $isUnique = (array_search($idxColumn, $uniques) !== false);

      if (is_array($idxColumn))
      {
        
      }

      $sql .= sprintf('CREATE %4$s INDEX `%1$s_I_%2$d` ON `%1$s` (`%3$s`);', $tempTableName, $i, $idxColumn, $isUnique?'UNIQUE':'') . PHP_EOL;
    }

    return $sql;
  }

  public function count()
  {
    return count($this->entities);
  }

  public function split($chunkSize)
  {
    $collections = array();
    $cursor = 0;
    $length = count($this->entities);

    while ($cursor < $length)
    {
      $chunk = array_slice($this->entities, $cursor, $chunkSize);

      $cursor += count($chunk);

      $collection = new EntityCollection($this->schema);
      $collection->add($chunk);

      $collections[] = $collection;
    }
    return $collections;
  }

  public function processValue($value)
  {
    if (is_string($value))
    {
      if ('null' == strtolower($value))
      {
        return $value;
      }

      $value = str_replace(array(chr(0), '\n', '\r', '\\', ',', '"', chr(26), '\'', ';'), array('\\' . chr(0), '\n', '\r', '\\', '\,', '\"', '\\' . chr(26), '\'\'', '\;'), $value);

      if (preg_match('/^\'.*\'$/', $value) <= 0)
      {
        $value = '\'' . $value . '\'';
      }
    }

    return $value;
  }

  public function current()
  {
    return current($this->entities);
  }

  public function key()
  {
    return key($this->entities);
  }

  public function next()
  {
    return next($this->entities);
  }

  public function rewind()
  {
    return reset($this->entities);
  }

  public function valid()
  {
    return key($this->entities) !== null;
  }

  public function setEventDispatcher($dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }
  
  public function setEntityLimit($v)
  {
    $this->limit = (int)$v;
  }
  
  public function setSubjectName($v)
  {
    $this->subjectName = $v;
  }
}
