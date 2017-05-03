<?php

namespace Redeye\BehatTapFormatter;


use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;
use Symfony\Component\Yaml\Yaml;

class TapFormatter implements Formatter
{
    /**
     * @var OutputPrinter
     */
    protected $printer;

    /** @var CallResult|null */
    private $failedStep;

    private $stepNo = 0;

    /**
     * TapFormatter constructor.
     * 
     * @param OutputPrinter $printer
     */
    public function __construct(OutputPrinter $printer)
    {
        $this->printer = $printer;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            SuiteTested::BEFORE    => 'onBeforeSuiteTested',
            SuiteTested::AFTER     => 'onAfterSuiteTested',
            ScenarioTested::BEFORE => 'onBeforeScenarioTested',
            ScenarioTested::AFTER  => 'onAfterScenarioTested',
            OutlineTested::BEFORE  => 'onBeforeOutlineTested',
            OutlineTested::AFTER   => 'onAfterOutlineTested',
            StepTested::AFTER      => 'saveFailedStep',
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'tap';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
    }

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
    }

    /**
     * Returns parameter name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
    }

    /**
     * Returns formatter output printer.
     *
     * @return OutputPrinter
     */
    public function getOutputPrinter()
    {
        return $this->printer;
    }

    public function saveFailedStep(AfterStepTested $event)
    {
        $result = $event->getTestResult();

        if (TestResult::FAILED === $result->getResultCode()) {
            $this->failedStep = $result;
        }
    }

    public function onBeforeSuiteTested(BeforeSuiteTested $event)
    {
        $this->printer->writeln("TAP version 13");
    }

    public function onAfterSuiteTested(SuiteTested $event)
    {
        $this->printer->writeln(sprintf("1..%d", $this->stepNo));
        $this->stepNo = 0;
    }

    public function onBeforeScenarioTested(BeforeScenarioTested $event)
    {
        $this->beforeTest($event->getScenario());
    }

    public function onAfterScenarioTested(AfterScenarioTested $event)
    {
        $this->afterTest($event->getTestResult(), $event->getScenario());
    }

    public function onBeforeOutlineTested(BeforeOutlineTested $event)
    {
        $this->beforeTest($event->getOutline());
    }

    public function onAfterOutlineTested(AfterOutlineTested $event)
    {
        $this->afterTest($event->getTestResult(), $event->getOutline());
    }

    private function beforeTest(ScenarioLikeInterface $scenario)
    {
        $this->failedStep = null;
        $this->stepNo++;
    }

    private function afterTest(TestResult $result, ScenarioLikeInterface $scenario)
    {
        switch ($result->getResultCode()) {
            case TestResult::SKIPPED:
                $this->printer->writeln(sprintf("ok %d #skip %s", $this->stepNo, $scenario->getTitle()));
                break;
            case TestResult::PASSED:
                $this->printer->writeln(sprintf("ok %d %s", $this->stepNo, $scenario->getTitle()));
                break;
            case TestResult::FAILED:
                $failedParams = [];
                if ($this->failedStep && $this->failedStep->hasException()) {
                    switch (true) {
                        case ($this->failedStep instanceof ExceptionResult && $this->failedStep->hasException()):
                            $exception = $this->failedStep->getException();
                            $failedParams['message'] = $exception->getMessage();
                            $failedParams['details'] = $exception->getTraceAsString();
                            break;
                        default:
                            $failedParams['message'] = sprintf("Unknown error in %s", get_class($this->failedStep));
                            break;
                    }
                }

                $this->printer->writeln(sprintf("not ok %d %s", $this->stepNo, $scenario->getTitle()));
                $this->printer->writeln('---');
                $this->printer->write(Yaml::dump($failedParams, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
                $this->printer->writeln('...');
                break;
        }
    }
}
