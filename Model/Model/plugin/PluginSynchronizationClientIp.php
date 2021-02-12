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

class PluginSynchronizationClientIp extends BaseSynchronizationClientIp
{
  const VER_4 = 4;
  const VER_6 = 6;

  public function __toString()
  {
    $result = parent::__toString();

    $version = $this->getIpVersion();;
    switch ($version)
    {
      case self::VER_4:
        {
          $result = long2ip($this->getIpv4());;
          break;
        }
    }

    return $result;
  }

  public function getAddress()
  {
    $address = null;

    $ipVersion = $this->getIpVersion();
    switch ($ipVersion)
    {
      case self::VER_4:
        {
          $address = $this->getIpv4();
          break;

        }
      default:
        {
          throw new sfException(sprintf('Unsupported IP version: "%s"', $ipVersion));
        }
    }

    return $address;
  }

  public function getIpv6()
  {
    throw new sfException('IPv6 addresses are not supported at the moment');
  }

  public function setIpv6()
  {
    throw new sfException('IPv6 addresses are not supported at the moment');
  }

  public function setIpVersion($v)
  {
    if (!in_array((int)$v, array(self::VER_4)))
    {
      throw new InvalidArgumentException('Only IPv4 addresses supported');
    }

    parent::setIpVersion($v);
  }
}
