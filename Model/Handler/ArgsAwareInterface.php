<?php

namespace Accurateweb\SynchronizationBundle\Model\Handler;

/*
 * Options from cmd
 */
interface ArgsAwareInterface
{
  public function setCmdOptions($options);
}