<?php
namespace elkan\BehatFormatter\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Class BehatFormatterContext
 *
 * @package elkan\BehatFormatter\Context
 */
class BehatFormatterContext extends MinkContext implements SnippetAcceptingContext
    {
    private $currentScenario;
    protected static $currentSuite;

    /**
     * @BeforeFeature
     *
     * @param BeforeFeatureScope $scope
     *
     */
    public static function setUpScreenshotSuiteEnvironment4ElkanBehatFormatter(BeforeFeatureScope $scope)
    {
        self::$currentSuite = $scope->getSuite()->getName();
    }

    /**
     * @BeforeScenario
     */
    public function setUpScreenshotScenarioEnvironmentElkanBehatFormatter(BeforeScenarioScope $scope)
    {
        $this->currentScenario = $scope->getScenario();
    }

    /**
     * Take screen-shot when step fails.
     * Take screenshot on result step (Then)
     * Works only with Selenium2Driver.
     *
     * @AfterStep
     * @param AfterStepScope $scope
     */
    public function afterStepScreenShotOnFailure(AfterStepScope $scope)
    {
        $currentSuite = self::$currentSuite;

        //if test has failed, and is not an api test, get screenshot
        if(!$scope->getTestResult()->isPassed() || $scope->getStep()->getKeywordType() === "Then")
        {
            $driver = $this->getSession()->getDriver();
            if (!$driver instanceof Selenium2Driver) {
                return;
            }

            //create filename string
            $fileName = $currentSuite.".".basename($scope->getFeature()->getFile()).'.'.$scope->getStep()->getLine().'.png';
            $fileName = str_replace('.feature', '', $fileName);

            /*
             * Determine destination folder!
             * This must be equal to the printer output path.
             * How the fuck do I get that in here???
             *
             * Fuck it, create a temporary folder for the screenshots and
             * let the Printer copy those to the assets folder.
             * Spend too many time here! And output is not the contexts concern, it's the printers concern.
             */

            $temp_destination = getcwd().DIRECTORY_SEPARATOR.".tmp_behatFormatter";
            if (! is_dir($temp_destination)) {
                mkdir($temp_destination, 0777, true);
            }

            $this->saveScreenshot($fileName, $temp_destination);
        }
    }
}