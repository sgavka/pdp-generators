<?php

declare(strict_types=1);

class NewsParser
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
            libxml_use_internal_errors(true);
            $dom->loadHTMLFile($this->pageUrl);
            libxml_clear_errors();

            $headings = $dom->getElementsByTagName('div');
            /** @var DOMNode $heading */
            foreach ($headings as $heading) {
                if ($heading->getAttribute('class') === 'short-1-title') {
                    $title = trim($heading->nodeValue);
                    yield $title;
                }
            }
        } while ($this->nextPage($dom));
    }

    private function nextPage(DOMDocument $dom): bool
    {
        $aElements = $dom->getElementsByTagName('a');
        /** @var DOMNode $aElement */
        foreach ($aElements as $aElement) {
            if ($aElement->nodeValue === 'Далі') {
                $this->pageUrl = $aElement->getAttribute('href');
                echo 'Page: ' . $this->pageUrl . PHP_EOL;

                return true;
            }
        }

        return false;
    }
}

$parser = new NewsParser('https://molbuk.ua/index.php?do=lastnews');

foreach ($parser->parseTitles() as $title) {
    echo $title . PHP_EOL;
}
