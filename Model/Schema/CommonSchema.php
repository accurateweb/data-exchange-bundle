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

namespace Accurateweb\SynchronizationBundle\Model\Schema;

use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchema;
use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchemaColumn;

class CommonSchema extends BaseSchema
{

  public function __construct($options = array())
  {
    parent::__construct($options);
    $this->loadColumns();
  }

  protected function configure($options)
  {
    $this->addRequiredOption('columns');
    $this->addOption('collate', 'utf8_unicode_ci');
  }

  protected function loadColumns()
  {
    $columns = array();
    $columnOptions = $this->getOption('columns');
    $factory = new ColumnSchemaFactory();

    foreach ($columnOptions as $name => $value)
    {
      $value['name'] = $name;
      $columns[$name] = $factory->createColumn($value);
    }

    $this->setColumns($columns);
  }

}
