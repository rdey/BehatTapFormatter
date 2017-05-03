<?php

namespace Redeye\BehatTapFormatter;

use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TapFormatterExtension implements Extension
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function getConfigKey()
    {
        return 'tap';
    }

    /**
     * @inheritdoc
     */
    public function initialize(ExtensionManager $extensionManager)
    {

    }

    /**
     * @inheritdoc
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * @inheritdoc
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $outputDefinition = new Reference('cli.output');
        $outputPrinterDefinition = new Definition('Redeye\\BehatTapFormatter\\ConsoleOutput', array($outputDefinition));

        $definition = new Definition("Redeye\\BehatTapFormatter\\TapFormatter", array($outputPrinterDefinition));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 90));

        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.tap', $definition);
    }
}
