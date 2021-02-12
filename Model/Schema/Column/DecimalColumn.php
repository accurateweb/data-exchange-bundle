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

namespace Accurateweb\SynchronizationBundle\Model\Schema\Column;

use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchemaColumn;

class DecimalColumn extends BaseSchemaColumn
{
  private $precision=10;
  private $scale=2;

  public function __construct ($name)
  {
    parent::__construct($name);
  }

  /**
   * @return string
   */
  public function getType ()
  {
    return 'DECIMAL';
  }

  /**
   * @return string
   */
  public function getSize ()
  {
    return sprintf('%s,%s', $this->precision, $this->scale);
  }

  /**
   * @param int $precision
   * @return $this
   */
  public function setPrecision ($precision)
  {
    $this->precision = $precision;
    return $this;
  }

  /**
   * @param int $scale
   * @return $this
   */
  public function setScale ($scale)
  {
    $this->scale = $scale;
    return $this;
  }
}