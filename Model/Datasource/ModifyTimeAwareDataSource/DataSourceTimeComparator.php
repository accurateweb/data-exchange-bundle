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

namespace Accurateweb\SynchronizationBundle\Model\Datasource\ModifyTimeAwareDataSource;

use Accurateweb\SynchronizationBundle\Exception\DataSourceException;


class DataSourceTimeComparator
{
  private $cacheDir;

  /**
   * DataSourceTimeComparator constructor.
   * @param string $cacheDir директория хранения времени
   */
  public function __construct ($cacheDir)
  {
    $this->cacheDir = $cacheDir;
  }

  /**
   * @param ModifyTimeAwareDataSourceInterface $dataSource
   * @param string $filename
   * @return boolean
   */
  public function isModified (
    ModifyTimeAwareDataSourceInterface $dataSource,
    $filename
  )
  {
    try
    {
      $lastRemoteTime = $dataSource->getLastModifyTime($filename);
      $lastLocalTime = $this->getLastStoreTime($filename);
      $this->storeNewTime($filename, $lastRemoteTime);

      return $lastRemoteTime > $lastLocalTime;
    }
    catch (DataSourceException $e)
    {
      return true;
    }
  }

  protected function getLastStoreTime ($filename)
  {
    $fullName  = $this->resolveLocalFileName($filename);

    if (!file_exists($fullName))
    {
      return null;
    }

    $uTime = (int)file_get_contents($fullName);
    $date = new \DateTime();
    $date->setTimestamp($uTime);

    return $date;
  }

  protected function storeNewTime ($filename, \DateTime $newTime = null)
  {
    $fullName = $this->resolveLocalFileName($filename);
    $newTime = $newTime?$newTime:time();

    file_put_contents($fullName, $newTime->getTimestamp());
  }

  private function resolveLocalFileName ($filename)
  {
    $fName = base64_encode($filename);
    $fullName = sprintf('%s/%s', $this->getDir(), $fName);
    return $fullName;
  }

  private function getDir ()
  {
    if (!file_exists($this->cacheDir))
    {
      mkdir($this->cacheDir);
    }

    $path = realpath($this->cacheDir);

    if (!$path)
    {
      throw new DataSourceException(sprintf('Directory %s not exists', $this->cacheDir));
    }

    return $path;
  }
}