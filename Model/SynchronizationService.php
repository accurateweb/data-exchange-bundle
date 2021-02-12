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

use Accurateweb\SynchronizationBundle\Exception\DataSourceException;
use Accurateweb\SynchronizationBundle\Model\Configuration\SynchronizationServiceConfiguration;
use Accurateweb\SynchronizationBundle\Model\Datasource\Base\BaseDataSource;
use Accurateweb\SynchronizationBundle\Model\Datasource\ModifyTimeAwareDataSource\DataSourceTimeComparator;
use Accurateweb\SynchronizationBundle\Model\Datasource\ModifyTimeAwareDataSource\ModifyTimeAwareDataSourceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class SynchronizationService
{
  private $configuration;
  private $dispatcher;
  private $logger;
  private $mTimeComparator;

  public function __construct(
    SynchronizationServiceConfiguration $configuration,
    EventDispatcherInterface $dispatcher,
    LoggerInterface $logger,
    DataSourceTimeComparator $mTimeComparator
  )
  {
    $this->configuration = $configuration;
    $this->dispatcher = $dispatcher;
    $this->logger = $logger;
    $this->mTimeComparator = $mTimeComparator;
  }

  /**
   * @param $subject
   * @param array $options
   * @return array|void
   */
  public function pull($subject, $options = array())
  {
    $filename = $this->getRemoteDataFile($subject, $options);
    $datasource = $this->getDatasource($subject, $options);

    if ($datasource instanceof ModifyTimeAwareDataSourceInterface)
    {
      $isModified = $this->mTimeComparator->isModified($datasource, $this->getRemoteFilename($subject, $options));

      if (!$isModified)
      {
        $this->logger->warning(sprintf('File %s not modified', $this->getRemoteFilename($subject, $options)));
        return ;
      }
    }

    return $this->getEngine(isset($options['mode']) ? $options['mode'] : SynchronizationMode::FULL)
      ->execute($subject, SynchronizationDirection::INCOMING, $filename, $options);
  }

  private function combineValue($name, $subject, $options)
  {
    return isset($options[$name]) ? $options[$name] : $this->configuration->getDefaultOf($subject, $name);
  }

  public function push($subject, $options = array())
  {
    $datasource = $this->getDatasource($subject, $options);
    $remoteFilename = $this->getRemoteFilename($subject, $options, 'outgoing');
    $workingDir = $this->configuration->getWorkingDirectory() . '/outgoing';

    if ((!is_dir($workingDir) && !@mkdir($workingDir, 0777, true)))
    {
      throw new \Exception('Unable to save temporary XML data. Directory "%s" doesn\'t exist and couldn\'t be created', $behafigeji);
    }

    $localFilename = $workingDir . '/' . $subject . '.xml';
    $parser = $this->configuration->getParser($subject);

    if (!$parser)
    {
      throw new \Exception('Unable to instantiate parser class');
    }

    $objects = $parser->fetchObjects();
    file_put_contents($localFilename, $parser->serialize($objects));
    $datasource->put($localFilename, $remoteFilename);
  }

  protected function getDatasource($subject, $options)
  {
    $datasource = $this->combineValue("datasource", $subject, $options);
    if (!$datasource instanceof BaseDataSource && is_string($datasource))
    {
      $datasource = $this->configuration->getDatasource($datasource);
    }

    if (!$datasource)
    {
      throw new InvalidConfigurationException('Unable to create datasource.');
    }

    return $datasource;
  }

  protected function getRemoteFilename($subject, $options, $direction = SynchronizationDirection::INCOMING)
  {

    if (isset($options['filename']))
    {
      return $options['filename'];
    }
    $names = $this->configuration->getDefaultOf($subject, 'filename');
    $name = null;

    if (is_string($names))
    {
      $name = $names;
    }
    else
    {
      if (isset($names[$direction]))
      {
        $name = $names[$direction];
      }
    }

    return $name;
  }

  /**
   * @param $subject
   * @param array $options
   * @return mixed
   * @throws DataSourceException
   * @throws BadCredentialsException
   */
  public function getRemoteDataFile($subject, $options = array())
  {
    $datasource = $this->getDatasource($subject, $options);
    $remoteFilename = $this->getRemoteFilename($subject, $options);
    $workingDir = $this->configuration->getWorkingDirectory() . '/incoming';

    if ((!is_dir($workingDir) && !@mkdir($workingDir, 0777, true)))
    {
      throw new \Exception('Unable to save temporary XML data. Directory "%s" doesn\'t exist and couldn\'t be created', $workingDir);
    }

    $localFilename = sprintf('%s/%s_%s.xml', $workingDir, $subject, date('Ymd'));

    if (is_file($localFilename))
    {
      $i = 1;

      while (is_file($localFilename))
      {
        $localFilename = sprintf('%s/%s_%s_%d.xml', $workingDir, $subject, date('Ymd'), $i++);
      }
    }

    return $datasource->get($remoteFilename, $localFilename);
  }

  public function getEngine($mode)
  {
    switch ($mode)
    {
      case SynchronizationMode::FULL:
        {
          return new \Accurateweb\SynchronizationBundle\Model\Engine\FullSynchronizationEngine($this->configuration);
        }
      case SynchronizationMode::INCREMENTAL:
        {
          return new \Accurateweb\SynchronizationBundle\Model\Engine\IncrementalSynchronizationEngine($this->configuration);
        }
      default:
        {
          throw new \Exception(sprintf('Unsupported mode: \'%s\'', $mode));
        }
    }
  }

}