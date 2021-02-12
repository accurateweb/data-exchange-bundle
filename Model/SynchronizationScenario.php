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

class SynchronizationScenario implements \Iterator, \ArrayAccess
{

  private $subjects = [],
          $iterator = null;

  protected $dispatcher;

  function __construct($dispatcher = null)
  {
    $this->setEventDispatcher($dispatcher);
  }

  function addSubject($subject)
  {
    $this->subjects[] = $subject;
    $this->rewind();
  }

  function current()
  {
    return $this->subjects[$this->iterator];
  }

  function key()
  {
    return $this->iterator;
  }

  function next()
  {
    ++$this->iterator;
  }

  function rewind()
  {
    $this->iterator = 0;
  }

  function valid()
  {
    return isset($this->subjects[$this->iterator]);
  }

  function offsetExists($offset)
  {
    return isset($this->subjects[$offset]);
  }

  function offsetGet($offset)
  {
    return $this->offsetExists($offset) ? $this->subjects[$offset] : null;
  }

  function offsetSet($offset, $value)
  {
    if (is_null($offset))
    {
      $this->subjects[] = $value;
    }
    else
    {
      $this->subjects[$offset] = $value;
    }
  }

  function offsetUnset($offset)
  {
    unset($this->subjects[$offset]);
  }

  /**
   * Вызывается перед выполнением сценария
   */
  public function preExecute()
  {
  }

  /**
   * Вызывается после выполнения всего сценария.
   * 
   * Позволяет выполнить дополнительные задачи после выполнения сценария синхронизации, такие как очистка или запуск
   * внешних скриптов
   */
  public function postExecute()
  {    
  }

  public function setEventDispatcher($dispatcher = null)
  {
    $this->dispatcher = $dispatcher;
  }
}
