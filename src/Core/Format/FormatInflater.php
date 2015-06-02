<?php namespace Qosasa\Core\Format;

use Qosasa\Core\Format;


class FormatInflater {

    /**
     * Inflate the format
     *
     * @param  stdClass  $format
     * @return Format
     */
    public function inflate($format)
    {
        return $this->fillFirstLevel($format);
    }

    /**
     * Fill the first level of format
     *
     * @param  stdClass  $obj
     * @return Format
     */
    public function fillFirstLevel($obj)
    {
        return $this->fill($obj, true);
    }

    /**
     * Fill Format class
     *
     * @param  stdClass  $obj
     * @param  boolean  $firstLevel
     * @return Format
     */
    public function fill($obj, $firstLevel=false)
    {
        if (is_string($obj)) {
            return $this->fillString($obj, $firstLevel);
        } else {
            return $this->fillObject($obj, $firstLevel);
        }
    }

    /**
     * Fill Format class with string
     *
     * @param  string  $string
     * @param  boolean  $firstLevel
     * @return Format
     */
    public function fillString($string, $firstLevel=false)
    {
        list($name, $type, $isArray) = $this->parseName($string);
        
        $format = new Format;
        $format->name = $name;
        
        if ($isArray) {
            $format->type = 'array';
            $subFormat = new Format;
            $subFormat->type = $type ?: 'string';            
            $format->format = $subFormat;
        } else $format->type = 'string';

        if ($firstLevel) {
            if ($format->type === 'object')
                $format->separator = ':';
            elseif ($format->type === 'array') {
                $format->separator = ',';
            }
        }

        return $format;
    }

    /**
     * Fill Format class with object
     *
     * @param  stdClass  $obj
     * @param  boolean  $firstLevel
     * @return Format
     */
    public function fillObject($obj, $firstLevel=false)
    {
        $format = new Format;

        // Resolve type from name
        if (isset($obj->name)) {
            list($name, $type) = $this->parseName($obj->name);
            $format->name = $name;
            $format->type = $type;
        }

        // Fill type if set
        if (isset($obj->type)) {
            $format->type = $obj->type;
        }

        // Fill default if set
        if (isset($obj->default)) {
            $format->default = $obj->default;
        }
        
        // Set separator, default to ':' for objects and ',' for arrays in first level
        if (in_array($format->type, ['object', 'array'])) {
            if (isset($obj->separator)) {
                $format->separator = $obj->separator;
            } elseif ($firstLevel) {
               $format->separator = ($format->type === 'object') ? ':':',';
            }
        }

        // Set flags
        if (isset($obj->flags)) {
            $format->flags = $obj->flags;
        }

        // Build format recursively
        if (isset($obj->fields)) {
            if ($firstLevel) {
                if (is_array($obj->fields)) {
                    $format->format = array_map([$this, 'fillFirstLevel'], $obj->fields);
                } else $format->format = $this->fill($obj->fields, true);
            } else {
                if (is_array($obj->fields)) {
                    $format->format = array_map([$this, 'fill'], $obj->fields);
                } else $format->format = $this->fill($obj->fields);
            }
        }

        return $format;
    }

    /**
     * Extract attribute name and type from format
     *
     * @param  string  $name
     * @return Format
     */
    public function parseName($name)
    {
        $pattern = '/^(?P<attr>\w+)(\[(?P<type>\w+)?\])?/';
        preg_match($pattern, $name, $matches);

        $attr = $matches['attr'];
        $type = empty($matches['type']) ? null : $matches['type'];
        $isArray = @$matches[2][0] === '[';
        
        return [$attr, $type, $isArray];
    }

}
