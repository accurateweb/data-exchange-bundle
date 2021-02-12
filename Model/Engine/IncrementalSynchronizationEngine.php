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

namespace Accurateweb\SynchronizationBundle\Model\Engine;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class IncrementalSynchronizationEngine extends BaseSynchronizationEngine
{

  public function execute($subject, $direction, $local_filename, $options = array())
  {
    $parser = $this->configuration->getParser($subject);

    if (!$parser)
    {
      throw new InvalidConfigurationException('Unable to instantiate parser class');
    }
    
    $subjectConfiguration = $this->configuration->getSubject($subject);
    $schema = $this->configuration->getSchema($subject);
    $transferMap = $schema->getTransferMap();
    $model = $subjectConfiguration->getOption('model');    
    $entities = $parser->parseFile($local_filename)->getEntities();

    if (!is_array($entities))
    {
      $entities = array($subject => $entities);
    }
    $syncronizationResult = array();
    foreach ($entities[$subject] as $entity)
    {

      if (!empty($transferMap))
      {
        $values = $entity->getValues();
        foreach ($values as $name => $value)
        {
          if (( isset($transferMap[$name]) && $transferMap[$name] != $name))
          {
            $entity->setValue($transferMap[$name], $value);
            $entity->removeValue($name);
          }
        }
      }

      $remoteObject = call_user_func(array($model, 'createRemoteObject'), $entity);

      $remoteId = $remoteObject->getRemoteId();
      if (is_array($remoteId))
      {
        $remoteId = implode('-', $remoteId);
      }

      try
      {
        $syncronizationResult[$remoteId] = $remoteObject->synchronize();
      }
      catch (\Exception $e)
      {
        $syncronizationResult[$remoteId] = new SynchronizationResult(SynchronizationResult::INTERNAL_SERVER_ERROR, $e->getMessage());
      }
    }


    foreach ($syncronizationResult as $remoteObjectId => $resultCode)
    {
      if ($resultCode->getCode() == SynchronizationResult::OK)
      {
        unset($syncronizationResult[$remoteObjectId]);
      }
    }

    return $syncronizationResult;
  }

}
