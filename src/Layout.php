<?php


namespace Peacock\View;


class Layout extends View
{

    /**
     * @var array
     */
    protected $data;


    /**
     * Layout constructor.
     * @param $layoutName
     * @param array $data
     */
    public function __construct($layoutName, array $data = [])
    {
        parent::__construct($layoutName, $data);
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

        // Run the section replacement based on the content, and build up the internal sections.
        $pattern = '|(?:\{SECTION\:(.+?)\})(.*?)(?:\{END_SECTION\})|s';
        preg_replace_callback($pattern, function ($matches) use ($parentLayout) {
            list(, $sectionName, $sectionContent) = $matches;
            $this->appendToSection($sectionName, $sectionContent);
        }, $content);

        if ($mergeChildren) {
            foreach ($this->childLayouts as $childLayout) {
                // Child adds their section content to this layouts section data.
                $childLayout->parseSections($this);
            }
        }

        $parentLayout->mergeChildSections($this);

        return $this;
    }

}