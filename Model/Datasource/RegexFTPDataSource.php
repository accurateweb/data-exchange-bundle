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
namespace Accurateweb\SynchronizationBundle\Model\Datasource;

use Accurateweb\SynchronizationBundle\Exception\DataSourceException;
use Accurateweb\SynchronizationBundle\Model\Connection\FTPConnection;
use Accurateweb\SynchronizationBundle\Model\Datasource\Base\BaseDataSource;
use Accurateweb\SynchronizationBundle\Model\Datasource\ModifyTimeAwareDataSource\ModifyTimeAwareDataSourceInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/*
 * directories - директории, в которых будет производиться поиск
 * имена файлов должны быть представлены в виде регулярных выражений
 */
class RegexFTPDataSource extends BaseDataSource implements ModifyTimeAwareDataSourceInterface
{
  private $connection;
  private $resolvedNames = array();

  public function __construct ($options = array())
  {
    parent::__construct($options);

    $this->connection = new FTPConnection([
      'host' => $this->getOption('host'),
      'username' => $this->getOption('username'),
      'password' => $this->getOption('password'),
      'passive_mode' => $this->getOption('passive_mode'),
    ]);
  }

  public function get ($from, $to = null)
  {
    if ($to === null)
    {
      $to = $this->getSavedName();
    }

    $ftp = $this->connection->connect();

    if (!$ftp)
    {
      throw new BadCredentialsException('Can not connect to FTP server');
    }

    $filename = $this->resolveFileNameRegex($from);

    if (!$this->connection->get($filename, $to))
    {
      throw new BadCredentialsException('File download error');
    }

    $this->connection->disconnect();

    return $to;
  }

  public function put ($from, $to)
  {
    $ftp = $this->connection->connect();

    if (!$ftp)
    {
      throw new BadCredentialsException('Can not connect to FTP server');
    }

    if (!$this->connection->put($from, $to))
    {
      throw new BadCredentialsException('File download error');
    }

    $this->connection->disconnect();
  }

  public function getConnection ()
  {
    return $this->connection;
  }

  /**
   * @param string $filename
   * @return string
   * @throws DataSourceException
   */
  protected function resolveFileNameRegex ($filename)
  {
    if (!isset($this->resolvedNames[$filename]))
    {
      $directories = $this->getOption('directories');
      $pattern = $filename;
      $files = [];

      foreach ($directories as $directory)
      {
        foreach ($this->getConnection()->ls($directory) as $file)
        {
          if (preg_match($pattern, $file))
          {
            $files[] = array(
              'filename' => $file,
              'mtime' => $this->getConnection()->getLastModificationTime($file)
            );
          }
        }
      }

      if (count($files))
      {
        $this->resolvedNames[$filename] = $files[0]['filename'];

        return $this->resolvedNames[$filename];
      }

      throw new DataSourceException(sprintf('File %s not found', $filename));
    }

    return $this->resolvedNames[$filename];
  }

  protected function configureOptions (OptionsResolver $resolver)
  {
    parent::configureOptions($resolver);
    $resolver
      ->setRequired(['host', 'username', 'password', 'directories'])
      ->setDefault('passive_mode', false)
      ->setAllowedTypes('directories', 'array')
      ->setAllowedTypes('host', 'string')
      ->setAllowedTypes('username', 'string')
      ->setAllowedTypes('password', 'string')
      ->setAllowedTypes('passive_mode', 'boolean')
    ;
  }

  public function getLastModifyTime ($filename)
  {
    $ftp = $this->connection->connect();

    if (!$ftp)
    {
      throw new BadCredentialsException('Can not connect to FTP server');
    }

    $filename = $this->resolveFileNameRegex($filename);
    $mTime = $this->getConnection()->getLastModificationTime($filename);

    if ($mTime === -1)
    {
      throw new DataSourceException();
    }

    $date = new \DateTime();
    $date->setTimestamp($mTime);
    return $date;
  }

}