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

namespace Accurateweb\SynchronizationBundle\Model;

class SynchronizationResult
{

  const OK = 0;
  const GENERIC_ERROR = 1;
  const OBJECT_EXISTS = 11;
  const OBJECT_NOT_FOUND = 12;
  const INVALID_STATUS = 13;
  const UNKNOWN_ERROR = -1;
  const INTERNAL_SERVER_ERROR = 20;
  const INVALID_REMOTE_ID = 101;
  
  private $code = null;
  private $message = null;

  public function __construct($code, $message = '')
  {
    $this->code = $code;
    $this->message = $message;
  }

  /**
   * @param $code
   * @param string $message
   * @return SynchronizationResult
   */
  public static function create($code, $message = '')
  {
    return new SynchronizationResult($code, $message);
  }

  public function getCode()
  {
    return $this->code;
  }

  public function getMessage()
  {
    return $this->message;
  }

}
