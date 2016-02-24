<?php
/**
 * Twig renderer for Behat report
 */

namespace elkan\BehatFormatter\Renderer;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Filter_Function;

class TwigRenderer implements RendererInterface {

    /**
     * Renders before an exercice.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeExercise($obj)
    {
        return '';
    }

    /**
     * Renders after an exercice.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterExercise($obj)
    {

        $templatePath = dirname(__FILE__).'/../../templates';
        $loader = new Twig_Loader_Filesystem($templatePath);
        $twig = new Twig_Environment($loader, array());
        $twig->addFilter('var_dump', new Twig_Filter_Function('var_dump'));
        $print = $twig->render('index.html.twig',
            array(
                'projectname'           => $obj->getProjectName(),
                'projectdescription'    => $obj->getProjectDescription(),
                'projectimage'          => $obj->getProjectImage(),
                'suites'                => $obj->getSuites(),
                'failedScenarios'       => $obj->getFailedScenarios(),
                'passedScenarios'       => $obj->getPassedScenarios(),
                'failedSteps'           => $obj->getFailedSteps(),
                'passedSteps'           => $obj->getPassedSteps(),
                'failedFeatures'        => $obj->getFailedFeatures(),
                'passedFeatures'        => $obj->getPassedFeatures(),
                'printStepArgs'         => $obj->getPrintArguments(),
                'printStepOuts'         => $obj->getPrintOutputs(),
                'printLoopBreak'        => $obj->getPrintLoopBreak(),
                'printShowTags'         => $obj->getPrintShowTags(),
                'buildDate'             => $obj->getBuildDate(),
            )
        );
        $obj->copyTempScreenshotDirectory();
        return $print;

    }

    /**
     * Renders before a suite.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeSuite($obj)
    {
        return '';
    }

    /**
     * Renders after a suite.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterSuite($obj)
    {
        return '';
    }

    /**
     * Renders before a feature.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeFeature($obj)
    {
        return '';
    }

    /**
     * Renders after a feature.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterFeature($obj)
    {
        return '';
    }

    /**
     * Renders before a scenario.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeScenario($obj)
    {
        return '';
    }

    /**
     * Renders after a scenario.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterScenario($obj)
    {
        return '';
    }

    /**
     * Renders before an outline.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeOutline($obj)
    {
        return '';
    }

    /**
     * Renders after an outline.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterOutline($obj)
    {
        return '';
    }

    /**
     * Renders before a step.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeStep($obj)
    {
        return '';
    }

    /**
     * Renders after a step.
     * @param object : BehatFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterStep($obj)
    {
        return '';
    }

    /**
     * To include CSS
     * @return string  : HTML generated
     */
    public function getCSS()
    {
        return '';

    }

    /**
     * To include JS
     * @return string  : HTML generated
     */
    public function getJS()
    {
        return '';
    }
}
