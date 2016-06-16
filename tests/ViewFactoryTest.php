<?php


class ViewFactoryTest extends TestBase
{

    public function testNewView()
    {
        $view = $this->factory->view('test_view', 'content');

        $this->assertTrue(is_a($view, '\\Peacock\\View\\View'));
    }

    public function testNewLayout()
    {
        $view = $this->factory->layout('test_layout');

        $this->assertTrue(is_a($view, '\\Peacock\\View\\Layout'));
    }

}