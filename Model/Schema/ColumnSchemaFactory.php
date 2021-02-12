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

use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchemaColumn;
use Accurateweb\SynchronizationBundle\Model\Schema\Column\DecimalColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColumnSchemaFactory
{
  /**
   * @param array $data
   * @return BaseSchemaColumn
   */
  public function createColumn($data)
  {
    $resolver = new OptionsResolver();
    $resolver->setRequired([
      'type',
      'name'
    ]);
    $resolver->setDefaults([
      'size' => null,
      'default' => null,
      'precision' => null,
      'scale' => null,
      'mapWith' => null,
      'index' => null,
    ]);
    $data = $resolver->resolve($data);

    if (strtolower($data['type']) == 'decimal')
    {
      $column = new DecimalColumn($data['name']);

      if (isset($data['precision']))
      {
        $column->setPrecision($data['precision']);
      }
      if (isset($data['scale']))
      {
        $column->setScale($data['scale']);
      }
    }
    else
    {
      $column = new BaseSchemaColumn($data['name']);
    }

    $column->setType($data['type']);

    if (isset($data['size']))
    {
      $column->setSize($data['size']);;
    }

    if (isset($data['mapWith']))
    {
      $column->setMapWith($data['mapWith']);
    };

    if (isset($data['index']))
    {
      $column->setIndex($data['index']);

      if ($data['index'] == 'unique')
      {
        $column->setUnique(true);
      }
    }

    if (isset($data['default']))
    {
      $column->setDefault($data['default']);
    }

    return $column;
  }
}