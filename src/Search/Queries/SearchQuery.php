<?php

namespace SilverStripe\FullTextSearch\Search\Queries;

use SilverStripe\Dev\Deprecation;
use SilverStripe\View\ViewableData;
use stdClass;

/**
 * Represents a search query
 *
 * API very much still in flux.
 */
class SearchQuery extends ViewableData
{
    public static $missing = null;
    public static $present = null;

    public static $default_page_size = 10;

    /** These are public, but only for index & variant access - API users should not manually access these */

    public $search = [];

    public $classes = [];

    public $require = [];
    public $exclude = [];

    protected $start = 0;
    protected $limit = -1;

    /** These are the API functions */

    public function __construct()
    {
        if (self::$missing === null) {
            self::$missing = new stdClass();
        }
        if (self::$present === null) {
            self::$present = new stdClass();
        }
    }

    /**
     * @param string $text   Search terms. Exact format (grouping, boolean expressions, etc.) depends on
     *                       the search implementation.
     * @param array  $fields Limits the search to specific fields (using composite field names)
     * @param array  $boost  Map of composite field names to float values. The higher the value,
     *                       the more important the field gets for relevancy.
     */
    public function addSearchTerm($text, $fields = null, $boost = [])
    {
        $this->search[] = [
            'text' => $text,
            'fields' => $fields ? (array) $fields : null,
            'boost' => $boost,
            'fuzzy' => false
        ];
        return $this;
    }

    /**
     * Similar to {@link addSearchTerm()}, but uses stemming and other similarity algorithms
     * to find the searched terms. For example, a term "fishing" would also likely find results
     * containing "fish" or "fisher". Depends on search implementation.
     *
     * @param string $text   See {@link addSearchTerm()}
     * @param array  $fields See {@link addSearchTerm()}
     * @param array  $boost  See {@link addSearchTerm()}
     */
    public function addFuzzySearchTerm($text, $fields = null, $boost = [])
    {
        $this->search[] = [
            'text' => $text,
            'fields' => $fields ? (array) $fields : null,
            'boost' => $boost,
            'fuzzy' => true
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getSearchTerms()
    {
        return $this->search;
    }

    /**
     * @param string $class
     * @param bool $includeSubclasses
     * @return $this
     */
    public function addClassFilter($class, $includeSubclasses = true)
    {
        $this->classes[] = [
            'class' => $class,
            'includeSubclasses' => $includeSubclasses
        ];
        return $this;
    }

    /**
     * @return array
     */
    public function getClassFilters()
    {
        return $this->classes;
    }

    /**
     * Similar to {@link addSearchTerm()}, but typically used to further narrow down
     * based on other facets which don't influence the field relevancy.
     *
     * @param string $field  Composite name of the field
     * @param mixed  $values Scalar value, array of values, or an instance of SearchQuery_Range
     */
    public function addFilter($field, $values)
    {
        $requires = isset($this->require[$field]) ? $this->require[$field] : [];
        $values = is_array($values) ? $values : [$values];
        $this->require[$field] = array_merge($requires, $values);
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->require;
    }

    /**
     * Excludes results which match these criteria, inverse of {@link addFilter()}.
     *
     * @param string $field
     * @param mixed $values
     */
    public function addExclude($field, $values)
    {
        $excludes = isset($this->exclude[$field]) ? $this->exclude[$field] : [];
        $values = is_array($values) ? $values : [$values];
        $this->exclude[$field] = array_merge($excludes, $values);
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludes()
    {
        return $this->exclude;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    public function setPageSize($page)
    {
        $this->setStart($page * self::$default_page_size);
        $this->setLimit(self::$default_page_size);
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return (int) ($this->getLimit() / $this->getStart());
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->search || $this->classes || $this->require || $this->exclude;
    }

    public function __toString()
    {
        return "Search Query\n";
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function search($text, $fields = null, $boost = [])
    {
        Deprecation::notice('4.0', 'Use addSearchTerm() instead');
        return $this->addSearchTerm($text, $fields, $boost);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function fuzzysearch($text, $fields = null, $boost = [])
    {
        Deprecation::notice('4.0', 'Use addFuzzySearchTerm() instead');
        return $this->addFuzzySearchTerm($text, $fields, $boost);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function inClass($class, $includeSubclasses = true)
    {
        Deprecation::notice('4.0', 'Use addClassFilter() instead');
        return $this->addClassFilter($class, $includeSubclasses);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function filter($field, $values)
    {
        Deprecation::notice('4.0', 'Use addFilter() instead');
        return $this->addFilter($field, $values);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function exclude($field, $values)
    {
        Deprecation::notice('4.0', 'Use addExclude() instead');
        return $this->addExclude($field, $values);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function start($start)
    {
        Deprecation::notice('4.0', 'Use setStart() instead');
        return $this->setStart($start);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function limit($limit)
    {
        Deprecation::notice('4.0', 'Use setLimit() instead');
        return $this->setLimit($limit);
    }

    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public function page($page)
    {
        Deprecation::notice('4.0', 'Use setPageSize() instead');
        return $this->setPageSize($page);
    }
}
