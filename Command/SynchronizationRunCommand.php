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

namespace Accurateweb\SynchronizationBundle\Command;

use Accurateweb\SynchronizationBundle\Event\SynchronizationEvent;
use Accurateweb\SynchronizationBundle\Model\Configuration\SynchronizationServiceConfiguration;
use Accurateweb\SynchronizationBundle\Model\Input\InputDefinition;
use Accurateweb\SynchronizationBundle\Model\SynchronizationMode;
use Accurateweb\SynchronizationBundle\Model\SynchronizationResult;
use Accurateweb\SynchronizationBundle\Model\SynchronizationScenario;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SynchronizationRunCommand
 * @package Accurateweb\SynchronizationBundle\Command
 */
class SynchronizationRunCommand extends ContainerAwareCommand
{

  /** @var $logger Logger */
  private $logger;
  private $rootDir;
  private $dispatcher;

  function setVars()
  {
    $this->logger = $this->getContainer()->get('logger');
    $this->dispatcher = $this->getContainer()->get('event_dispatcher');
    $this->rootDir = $this->getContainer()->get('kernel')->getRootDir();

  }

  public function configure()
  {
    $this->setDefinition(new InputDefinition([], true));
    $desc = 'The [catalog:synchronize|INFO] task updates local database
using remote datasource.

Synchronized table depends on chosen subject

Example: php symfony synchronization:run --datasource=local --filename=/path catalog

Call it with:

  [php symfony synchronization:run|INFO]';

    $this
      ->setName('synchronization:run')
      ->addArgument('subject', InputOption::VALUE_OPTIONAL,
        'Synchronization subject or scenario')
      ->addOption('application', null, InputOption::VALUE_REQUIRED,
        'The application name')
      ->addOption('connection', null, InputOption::VALUE_REQUIRED,
        'The connection name', 'doctrine')
      ->addOption('datasource', null, InputOption::VALUE_REQUIRED, 'Datasource name')
      ->addOption('filename', null, InputOption::VALUE_OPTIONAL,
        'Filename to parse. If scenario is provided, this parameter is ignored')
      ->addOption('mode', null, InputOption::VALUE_OPTIONAL,
        'Synchronization mode. Must be "full" or "incremental"', SynchronizationMode::FULL)
      ->setDescription($desc);
  }

  public function execute(InputInterface $input, OutputInterface $output)
  {
    $this->setVars();
    $options = $input->getOptions();
    $exitCode = 0;
    $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

    $subject = $input->getArgument('subject');

    if (isset($subject[0]))
    {
      $subject = $subject[0];
    } else
    {
      throw new InvalidConfigurationException('Укажите сценарий');
    }

    $configuration = $this->getServiceConfiguration($options);
    $scenario = $configuration->getScenario($subject);

    if (is_null($scenario))
    {
      $scenario = new SynchronizationScenario($this->dispatcher);
      $scenario->addSubject($input->getArgument('subject'));
    }

    $scenario->preExecute();
    $service = $this->getContainer()->get('aw.synchronization.factory')->createSynchronizationService($configuration);

    foreach ($scenario as $subject)
    {
      $subject = is_array($subject) ? $subject[0] : $subject;

      $io->note(sprintf('synchronizing subject: %s...', $subject));

      $service->pull($subject, $input->getOptions());
      $io->note("SynchronizationResult: " . SynchronizationResult::OK);

      $this->logger->info("SynchronizationFinishedAt: " . date('d-m-Y H:i:s'));
      $this->getContainer()->get('event_dispatcher')->dispatch('synchronization.complete', new SynchronizationEvent($service, $subject, $options));
    }
    $io->note('Performing post-execute operations...');

    $scenario->postExecute();

    $this->logger->addInfo('complete');
    $io->success('Synchronization complete.');

    return $exitCode;
  }

  /**
   *
   * @param array $options
   * @return SynchronizationServiceConfiguration
   */
  protected function getServiceConfiguration($options)
  {
    $configuration = new SynchronizationServiceConfiguration($this->dispatcher, $this->rootDir);
    $configuration->setDbConnection($this->getConnection());
    $configuration->load($this->rootDir . "/config/parser.yml");
    $configuration->setLogger($this->logger);

    return $configuration;
  }

  protected function getConnection()
  {
    return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getConnection();
  }
}