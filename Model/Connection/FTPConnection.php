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

namespace Accurateweb\SynchronizationBundle\Model\Connection;

use Accurateweb\SynchronizationBundle\Model\Connection\Base\BaseConnection;

class FTPConnection extends BaseConnection
{
  private $rs;

  protected function configure($options)
  {
    $this->addRequiredOption('host');
    $this->addRequiredOption('username');
    $this->addRequiredOption('password');
    $this->addOption('directories');
    $this->addOption('passive_mode', false);
  }

  public function chdir($directory)
  {
    return @ftp_chdir($this->rs, $directory);
  }

  public function connect()
  {
    if ($this->rs)
    {
      $this->disconnect();
    }

    $rs = ftp_connect($this->getOption('host'));

    if ($rs)
    {
      $this->rs = $rs;
      $loggedIn = ftp_login($this->rs, $this->getOption('username'), $this->getOption('password'));
      if ($loggedIn && $this->getOption('passive_mode'))
      {
        @ftp_pasv($this->rs, true);
      }
    }

    if (!$rs || !$loggedIn)
    {
      return false;
    }

    return $this->rs;
  }

  public function disconnect()
  {
    if (ftp_close($this->rs))
    {
      $this->rs = null;
    }
  }

  public function get($src, $dest, $passive_mode = false)
  {
    $result = @ftp_get($this->rs, $dest, $src, FTP_BINARY);
    return $result;
  }

  public function put($src, $dest)
  {
    $result = @ftp_put($this->rs, $dest, $src, FTP_BINARY);
    return $result;
  }

  public function connected()
  {
    return (bool) $this->rs;
  }

  public function ls($directory)
  {
    return @ftp_nlist($this->rs, $directory);
  }

  public function getLastModificationTime($path)
  {
    return @ftp_mdtm($this->rs, $path);
  }

  public function delete($path)
  {
    return @ftp_delete($this->rs, $path);
  }

  public function rename($oldname, $newname)
  {
    return @ftp_rename($this->rs, $oldname, $newname);
  }

  public function file_exists($filename)
  {
    $fileList = $this->ls(dirname($filename));
    return in_array($filename, $fileList);
  }

  public function file_size($filename)
  {
    return @ftp_size($this->rs, $filename);
  }

}
