<?php


use Peacock\View\ViewFactory;

class TestBase extends PHPUnit_Framework_TestCase
{

    /**
     * @var ViewFactory
     */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();

        $viewsDir = __DIR__ . '/views';

        $this->factory = ViewFactory::getInstance();
        $this->factory->setViewsDirectory($viewsDir);
    }

}