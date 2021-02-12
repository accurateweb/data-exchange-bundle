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


use Accurateweb\SynchronizationBundle\Model\Configuration\SynchronizationServiceConfiguration;
use Accurateweb\SynchronizationBundle\Model\Datasource\ModifyTimeAwareDataSource\DataSourceTimeComparator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SynchronizationServiceFactory
{
  private $dispatcher;
  private $logger;
  private $mTimeComparator;

  public function __construct(
    EventDispatcherInterface $dispatcher,
    LoggerInterface $logger,
    DataSourceTimeComparator $mTimeComparator
  )
  {
    $this->dispatcher = $dispatcher;
    $this->logger = $logger;
    $this->mTimeComparator = $mTimeComparator;
  }

  /**
   * @param SynchronizationServiceConfiguration $configuration
   * @return SynchronizationService
   */
  public function createSynchronizationService (SynchronizationServiceConfiguration $configuration)
  {
    return new SynchronizationService(
      $configuration,
      $this->dispatcher,
      $this->logger,
      $this->mTimeComparator
    );
  }
}