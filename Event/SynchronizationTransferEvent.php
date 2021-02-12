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


use Accurateweb\SynchronizationBundle\Model\Handler\Base\BaseDataHandler;
use Symfony\Component\EventDispatcher\Event;

class SynchronizationTransferEvent extends Event
{
  private $dataHandler;
  private $collectionSubject;
  private $subject;

  public function __construct (BaseDataHandler $dataHandler, $collectionSubject, $subject)
  {
    $this->dataHandler = $dataHandler;
    $this->collectionSubject = $collectionSubject;
    $this->subject = $subject;
  }

  /**
   * @return BaseDataHandler
   */
  public function getDataHandler ()
  {
    return $this->dataHandler;
  }

  /**
   * @return mixed
   */
  public function getCollectionSubject ()
  {
    return $this->collectionSubject;
  }

  /**
   * @return string
   */
  public function getSubject ()
  {
    return $this->subject;
  }
}