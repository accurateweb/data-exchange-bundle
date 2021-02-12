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

class PluginSynchronizationClient extends BaseSynchronizationClient
{
  public function getAuthKey()
  {
    return $this->checkAuthKey();
  }

  protected function generateAuthKey()
  {
    return md5(uniqid('syncli', true));
  }

  protected function checkAuthKey()
  {
    $authKey = parent::getAuthKey();

    if (!$authKey)
    {
      $authKey = $this->generateAuthKey();
      $this->setAuthKey($authKey);
    }

    return $authKey;
  }

  public function preInsert($con = null)
  {
    $this->checkAuthKey();
    return true;
  }
}