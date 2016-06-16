<?php


class ChildViewLayoutTest extends TestBase
{

    public function testChildViewFromView()
    {
        $view = $this->factory->view('test_view', 'content');

        $child = $view->childLayout('test_view');

        $this->assertTrue(is_a($child, '\\Peacock\\View\\View'));
    }

    public function testChildLayoutFromView()
    {
        $view = $this->factory->view('test_view', 'content');

        $child = $view->childLayout('test_layout');

        $this->assertTrue(is_a($child, '\\Peacock\\View\\Layout'));
    }

    public function testChildViewFromLayout()
    {
        $layout = $this->factory->layout('test_layout');

        $child = $layout->childLayout('test_view');

        $this->assertTrue(is_a($child, '\\Peacock\\View\\View'));
    }

    public function testChildLayoutFromLayout()
    {
        $layout = $this->factory->layout('test_layout');

        $child = $layout->childLayout('test_layout');

        $this->assertTrue(is_a($child, '\\Peacock\\View\\Layout'));
    }

}