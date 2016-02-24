<?php
/**
 * Created by PhpStorm.
 * User: nealv
 * Date: 05/01/15
 * Time: 12:50
 */

namespace elkan\BehatFormatter\Classes;


class Suite
{
    private $name;
    private $features;
    private $totalFeatures;
    private $failedFeatures;
    private $successFeatures;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param mixed $features
     */
    public function setFeatures($features)
    {
        $this->features = $features;
    }

    public function addFeature($feature)
    {
        $this->features[] = $feature;
    }
}
