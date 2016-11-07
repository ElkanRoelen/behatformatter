<?php

namespace elkan\BehatFormatter\Formatter;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Testwork\Counter\Memory;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\Output\Exception\BadOutputPathException;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use elkan\BehatFormatter\Classes\Feature;
use elkan\BehatFormatter\Classes\Scenario;
use elkan\BehatFormatter\Classes\Step;
use elkan\BehatFormatter\Classes\Suite;
use elkan\BehatFormatter\Printer\FileOutputPrinter;
use elkan\BehatFormatter\Renderer\BaseRenderer;
use Behat\Behat\Hook\Scope\AfterStepScope;
use elkan\BehatFormatter\Context;


/**
 * Class BehatFormatter
 * @package tests\features\formatter
 */
class BehatFormatter implements Formatter {

    //<editor-fold desc="Variables">
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $timer;

    /**
     * @var
     */
    private $memory;

    /**
     * @param String $outputPath where to save the generated report file
     */
    private $outputPath;

    /**
     * @param String $base_path Behat base path
     */
    private $base_path;

    /**
     * Printer used by this Formatter and Context
     * @param $printer OutputPrinter
     */
    private $printer;

    /**
     * Renderer used by this Formatter
     * @param $renderer BaseRenderer
     */
    private $renderer;

    /**
     * Flag used by this Formatter
     * @param $print_args boolean
     */
    private $print_args;

    /**
     * Flag used by this Formatter
     * @param $print_outp boolean
     */
    private $print_outp;

    /**
     * Flag used by this Formatter
     * @param $loop_break boolean
     */
    private $loop_break;

    /**
     * Flag used by this Formatter
     * @param $show_tags boolean
     */
    private $show_tags;

    /**
     * Flag used by this Formatter
     * @var string
     */
    private $projectname;

    /**
     * Flag used by this Formatter
     * @var string
     */
    private $projectdescription;

    /**
     * Flag used by this Formatter
     * @var string
     */
    private $projectimage;

    /**
     * @var Array
     */
    private $suites;

    /**
     * @var Suite
     */
    private $currentSuite;

    /**
     * @var int
     */
    private $featureCounter = 1;

    /**
     * @var Feature
     */
    private $currentFeature;

    /**
     * @var Scenario
     */
    private $currentScenario;

    /**
     * @var int
     */
    private $currentExampleCount;

    /**
     * @var Array|null
     */
    private $currentExampleLines;

    /**
     * @var Scenario[]
     */
    private $failedScenarios;

    /**
     * @var Scenario[]
     */
    private $passedScenarios;

    /**
     * @var Feature[]
     */
    private $failedFeatures;

    /**
     * @var Feature[]
     */
    private $passedFeatures;

    /**
     * @var Step[]
     */
    private $failedSteps;

    /**
     * @var Step[]
     */
    private $passedSteps;

    /**
     * @var Step[]
     */
    private $pendingSteps;

    /**
     * @var Step[]
     */
    private $skippedSteps;


    //</editor-fold>

    //<editor-fold desc="Formatter functions">
    /**
     * @param $name
     * @param $base_path
     */
    function __construct($name, $projectName, $projectImage, $projectDescription, $renderer, $filename, $print_args, $print_outp, $loop_break, $show_tags, $base_path)
    {
        $this->projectname = $projectName;
        $this->projectimage = $projectImage;
        $this->projectdescription = $projectDescription;
        $this->name = $name;
        $this->base_path = $base_path;
        $this->print_args = $print_args;
        $this->print_outp = $print_outp;
        $this->loop_break = $loop_break;
        $this->show_tags = $show_tags;
        $this->renderer = new BaseRenderer($renderer, $base_path);
        $this->printer = new FileOutputPrinter($this->renderer->getNameList(), $filename, $base_path);
        $this->timer = new Timer();
        $this->memory = new Memory();

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            'tester.exercise_completed.before' => 'onBeforeExercise',
            'tester.exercise_completed.after'  => 'onAfterExercise',
            'tester.suite_tested.before'       => 'onBeforeSuiteTested',
            'tester.suite_tested.after'        => 'onAfterSuiteTested',
            'tester.feature_tested.before'     => 'onBeforeFeatureTested',
            'tester.feature_tested.after'      => 'onAfterFeatureTested',
            'tester.scenario_tested.before'    => 'onBeforeScenarioTested',
            'tester.scenario_tested.after'     => 'onAfterScenarioTested',
            'tester.outline_tested.before'     => 'onBeforeOutlineTested',
            'tester.outline_tested.after'      => 'onAfterOutlineTested',
            'tester.step_tested.after'         => 'onAfterStepTested',
        );
    }

    /**
     * Returns formatter name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectname;
    }

    /**
     * Copy temporary screenshot folder to the assets folder
     */
    public function copyTempScreenshotDirectory(){
        $source = getcwd().DIRECTORY_SEPARATOR.".tmp_behatFormatter";
        $destination = $this->printer->getOutputPath() . DIRECTORY_SEPARATOR . 'assets'.DIRECTORY_SEPARATOR.'screenshots';

        if (is_dir($destination)) {
            $this->delTree("$destination");
        }
        if (is_dir($source)) {
            rename($source, $destination);
        }
    }

    /**
     * Delete recursive directory
     * @return true or false
     */
    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file") && !is_link($dir)) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * @return string
     */
    public function getProjectImage()
    {
        $imagepath = realpath($this->projectimage);
        if ($imagepath === FALSE || $this->projectimage === null) {
            //There is no image to copy
            return null;
        }

        // Copy project image to assets folder

        //first create the assets dir
        $destination = $this->printer->getOutputPath() . DIRECTORY_SEPARATOR . 'assets';
        @mkdir($destination);

        $filename = basename($imagepath);
        copy($imagepath, $destination . DIRECTORY_SEPARATOR . $filename);

        return "assets/".$filename;
    }

    /**
     * @return string
     */
    public function getProjectDescription()
    {
        return $this->projectdescription;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * Returns formatter description.
     * @return string
     */
    public function getDescription()
    {
        return "Elkan's behat Formatter";
    }

    /**
     * Returns formatter output printer.
     * @return OutputPrinter
     */
    public function getOutputPrinter()
    {
        return $this->printer;
    }

    /**
     * Sets formatter parameter.
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[ $name ] = $value;
    }

    /**
     * Returns parameter name.
     * @param string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->parameters[ $name ];
    }

    /**
     * Verify that the specified output path exists or can be created,
     * then sets the output path.
     * @param String $path Output path relative to %paths.base%
     * @throws BadOutputPathException
     */
    public function setOutputPath($path)
    {
        $outpath = realpath($this->base_path.DIRECTORY_SEPARATOR.$path);
        if(!file_exists($outpath)) {
            if(!mkdir($outpath, 0755, true)) {
                throw new BadOutputPathException(
                    sprintf(
                        'Output path %s does not exist and could not be created!',
                        $outpath
                    ),
                    $outpath
                );
            }
        } else {
            if(!is_dir($outpath)) {
                throw new BadOutputPathException(
                    sprintf(
                        'The argument to `output` is expected to the a directory, but got %s!',
                        $outpath
                    ),
                    $outpath
                );
            }
        }
        $this->outputPath = $outpath;
    }

    /**
     * Returns output path
     * @return String output path
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * Returns if it should print the step arguments
     * @return boolean
     */
    public function getPrintArguments()
    {
        return $this->print_args;
    }

    /**
     * Returns if it should print the step outputs
     * @return boolean
     */
    public function getPrintOutputs()
    {
        return $this->print_outp;
    }

    /**
     * Returns if it should print scenario loop break
     * @return boolean
     */
    public function getPrintLoopBreak()
    {
        return $this->loop_break;
    }

    /**
     * Returns date and time
     * @return string
     */
    public function getBuildDate()
    {
        $datetime = (date('I')) ? 7200 : 3600;
        return gmdate("D d M Y H:i", time() + $datetime);
    }


    /**
     * Returns if it should print tags
     * @return boolean
     */
    public function getPrintShowTags()
    {
        return $this->show_tags;
    }

    public function getTimer()
    {
        return $this->timer;
    }

    public function getMemory()
    {
        return $this->memory;
    }

    public function getSuites()
    {
        return $this->suites;
    }

    public function getCurrentSuite()
    {
        return $this->currentSuite;
    }

    public function getFeatureCounter()
    {
        return $this->featureCounter;
    }

    public function getCurrentFeature()
    {
        return $this->currentFeature;
    }

    public function getCurrentScenario()
    {
        return $this->currentScenario;
    }

    public function getFailedScenarios()
    {
        return $this->failedScenarios;
    }

    public function getPassedScenarios()
    {
        return $this->passedScenarios;
    }

    public function getFailedFeatures()
    {
        return $this->failedFeatures;
    }

    public function getPassedFeatures()
    {
        return $this->passedFeatures;
    }

    public function getFailedSteps()
    {
        return $this->failedSteps;
    }

    public function getPassedSteps()
    {
        return $this->passedSteps;
    }

    public function getPendingSteps()
    {
        return $this->pendingSteps;
    }

    public function getSkippedSteps()
    {
        return $this->skippedSteps;
    }
    //</editor-fold>

    //<editor-fold desc="Event functions">
    /**
     * @param BeforeExerciseCompleted $event
     */
    public function onBeforeExercise(BeforeExerciseCompleted $event)
    {
        $this->timer->start();

        $print = $this->renderer->renderBeforeExercise($this);
        $this->printer->write($print);
    }

    /**
     * @param AfterExerciseCompleted $event
     */
    public function onAfterExercise(AfterExerciseCompleted $event)
    {

        $this->timer->stop();

        $print = $this->renderer->renderAfterExercise($this);
        $this->printer->writeln($print);
    }

    /**
     * @param BeforeSuiteTested $event
     */
    public function onBeforeSuiteTested(BeforeSuiteTested $event)
    {
        $this->currentSuite = new Suite();
        $this->currentSuite->setName($event->getSuite()->getName());

        $print = $this->renderer->renderBeforeSuite($this);
        $this->printer->writeln($print);
    }

    /**
     * @param AfterSuiteTested $event
     */
    public function onAfterSuiteTested(AfterSuiteTested $event)
    {

        $this->suites[] = $this->currentSuite;

        $print = $this->renderer->renderAfterSuite($this);
        $this->printer->writeln($print);
    }

    /**
     * @param BeforeFeatureTested $event
     */
    public function onBeforeFeatureTested(BeforeFeatureTested $event)
    {
        $feature = new Feature();
        $feature->setId($this->featureCounter);
        $this->featureCounter++;
        $feature->setName($event->getFeature()->getTitle());
        $feature->setDescription($event->getFeature()->getDescription());
        $feature->setTags($event->getFeature()->getTags());
        $feature->setFile($event->getFeature()->getFile());
        $feature->setScreenshotFolder($event->getSuite()->getName().'/'.$event->getFeature()->getTitle());
        $this->currentFeature = $feature;

        $print = $this->renderer->renderBeforeFeature($this);
        $this->printer->writeln($print);
    }

    /**
     * @param AfterFeatureTested $event
     */
    public function onAfterFeatureTested(AfterFeatureTested $event)
    {
        $this->currentSuite->addFeature($this->currentFeature);
        if($this->currentFeature->allPassed()) {
            $this->passedFeatures[] = $this->currentFeature;
        } else {
            $this->failedFeatures[] = $this->currentFeature;
        }

        $print = $this->renderer->renderAfterFeature($this);
        $this->printer->writeln($print);
    }

    /**
     * @param BeforeScenarioTested $event
     */
    public function onBeforeScenarioTested(BeforeScenarioTested $event)
    {
        $scenario = new Scenario();
        $scenario->setName($event->getScenario()->getTitle());
        $scenario->setTags($event->getScenario()->getTags());
        $scenario->setLine($event->getScenario()->getLine());
        $scenario->setScreenshotName($event->getScenario()->getTitle());
        $this->currentScenario = $scenario;

        $print = $this->renderer->renderBeforeScenario($this);
        $this->printer->writeln($print);
    }

    /**
     * @param AfterScenarioTested $event
     */
    public function onAfterScenarioTested(AfterScenarioTested $event)
    {
        $scenarioPassed = $event->getTestResult()->isPassed();

        if($scenarioPassed) {
            $this->passedScenarios[] = $this->currentScenario;
            $this->currentFeature->addPassedScenario();
        } else {
            $this->failedScenarios[] = $this->currentScenario;
            $this->currentFeature->addFailedScenario();
        }

        $this->currentScenario->setLoopCount(1);
        $this->currentScenario->setPassed($event->getTestResult()->isPassed());
        $this->currentFeature->addScenario($this->currentScenario);

        $print = $this->renderer->renderAfterScenario($this);
        $this->printer->writeln($print);
    }

    /**
     * @param BeforeOutlineTested $event
     */
    public function onBeforeOutlineTested(BeforeOutlineTested $event)
    {
        $scenario = new Scenario();
        $scenario->setName($event->getOutline()->getTitle());
        $scenario->setTags($event->getOutline()->getTags());
        $scenario->setLine($event->getOutline()->getLine());
        $this->currentScenario = $scenario;
        $this->currentExampleCount = 0;
        $this->currentExampleLines = array_map(function ($example) {
            return $example->getLine();
        }, $event->getOutline()->getExamples());

        $print = $this->renderer->renderBeforeOutline($this);
        $this->printer->writeln($print);
    }

    /**
     * @param AfterOutlineTested $event
     */
    public function onAfterOutlineTested(AfterOutlineTested $event)
    {
        $scenarioPassed = $event->getTestResult()->isPassed();

        if($scenarioPassed) {
            $this->passedScenarios[] = $this->currentScenario;
            $this->currentFeature->addPassedScenario();
        } else {
            $this->failedScenarios[] = $this->currentScenario;
            $this->currentFeature->addFailedScenario();
        }

        $this->currentScenario->setLoopCount(sizeof($event->getTestResult()));
        $this->currentScenario->setPassed($event->getTestResult()->isPassed());
        $this->currentFeature->addScenario($this->currentScenario);
        $this->currentExampleLines = null;

        $print = $this->renderer->renderAfterOutline($this);
        $this->printer->writeln($print);
    }

    /**
     * @param BeforeStepTested $event
     */
    public function onBeforeStepTested(BeforeStepTested $event)
    {
        $print = $this->renderer->renderBeforeStep($this);
        $this->printer->writeln($print);
    }


    /**
     * @param AfterStepTested $event
     */
    public function onAfterStepTested(AfterStepTested $event)
    {
        $result = $event->getTestResult();

        //$this->dumpObj($event->getStep()->getArguments());
        /** @var Step $step */
        $step = new Step();
        $step->setKeyword($event->getStep()->getKeyword());
        $step->setText(Context\BehatFormatterContext::transform($event->getStep()->getText()));
        $step->setLine($event->getStep()->getLine());
        $step->setArguments($event->getStep()->getArguments());
        $step->setResult($result);
        $step->setResultCode($result->getResultCode());

        //What is the result of this step ?
        if(is_a($result, 'Behat\Behat\Tester\Result\UndefinedStepResult')) {
            //pending step -> no definition to load
            $this->pendingSteps[] = $step;
        } else {
            if(is_a($result, 'Behat\Behat\Tester\Result\SkippedStepResult')) {
                //skipped step
                /** @var ExecutedStepResult $result */
                $step->setDefinition($result->getStepDefinition());
                $this->skippedSteps[] = $step;
            } else {
                //failed or passed
                if($result instanceof ExecutedStepResult) {
                    $step->setDefinition($result->getStepDefinition());
                    $exception = $result->getException();
                    if($exception) {
                        $step->setException($exception->getMessage());
                        $this->failedSteps[] = $step;
                    } else {
                        $step->setOutput($result->getCallResult()->getStdOut());
                        $this->passedSteps[] = $step;
                    }
                }
            }
        }
        if(($step->getResultCode() == "99") || ($step->getResult()->isPassed() && $step->getKeyword() === "Then")){
            $scenarioLine = empty($this->currentExampleLines) ? $this->currentScenario->getLine() : $this->currentExampleLines[$this->currentExampleCount++];
            $screenshot = $event->getSuite()->getName().".".basename($event->getFeature()->getFile()).".$scenarioLine.".$event->getStep()->getLine().".png";
            $screenshot = str_replace('.feature', '', $screenshot);

            if (file_exists(getcwd().DIRECTORY_SEPARATOR.".tmp_behatFormatter".DIRECTORY_SEPARATOR.$screenshot)){
                $screenshot = 'assets'.DIRECTORY_SEPARATOR.'screenshots'.DIRECTORY_SEPARATOR.$screenshot;
                $step->setScreenshot($screenshot);
            }
        }
        $this->currentScenario->addStep($step);

        $print = $this->renderer->renderAfterStep($this);
        $this->printer->writeln($print);

    }
    //</editor-fold>

    /**
     * @param $text
     */
    public function printText($text)
    {
        file_put_contents('php://stdout', $text);
    }

    /**
     * @param $obj
     */
    public function dumpObj($obj)
    {
        ob_start();
        var_dump($obj);
        $result = ob_get_clean();
        $this->printText($result);
    }
}
