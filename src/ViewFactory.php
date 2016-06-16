<?php


namespace Peacock\View;


use TomWright\Singleton\SingletonTrait;

class ViewFactory
{

    use SingletonTrait;

    /**
     * @var string
     */
    protected $viewsDirectory;


    /**
     * ViewFactory constructor.
     */
    public function __construct()
    {
    }


    /**
     * @return string
     */
    public function getViewsDirectory()
    {
        return $this->viewsDirectory;
    }


    /**
     * @param string $viewsDirectory
     */
    public function setViewsDirectory($viewsDirectory)
    {
        $this->viewsDirectory = rtrim($viewsDirectory, " \t\n\r\0\x0B");
    }


    /**
     * @param $layout
     * @param array $data
     * @param array $parameters
     * @return Layout
     */
    public function layout($layout, array $data = [], array $parameters = [])
    {
        $layout = new Layout($layout);
        $layout->setViewsDirectory($this->getViewsDirectory());
        $layout->setData($data);
        $layout->setParameters($parameters);
        return $layout;
    }


    /**
     * @param $view
     * @param $section
     * @param array $data
     * @param null|bool $overwriteSection
     * @return View
     */
    public function view($view, $section, array $data = [], $overwriteSection = null)
    {
        $view = new View($view);
        $view->setViewsDirectory($this->getViewsDirectory());
        $view->setData($data);
        $view->setSectionName($section);

        if ($overwriteSection !== null) {
            if ($overwriteSection) {
                $view->appendParameter('overwrite-sections', $section);
            } else {
                $view->appendParameter('do-not-overwrite-sections', $section);
            }
        }

        return $view;
    }

}