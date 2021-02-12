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

namespace Accurateweb\SynchronizationBundle\Model\Connection\Base;

class BaseConnection
{
  private $options = array();
  private $requiredOptions = array();

  public function __construct($options = array())
  {
    $this->configure($options);

    $currentOptionKeys = array_keys($this->options);
    $optionKeys = array_keys($options);

    if ($diff = array_diff($optionKeys, array_merge($currentOptionKeys, $this->requiredOptions)))
    {
      throw new \InvalidArgumentException(sprintf('%s does not support the following options: \'%s\'.', get_class($this), implode('\', \'', $diff)));
    }

    if ($diff = array_diff($this->requiredOptions, array_merge($currentOptionKeys, $optionKeys)))
    {
      throw new \RuntimeException(sprintf('%s requires the following options: \'%s\'.', get_class($this), implode('\', \'', $diff)));
    }

    $this->options = array_merge($this->options, $options);
  }

  protected function configure($options)
  {
    
  }

  public function addOption($name, $value = null)
  {
    $this->options[$name] = $value;
    return $this;
  }

  public function getOption($name)
  {
    return isset($this->options[$name]) ? $this->options[$name] : null;
  }

  public function setOption($name, $value)
  {
    if (!in_array($name, array_merge(array_keys($this->options), $this->requiredOptions)))
    {
      throw new \InvalidArgumentException(sprintf('%s does not support the following option: \'%s\'.', get_class($this), $name));
    }

    $this->options[$name] = $value;

    return $this;
  }

  public function hasOption($name)
  {
    return isset($this->options[$name]);
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function setOptions($values)
  {
    $this->options = $values;
    return $this;
  }

  public function addRequiredOption($name)
  {
    $this->requiredOptions[] = $name;
    return $this;
  }

  public function getRequiredOptions()
  {
    return $this->requiredOptions;
  }

}
