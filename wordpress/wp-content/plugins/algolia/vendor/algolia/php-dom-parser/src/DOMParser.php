<?php

namespace Algolia;

final class DOMParser
{
    /**
     * An array of attributeName => domSelector.
     * Order matters and will determine the hierarchy of the page.
     *
     * @var array
     */
    private $attributes = array();

    /**
     * These attributes will be used if no attribute selectors
     * were set with setAttributeSelectors.
     *
     * @var array
     */
    private $defaultAttributes = array(
        'title1'  => 'h1',
        'title2'  => 'h2',
        'title3'  => 'h3',
        'title4'  => 'h4',
        'title5'  => 'h5',
        'title6'  => 'h6',
        'content' => 'p, ul, ol, dl, table',
    );

    /**
     * Keeps track of the current depth in the hierarchy during parsing.
     *
     * @var int
     */
    private $currentLevel = -1;

    /**
     * The resulting objects array buffer.
     *
     * @var array
     */
    private $parsedObjects = array();

    /**
     * Holds track of the next record that will be published once
     * we reach a conflict in the hierarchy.
     *
     * @var array
     */
    private $currentObject = array();

    /**
     * The CSS starting point selector. If none were set we parse everything.
     * If this matches multiple nodes, every match will be parsed.
     *
     * @var string
     */
    private $rootSelector;

    /**
     * All nodes matching the listed selectors
     * will not be part of the resulting records.
     *
     * @var array
     */
    private $exclude = array();

    /**
     * Attributes that will be added to every record has is.
     * The removes the need to manually travers the resulting
     * fetched records just to insert these fields.
     *
     * @var array
     */
    private $sharedAttributes = array();

    /**
     * Reset the state of the parser.
     */
    private function init()
    {
        if (empty($this->attributes)) {
            $this->attributes = $this->defaultAttributes;
        }

        $this->currentLevel = -1;
        $this->parsedObjects = array();
        $this->currentObject = $this->getNewEmptyObject();
    }

    /**
     * @param $attributes
     */
    public function setSharedAttributes($attributes)
    {
        $this->sharedAttributes = $attributes;
    }

    /**
     * @param string $selector
     */
    public function setRootSelector($selector)
    {
        $this->rootSelector = (string) $selector;
    }

    /**
     * Removes the root selector.
     */
    public function removeRootSelector()
    {
        $this->rootSelector = null;
    }

    /**
     * @param array $selectors
     */
    public function setAttributeSelectors(array $selectors)
    {
        $this->attributes = $selectors;
    }

    /**
     * @param array $selectors
     */
    public function setExcludeSelectors(array $selectors)
    {
        $this->exclude = $selectors;
    }

    /**
     * @return array
     */
    private function getNewEmptyObject()
    {
        $object = array();
        foreach ($this->attributes as $attributeKey => $selector) {
            $object[$attributeKey] = '';
        }

        return $object;
    }

    /**
     * @param int $forAttributeLeveled
     */
    private function prepareCurrentObject($forAttributeLeveled)
    {
        $new = $this->getNewEmptyObject();
        if (0 === $forAttributeLeveled) {
            // We are at the root, no need to copy anything.
            $this->currentObject = $new;

            return;
        }

        $counter = 0;
        foreach ($this->attributes as $attributeKey => $selector) {
            // We copy the values till we reached the expected level.
            $new[$attributeKey] = $this->currentObject[$attributeKey];

            if (++$counter === $forAttributeLeveled) {
                break;
            }
        }
        $this->currentObject = $new;
    }

    /**
     * @param string $dom
     *
     * @return array
     */
    public function parse($dom)
    {
        $this->init();
        $dom = new \simple_html_dom((string) $dom);

        if (null === $dom->root) {
            if (!empty($this->sharedAttributes)) {
                $this->publishCurrentObject();
            }

            return $this->parsedObjects;
        }

        // We filter the exclusion first to be able to use a global selector.
        $this->filterExcluded($dom);

        if (null !== $this->rootSelector) {
            /* @var \simple_html_dom_node $dom */
            $rootNodes = $dom->find($this->rootSelector);
            if (empty($dom)) {
                return array();
            }
        } else {
            $rootNodes = array($dom);
        }

        foreach ($rootNodes as $rootNode) {
            $this->parseNode($rootNode);
        }

        return $this->parsedObjects;
    }

    /**
     * @param string $url
     * @param int    $timeout
     *
     * @return array
     */
    public function parseURL($url, $timeout = 2)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf("'%s' is not a valid URL.", $url));
        }

        $exploded = explode('://', $url);
        $protocol = $exploded[0];

        $context = stream_context_create(
            array(
                $protocol => array(
                    'timeout' => (int) $timeout,
                ),
            )
        );

        $dom = false;
        try {
            $dom = file_get_contents($url, false, $context);
        } catch (\Exception $e) {
        }

        if (false === $dom) {
            throw new \RuntimeException(sprintf("Unable to parse URL '%s'", $url));
        }

        return $this->parse($dom);
    }

    private function parseNode($rootNode)
    {
        $globalSelector = implode(',', $this->attributes);
        $nodes = $rootNode->find($globalSelector);
        foreach ($nodes as $node) {
            /* @var \simple_html_dom_node $node */
            $attributeKey = $this->getMatchingAttributeKey($node);
            $level = $this->getAttributeLevel($attributeKey);

            $attributeValue = $this->getAttributeValue($node);
            if (empty($attributeValue)) {
                // We skip empty values to not add ghost records.
                continue;
            }

            // If we are deeper in the hierarchy, we need to create a record and go up to
            // the current element level.
            if ($level <= $this->currentLevel) {
                $this->publishCurrentObject();
                $this->prepareCurrentObject($level);
            }

            $this->setCurrentObjectAttribute($attributeKey, $attributeValue);
            $this->currentLevel = $level;
        }
        $this->publishCurrentObject();

        return $this->parsedObjects;
    }

    /**
     * @param \simple_html_dom_node $node
     *
     * @return mixed|string
     */
    private function getAttributeValue(\simple_html_dom_node $node)
    {
        // Prepare text output.
        $text = $node->innertext();
        $text = strip_tags($text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = mb_ereg_replace('\s+', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * @param string $attributeKey
     * @param string $attributeValue
     */
    private function setCurrentObjectAttribute($attributeKey, $attributeValue)
    {
        $this->currentObject[$attributeKey] = $attributeValue;
    }

    /**
     * @param string $attributeKey
     *
     * @return int
     */
    private function getAttributeLevel($attributeKey)
    {
        if (!isset($this->attributes[$attributeKey])) {
            throw new \InvalidArgumentException(sprintf('No attribute is keyed %s.', $attributeKey));
        }
        $keys = array_keys($this->attributes);

        return array_search($attributeKey, $keys);
    }

    /**
     * @param \simple_html_dom_node $node
     *
     * @return int|string
     */
    private function getMatchingAttributeKey(\simple_html_dom_node $node)
    {
        $tag = $node->tag;
        foreach ($this->attributes as $attributeKey => $selector) {
            $selector = str_replace(' ', '', $selector);
            $selectorTags = explode(',', $selector);
            if (in_array($tag, $selectorTags)) {
                return $attributeKey;
            }
        }

        throw new \RuntimeException(sprintf('Tag %s does not match any attribute.', $tag));
    }

    private function publishCurrentObject()
    {
        $this->parsedObjects[] = $this->sharedAttributes + $this->currentObject;
    }

    /**
     * @param \simple_html_dom $dom
     */
    private function filterExcluded(\simple_html_dom $dom)
    {
        $excludeSelector = implode(',', $this->exclude);
        $excludedNodes = $dom->find($excludeSelector);
        foreach ($excludedNodes as $excludedNode) {
            $this->emptyNodeContent($excludedNode);
        }
    }

    /**
     * @param \simple_html_dom_node $node
     */
    private function emptyNodeContent(\simple_html_dom_node $node)
    {
        $node->innertext = '';
        foreach ($node->children() as $child) {
            $this->emptyNodeContent($child);
        }
    }
}
