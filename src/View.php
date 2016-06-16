<?php


namespace Peacock\View;


class View
{

    /**
     * @var string
     */
    protected $viewsDirectory;

    /**
     * @var string
     */
    protected $layoutName;

    /**
     * @var array
     */
    protected $sections;

    /**
     * @var View[]
     */
    protected $childLayouts;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $sectionName;

    /**
     * @var string
     */
    protected $unParsedLayoutContent;

    /**
     * @var array
     */
    protected $parameters;


    /**
     * View constructor.
     * @param $layoutName
     * @param array $data
     */
    public function __construct($layoutName, array $data = [])
    {
        $this->sections = [];
        $this->childLayouts = [];
        $this->data = [];
        $this->unParsedLayoutContent = null;
        $this->parameters = [];
        $this->setLayoutName($layoutName);
        $this->setData($data);
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
     * @return mixed|string
     */
    public function parse()
    {
        foreach ($this->childLayouts as $childLayout) {
            $childLayout->parseSections($this);
        }

        $content = $this->getUnparsedLayoutContent();

        $pattern = '|(?:\{SECTION\:(.+?)\})(.*?)(?:\{END_SECTION\})|s';
        $content = preg_replace_callback($pattern, function ($matches) {
            list(, $sectionName, ) = $matches;
            $result = '';
            if (array_key_exists($sectionName, $this->sections)) {
                $result .= implode('', $this->sections[$sectionName]);
            }
            return $result;
        }, $content);

        return $content;
    }


    /**
     * @param $content
     * @return mixed
     */
    protected function parseParameters($content)
    {
        $pattern = '|(?:\{PARAMETER\:(.+?)\})(.*?)(?:\{END_PARAMETER\})|s';
        $content = preg_replace_callback($pattern, function ($matches) {
            list(, $name, $val) = $matches;
            $this->setParameter($name, $val);
            return '';
        }, $content);

        return $content;
    }


    /**
     * @param View $parentLayout
     * @param bool $mergeChildren
     * @return $this
     */
    public function parseSections(View $parentLayout, $mergeChildren = true)
    {
        // Get the unparsed content
        $content = $this->getUnparsedLayoutContent();

        $content = $this->parseParameters($content);

        // This is just a view, so the section will be $this->sectionName
        // and the content will be $content
        $this->appendToSection($this->getSectionName(), $content);

        if ($mergeChildren) {
            foreach ($this->childLayouts as $childLayout) {
                // Child adds their section content to this layouts section data.
                $childLayout->parseSections($this);
            }
        }

        $parentLayout->mergeChildSections($this);

        return $this;
    }


    /**
     * @return string
     */
    protected function getUnparsedLayoutContent()
    {
        if ($this->unParsedLayoutContent !== null) {
            return $this->unParsedLayoutContent;
        }

        $layoutName = $this->getLayoutName();
        $viewsPath = $this->getViewsDirectory() . '/';
        $layoutPath = "{$viewsPath}{$layoutName}.php";

        ob_start();
        extract($this->data);
        include $layoutPath;
        $contents = ob_get_clean();

        $this->unParsedLayoutContent = $contents;

        return $this->unParsedLayoutContent;
    }


    /**
     * Renders the layout.
     */
    public function render()
    {
        echo $this->parse();
    }


    /**
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }


    /**
     * @param string $layoutName
     * @return $this
     */
    public function setLayoutName($layoutName)
    {
        $this->layoutName = $layoutName;
        return $this;
    }


    /**
     * Creates a new Layout and adds it as a child, as well as merging the data down to it.
     * @param $layout
     * @param array $data
     * @param array $parameters
     * @return Layout
     */
    public function childLayout($layout, array $data = [], array $parameters = [])
    {
        if (is_string($layout)) {
            $factory = ViewFactory::getInstance();
            $layout = $factory->layout($layout, $data, $parameters);
        } else {
            $layout->setData($data);
        }

        $layout->mergeData($this);

        $this->childLayouts[] = $layout;

        return $layout;
    }


    /**
     * Creates a new View and adds it as a child, as well as merging the data down to it.
     * @param $view
     * @param $section
     * @param array $data
     * @param bool|null $overwriteSection
     * @return View
     */
    public function childView($view, $section, array $data = [], $overwriteSection = null)
    {
        if (is_string($view)) {
            $factory = ViewFactory::getInstance();
            $view = $factory->view($view, $section, $data, $overwriteSection);
        } else {
            $view->setData($data);
            $view->setSectionName($section);
        }

        $view->mergeData($this);

        if ($overwriteSection !== null) {
            if ($overwriteSection) {
                $view->appendParameter('overwrite-sections', $section);
            } else {
                $view->appendParameter('do-not-overwrite-sections', $section);
            }
        }

        $this->childLayouts[] = $view;

        return $view;
    }


    /**
     * Merges the parents data into the current layout, while prioritising existing data over parent data.
     * @param View $parentLayout
     */
    public function mergeData(View $parentLayout)
    {
        $parentData = $parentLayout->getData();
        $childData = $this->getData();
        $newData = array_merge($parentData, $childData);
        $this->setData($newData);
    }


    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }


    /**
     * Adds child sections to parent layout.
     * @param View $childLayout
     */
    public function mergeChildSections(View $childLayout)
    {
        $childSections = $childLayout->getSections();
        foreach ($childSections as $sectionName => $sectionData) {
            if ($childLayout->shouldOverwriteSection($sectionName)) {
                $this->appendParameter('overwrite-section', $sectionName);
                $this->initSection($sectionName, true);
            }
            foreach ($sectionData as $data) {
                $this->appendToSection($sectionName, $data);
            }
        }
    }


    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }


    /**
     * Initializes the specified section as an empty array.
     * @param $section
     * @param bool $clearIfExists
     */
    public function initSection($section, $clearIfExists = false)
    {
        if ($clearIfExists || ! array_key_exists($section, $this->sections)) {
            $this->sections[$section] = [];
        }
    }


    /**
     * @param $section
     * @param $body
     * @return $this
     */
    public function appendToSection($section, $body)
    {
        $this->initSection($section);
        $this->sections[$section][] = $body;

        return $this;
    }


    /**
     * @param $section
     * @param $body
     * @return $this
     */
    public function prependToSection($section, $body)
    {
        $this->initSection($section);
        array_unshift($this->sections[$section], $body);

        return $this;
    }


    /**
     * @return string
     */
    public function getSectionName()
    {
        return $this->sectionName;
    }


    /**
     * @param string $sectionName
     */
    public function setSectionName($sectionName)
    {
        $this->sectionName = $sectionName;
    }


    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        foreach ($parameters as $key => $val) {
            $this->setParameter($key, $val);
        }

        return $this;
    }


    /**
     * @param $name
     * @param $val
     * @return $this;
     */
    public function setParameter($name, $val)
    {
        if (is_string($val)) {
            $val = trim($val);
        }

        switch ($name) {
            case 'overwrite-sections':
            case 'do-not-overwrite-sections':
                if (is_string($val)) {
                    $val = explode("\n", $val);
                } elseif (! is_array($val)) {
                    $val = [$val];
                }
                break;
        }

        if (is_array($val)) {
            foreach ($val as $v) {
                $this->appendParameter($name, $v);
            }
        } else {
            $this->parameters[$name] = $val;
        }

        return $this;
    }


    /**
     * Similar to setParameter, but if $name is a parameter that contains an array, it will append to it.
     * @param $name
     * @param $appendVal
     * @return $this
     */
    public function appendParameter($name, $appendVal)
    {
        if (is_string($appendVal)) {
            $appendVal = trim($appendVal);
        }
        $currentVal = $this->getParameter($name);

        switch ($name) {
            case 'overwrite-sections':
            case 'do-not-overwrite-sections':
                if (is_string($appendVal)) {
                    $appendVal = explode("\n", $appendVal);
                }
                if (! is_array($appendVal)) {
                    $appendVal = [$appendVal];
                }
                if ($currentVal === null) {
                    $currentVal = [];
                } elseif (! is_array($currentVal)) {
                    $currentVal = [$currentVal];
                }
                $appendVal = array_unique(array_merge($currentVal, $appendVal));
                break;
        }

        $this->parameters[$name] = $appendVal;

        return $this;
    }


    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getParameter($name, $default = null)
    {
        $result = $default;

        if (array_key_exists($name, $this->parameters)) {
            $result = $this->parameters[$name];
        }

        return $result;
    }


    /**
     * @param $section
     * @return bool
     */
    public function shouldOverwriteSection($section)
    {
        $overwriteSections = $this->getParameter('overwrite-sections');
        $doNotOverwriteSections = $this->getParameter('do-not-overwrite-sections', []);
        $result = false;
        if (is_array($overwriteSections)) {
            if (in_array($section, $overwriteSections) || in_array('*', $overwriteSections)) {
                if (! in_array($section, $doNotOverwriteSections)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

}