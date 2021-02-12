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

namespace Accurateweb\SynchronizationBundle\Model\Datasource\Base;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseDataSource
{
  protected $options = array();
  
  public function __construct($options = array())
  {
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
  }
  
  abstract public function get ($from, $to=null);
  
  protected function getOptions()
  {
    return $this->options;
  }

  protected function getOption ($name)
  {
    return $this->options[$name];
  }
  
  protected function getSavedName()
  {
    return tempnam(sfConfig::get("sf_data_dir"), null);
  }

  protected function configureOptions (OptionsResolver $resolver)
  {
  }
}