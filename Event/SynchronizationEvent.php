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

namespace Accurateweb\SynchronizationBundle\Event;

use Accurateweb\SynchronizationBundle\Model\SynchronizationService;
use Symfony\Component\EventDispatcher\Event;

class SynchronizationEvent extends Event
{
  private $service;
  private $subject;
  private $options;

  public function __construct (SynchronizationService $service, $subject, $options)
  {
    $this->service = $service;
    $this->subject = $subject;
    $this->options = $options;
  }

  /**
   * @return SynchronizationService
   */
  public function getService ()
  {
    return $this->service;
  }

  /**
   * @return string
   */
  public function getSubject ()
  {
    return $this->subject;
  }

  /**
   * @return array
   */
  public function getOptions ()
  {
    return $this->options;
  }
}