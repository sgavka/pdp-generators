<?php

declare(strict_types=1);

abstract class NewsParserParser
{
    private $pageUrl;

    public function __construct($pageUrl)
    {
        $this->pageUrl = $pageUrl;
    }

    public function parseTitles()
    {
        $dom = new DOMDocument();

        do {
            $this->loadPage($dom);

            foreach ($this->extractHeadings($dom) as $heading) {
                $title = trim($heading);
                yield $title;
            }
        } while ($this->nextPage($dom));
    }

    private function nextPage(DOMDocument $dom): bool
    {
        $nextPageUrl = $this->extractNextPageUrl($dom);
        if ($nextPageUrl) {
            $this->pageUrl = $nextPageUrl;
            return true;
        }

        return false;
    }

    abstract protected function extractNextPageUrl(DOMDocument $dom): ?string;

    private function loadPage(DOMDocument $dom): void
    {
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($this->pageUrl);
        libxml_clear_errors();
    }

    /**
     * @return Generator<string>
     */
    abstract protected function extractHeadings(DOMDocument $dom): Generator;
}

class MolBukParser extends NewsParserParser
{
    protected function extractNextPageUrl(DOMDocument $dom): ?string
    {
        $elements = $dom->getElementsByTagName('a');
        /** @var DOMNode $element */
        foreach ($elements as $element) {
            if ($element->nodeValue === 'Далі') {
                return $element->getAttribute('href');
            }
        }

        return null;
    }

    protected function extractHeadings(DOMDocument $dom): Generator
    {
        $elements = $dom->getElementsByTagName('div');
        /** @var DOMNode $element */
        foreach ($elements as $element) {
            if ($element->getAttribute('class') === 'short-1-title') {
                yield $element->nodeValue;
            }
        }
    }
}

$parser = new MolBukParser('https://molbuk.ua/index.php?do=lastnews');

foreach ($parser->parseTitles() as $title) {
    echo $title . PHP_EOL;
}
