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

/**
 * Предоставляет функции управления параметрами конкретной синхронизации
 */
class Synchronization
{
  private $subject;
  private $service;
  private $mode;
  private $direction;

  public function __construct($service, $subject, $mode=SynchronizationMode::INCREMENTAL, $direction=SynchronizationDirection::INCOMING)
  {
    $this->service = $service;
    $this->mode = $mode;
    $this->direction = $direction;
    $this->subject = $subject;
  }

  public function execute($parameters=array())
  {
    switch ($this->direction)
    {
      case SynchronizationDirection::INCOMING:
        {
          $options = array_merge($parameters, array(
            'mode' => $this->mode
          ));

          return $this->service->pull($this->subject, $options);
          break;
        }
      case SynchronizationDirection::OUTGOING:
        {
          $options = $parameters;
          return $this->service->push($this->subject, $options);
          break;
        }
      default: throw new asSynchronizationException(sprintf('Неверное направление синхронизации "%s"', $this->direction));
    }
  }

  public function getMode()
  {
    return $this->mode;
  }

  public function getDirection()
  {
    return $this->direction;
  }

  public function getSubjectName()
  {
    return $this->subject;
  }
}
