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

namespace Accurateweb\SynchronizationBundle\Model\Configuration;

use Accurateweb\SynchronizationBundle\Model\Handler\TransferHandler;
use Accurateweb\SynchronizationBundle\Model\SynchronizationScenario;
use Accurateweb\SynchronizationBundle\Model\SynchronizationSubject;
use Gedmo\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class SynchronizationServiceConfiguration
{

  private $datasources = array();
  private $dispatcher;
  /**
   * @var SynchronizationSubject[]|array
   */
  private $subjects = array();
  private $scenarios = array();
  private $working_dir;
  private $sql_temp_dir;
  private $db_connection;
  private $kernelRootDir;
  private $logger;

  public function __construct($dispatcher = null, $kernelRootDir = null)
  {
    $this->kernelRootDir = $kernelRootDir;
    $this->working_dir = str_replace('\\', '/', $this->kernelRootDir . '/../var/synchronization');
    $this->sql_temp_dir = $this->working_dir . '/sql';
    $this->dispatcher = $dispatcher;
    $this->setDbConnection($this->getDbConnection());
  }

  public function load($filename = null)
  {
    $finder = new Finder();
    $finder->files()->in($this->kernelRootDir."/config/")->name('parser.yml');

    foreach ($finder->files() as $file)
    {
      $filename = $file->getContents();
    }

    $configuration = Yaml::parse($filename);
    $this->loadConnections($configuration);
    $this->loadSubjects($configuration);
    $this->loadScenarios($configuration);
  }

  public function getDatasource($alias)
  {
    return isset($this->datasources[$alias]) ? $this->datasources[$alias] : null;
  }

  private function loadConnections($configuration)
  {
    $this->datasources = array();

    $datasourceList = isset($configuration['datasources']) ? $configuration['datasources'] : array();
    foreach ($datasourceList as $name => $datasourceConfiguration)
    {

      if (!isset($datasourceConfiguration['class']))
      {
         throw new InvalidConfigurationException(sprintf('Не задан параметр class для соединения %s', $name));
      }

      $className = $datasourceConfiguration['class'];
      $options = array();

      if (isset($datasourceConfiguration['options']))
      {
        $options = array_merge($options, $datasourceConfiguration['options']);
      }

      $this->datasources[$name] = new $className($options);
    }
  }

  private function loadSubjects($configuration)
  {
    $subjects = (isset($configuration['subjects']) ? $configuration['subjects'] : array());

    foreach ($subjects as $name => $subjectConfiguration)
    {
      $this->subjects[$name] = new SynchronizationSubject($this, $name, $subjectConfiguration);
    }
  }

  private function loadScenarios($configuration)
  {
    $scenarioConfigurations = isset($configuration['scenarios']) ? $configuration['scenarios'] : array();
    foreach ($scenarioConfigurations as $scenarioName => $scenarioConfiguration)
    {

      $subjects = (isset($scenarioConfiguration['subjects']) ? $scenarioConfiguration['subjects'] : array());
      $scenarioClassName = isset($scenarioConfiguration['class']) ? $scenarioConfiguration['class'] : 'SynchronizationScenario';
      $scenario = new $scenarioClassName($this->dispatcher);
      if (!$scenario instanceof SynchronizationScenario)
      {
        throw new InvalidArgumentException('scenario must be an instance of SynchronizationScenario');
      }
      foreach ($subjects as $subjectName => $subjectConfiguration)
      {
        $scenario->addSubject($subjectName);
      }

      $this->scenarios[$scenarioName] = $scenario;
    }
  }

  public function getScenario($name)
  {
    return isset($this->scenarios[$name]) ? $this->scenarios[$name] : null;
  }

  /**
   * @param $name
   * @return SynchronizationSubject|null
   */
  public function getSubject($name)
  {
    return isset($this->subjects[$name]) ? $this->subjects[$name] : null;
  }

  public function getSubjects()
  {
    return $this->subjects;
  }

  public function getParser($subject)
  {
    $parser = null;
    $subject = $this->getSubject($subject);

    if ($subject)
    {
      $parser = $subject->getParser();
    }

    return $parser;
  }

  public function getDefaultOf($subject, $name)
  {
    $result = null;

    $subject = $this->getSubject($subject);

    if ($subject)
    {
      $result = $subject->getDefault($name);
    }

    return $result;
  }

  public function getWorkingDirectory()
  {
    return $this->working_dir;
  }

  public function getSqlTempDirectory()
  {
    return $this->sql_temp_dir;
  }

  public function setDbConnection($connection)
  {
    $this->db_connection = $connection;
  }

  public function getDbConnection()
  {
    return $this->db_connection;
  }

  function getInsertHandler($subject)
  {
    $handler = null;
    $schema = $this->getSchema($subject);
    $subject = $this->getSubject($subject);


    if ($subject)
    {
      $handler = $subject->getInsertHandler($this->db_connection, $schema, $this->dispatcher);
      $handler->setLogger($this->logger);
    }

    return $handler;
  }

  /**
   * @param $subject
   * @return TransferHandler|null
   */
  public function getTransferHandler($subject)
  {
    $handler = null;

    $schema = $this->getSchema($subject);
    $subject = $this->getSubject($subject);

    if ($subject)
    {
      /** @var SynchronizationSubject $subject*/
      $handler = $subject->getTransferHandler($this->db_connection, $schema, $this->dispatcher);
    }


    return $handler;
  }

  function getSchema($subject)
  {
    $schema = null;
    $subject = $this->getSubject($subject);

    if ($subject)
    {
      $schema = $subject->getSchema();
    }

    return $schema;
  }

  /**
   * @return EventDispatcherInterface
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
   * @return LoggerInterface
   */
  public function getLogger ()
  {
    if (!$this->logger)
    {
      $this->logger = new NullLogger();
    }

    return $this->logger;
  }

  /**
   * @param LoggerInterface $logger
   * @return $this
   */
  public function setLogger (LoggerInterface $logger)
  {
    $this->logger = $logger;
    return $this;
  }
}
