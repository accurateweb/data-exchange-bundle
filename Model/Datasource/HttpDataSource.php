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

use Accurateweb\SynchronizationBundle\Model\Datasource\Base\BaseDataSource;

class HttpDataSource extends BaseDataSource
{

  public function get($from, $to = null)
  {
    $fh = fopen($to, 'w');
    
    $ch = curl_init($from);
    
    curl_setopt($ch, CURLOPT_FILE, $fh);
    curl_exec($ch);
    curl_close($ch);
    
    fclose($fh);
    
    return $to;
  }

}
