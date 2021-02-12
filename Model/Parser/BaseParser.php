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

namespace Accurateweb\SynchronizationBundle\Model\Parser;

use Accurateweb\SynchronizationBundle\Exception\ParserException;
use Accurateweb\SynchronizationBundle\Model\Configuration\SynchronizationServiceConfiguration;
use Accurateweb\SynchronizationBundle\Model\Entity\EntityCollection;
use Accurateweb\SynchronizationBundle\Model\Schema\Base\BaseSchema;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

abstract class BaseParser
{
  /** @var $entityFactory \Accurateweb\SynchronizationBundle\Model\Entity\EntityFactory */
  private $entityFactory;
  private $options;
  
  protected $entities;
  /** @var BaseSchema */
  protected $schema;
  
  private $subject;
  private $serviceConfiguration;

  public function __construct($configuration, $subject, $entityFactory, $schema, $options)
  {
    $this->entityFactory = $entityFactory;
    $this->options = $options;
    $this->schema = $schema;

    $this->entities = new EntityCollection($schema);
    
    $this->subject = $subject;
    $this->serviceConfiguration = $configuration;
  }

  public function getEntities()
  {
    return $this->entities;
  }

  abstract protected function loadFile($filename);

  public function parseFile($filename)
  {
    $xml = $this->loadFile($filename);

    if ($xml === false)
    {
      throw new ParserException('Unable to load source file');
    }

    return $this->parse($xml);
  }

  /**
   * @return mixed
   */
  protected function createEntity()
  {
    return $this->entityFactory->create();
  }

  public function getOption($name)
  {
    $value = null;

    if ($this->hasOption($name))
    {
      $value = $this->options[$name];
    }

    return $value;
  }

  /**
   * @return SynchronizationServiceConfiguration
   */
  protected function getServiceConfiguration()
  {
    return $this->serviceConfiguration;
  }

  protected function getSubject()
  {
    return $this->subject;
  }

  public function hasOption($name)
  {
    return isset($this->options[$name]);
  }

  abstract public function parse($source);

  abstract public function serialize($objects);

  public function fetchObjects()
  {
    $queryParameters = $this->getOption('query');

    if ((!empty($queryParameters) && isset($queryParameters['class'])))
    {
      $queryClass = $queryParameters['class'];
      $query = call_user_func($queryClass . '::create');

      if (!$queryClass)
      {
        throw new InvalidConfigurationException(sprintf('Unable to instantiate query class \'%s\'', $query));
      }

      $objects = $query->find();
      return $objects;
    }

    return array();
  }

}
