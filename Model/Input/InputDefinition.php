<?php


namespace Accurateweb\SynchronizationBundle\Model\Input;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition as BaseInputDefinition;
use Symfony\Component\Console\Input\InputOption;

class InputDefinition extends BaseInputDefinition
{
  /*
   * Игнорировать ошибки несуществющих аргументов и опций
   */
  private $ignoreEmptyDefinition;

  public function __construct (array $definition = [], $ignoreEmptyDefinition=false)
  {
    $this->ignoreEmptyDefinition = $ignoreEmptyDefinition;
    parent::__construct($definition);
  }

  public function hasArgument ($name)
  {
    $hasArgument = parent::hasArgument($name);

    if (!$hasArgument && $this->ignoreEmptyDefinition)
    {
      $this->addArgument(new InputArgument($name, InputArgument::OPTIONAL));
    }

    return $hasArgument;
  }

  public function hasOption ($name)
  {
    $hasOption = parent::hasOption($name);

    if (!$hasOption && $this->ignoreEmptyDefinition)
    {
      $this->addOption(new InputOption($name, null,InputArgument::OPTIONAL));
    }

    return $hasOption;
  }
}