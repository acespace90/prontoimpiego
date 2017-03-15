<?php

namespace AppBundle\Twig\Extension;

class SvgImporterExtension extends \Twig_Extension
{
    /**
     * @var
     */
    private $baseDir;

    public function __construct()
    {
        $this->baseDir = realpath(__DIR__ . '/../../../../');
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('import_svg', [$this, 'loadSvg'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'fazland_svg_importer';
    }

    public function loadSvg($filename, array $options = array ())
    {
        if (empty($filename)) {
            return '';
        }

        if (! file_exists($filename)) {
            return '';
        }

        $filename = $this->baseDir.'/'.$filename;


        $document = new \DOMDocument();
        $document->load($filename);

        /** @var \DOMElement $element */
        foreach ($document->getElementsByTagName('svg') as $element)
        {
            if (isset ($options['height']) && isset ($options['width']) && ! $element->hasAttribute('viewBox')) {
                $element->setAttribute('viewBox', '0 0 '.$options['height'].' '.$options['width']);
            }

            if (isset ($options['class'])) {
                $element->setAttribute('class', $options['class']);
            }

            if (isset ($options['id'])) {
                $element->setAttribute('id', $options['id']);
            }
        }

        return $document->saveHTML();
    }
}
