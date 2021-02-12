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

namespace Accurateweb\SynchronizationBundle\Model\Handler\Base;

use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchema;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseDataHandler
{

  protected $schema = null;
  /** @var $connection Connection */
  protected $connection = null;
  /** @var EventDispatcherInterface */
  protected $dispatcher = null;
  /** @var LoggerInterface */
  protected $logger;

  /**
   * Конструктор
   *
   * Список поддерживаемых опций:
   * - debug_sql boolean Указывает, нужно ли включать лог запросов к MySQL. По умолчанию false
   * - debug_profile boolean Указывает, нужно ли вести журнал операций. По умолчанию false
   *
   * @param $connection
   * @param $schema
   * @param $dispatcher
   * @param array $options Опции
   */
  public function __construct($connection, $schema, $dispatcher, $options = array())
  {
    $resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();
    $this->configure($resolver);

    $this->schema = $schema;
    $this->connection = $connection;
    $this->dispatcher = $dispatcher;
    $this->options = $resolver->resolve($options);
  }

  protected function configure(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
  {
    $resolver->setDefault('debug_sql', false);
    $resolver->setDefault('debug_profile', false);
  }

  /**
   * Выполняет запрос к БД
   *
   * @param string $sql Текст SQL-запроса
   * @return mixed
   */
  public function query($sql)
  {
    if ($this->getOption('debug_sql'))
    {
      //  $this->logger->info(sprintf('SQL Query: %s', $sql));
    }

    $stmt = $this->connection->prepare($sql);
    $result = $stmt->execute();

    if ($this->getOption('debug_sql') && $this->getOption('debug_profile'))
    {
      //$this->logger->info(sprintf('Query finished', $sql));
    }

    if (!$result)
    {
      //$this->logger->addError(sprintf('Unable to execute query: %s...', $sql));
    }

    return $result;
  }

  public function getConnection()
  {
    return $this->connection;
  }

  function getOption($name)
  {
    return (isset($this->options[$name]) ? $this->options[$name] : null);
  }

  /**
   * Возвращает схему данных используемой таблицы
   *
   * @return BaseSchema
   */
  function getSchema()
  {
    return $this->schema;
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
  public function setLogger ($logger)
  {
    $this->logger = $logger;
    return $this;
  }
}
