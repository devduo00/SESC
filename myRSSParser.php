<?PHP
class RSSParser
{
    // keeps track of current and preceding elements
    var $tags = array();

    // array containing all feed data
    var $output = array();

    // return value for display functions
    var $retval = "";

    var $errorlevel = 0;

    // constructor for new object
    function RSSParser($file)
    {
        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_NOTICE);

        // instantiate xml-parser and assign event handlers
        $xml_parser = xml_parser_create("");
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "parseData");

        // open file for reading and send data to xml-parser
        $opts = array('http' => array('header' => "User-Agent:MyAgent/1.0\r\n"));
        $context = stream_context_create($opts);
        $data = file_get_contents($file, false, $context);

        xml_parse($xml_parser, $data) or die(
        sprintf("RSSParser: Error <b>%s</b> at line <b>%d</b><br>",
            xml_error_string(xml_get_error_code($xml_parser)),
            xml_get_current_line_number($xml_parser))
        );

        // dismiss xml parser
        xml_parser_free($xml_parser);

        error_reporting($errorlevel);
    }

    function startElement($parser, $tagname, $attrs=array())
    {
        // RSS 2.0 - ENCLOSURE
        if($tagname == "ENCLOSURE" && $attrs) {
            $this->startElement($parser, "ENCLOSURE");
            foreach($attrs as $attr => $attrval) {
                $this->startElement($parser, $attr);
                $this->parseData($parser, $attrval);
                $this->endElement($parser, $attr);
            }
            $this->endElement($parser, "ENCLOSURE");
        }

        // Yahoo! Media RSS - images
        if($tagname == "MEDIA:CONTENT" && $attrs['URL'] && $attrs['MEDIUM'] == 'image') {
            $this->startElement($parser, "IMAGE");
            $this->parseData($parser, $attrs['URL']);
            $this->endElement($parser, "IMAGE");
        }

        // check if this element can contain others - list may be edited
        if(preg_match("/^(RDF|RSS|CHANNEL|IMAGE|ITEM)/", $tagname)) {
            if($this->tags) {
                $depth = count($this->tags);
                if(is_array($tmp = end($this->tags))) {
                    list($parent, $num) = each($tmp);
                    if($parent) $this->tags[$depth-1][$parent][$tagname]++;
                }
            }
            array_push($this->tags, array($tagname => array()));
        } else {
            if(!preg_match("/^(A|B|I)$/", $tagname)) {
                // add tag to tags array
                array_push($this->tags, $tagname);
            }
        }
    }

    function endElement($parser, $tagname)
    {
        if(!preg_match("/^(A|B|I)$/", $tagname)) {
            // remove tag from tags array
            array_pop($this->tags);
        }
    }

    function parseData($parser, $data)
    {
        // return if data contains no text
        if(!trim($data)) return;

        $evalcode = "\$this->output";
        foreach($this->tags as $tag) {
            if(is_array($tag)) {
                list($tagname, $indexes) = each($tag);
                $evalcode .= "[\"$tagname\"]";
                if(${$tagname}) $evalcode .= "[" . (${$tagname} - 1) . "]";
                if($indexes) extract($indexes);
            } else {
                if(preg_match("/^([A-Z]+):([A-Z]+)$/", $tag, $matches)) {
                    $evalcode .= "[\"$matches[1]\"][\"$matches[2]\"]";
                } else {
                    $evalcode .= "[\"$tag\"]";
                }
            }
        }
        eval("$evalcode = $evalcode . '" . addslashes($data) . "';");
    }

    // display a single channel as HTML
    function display_channel($data, $limit)
    {
        extract($data);
        if($IMAGE) {
            // display channel image(s)
            foreach($IMAGE as $image) $this->display_image($image);
        }
        if($TITLE) {
            // display channel information
            $this->retval .= "<h1>";
            if($LINK) $this->retval .= "<a href=\"$LINK\" target=\"_blank\">";
            $this->retval .= stripslashes($TITLE);
            if($LINK) $this->retval .= "</a>";
            $this->retval .= "</h1>\n";
            if($DESCRIPTION) $this->retval .= "<p>$DESCRIPTION</p>\n\n";
            $tmp = array();
            if($PUBDATE) $tmp[] = "<small>Published: $PUBDATE</small>";
            if($COPYRIGHT) $tmp[] = "<small>Copyright: $COPYRIGHT</small>";
            if($tmp) $this->retval .= "<p>" . implode("<br>\n", $tmp) . "</p>\n\n";
            $this->retval .= "<div class=\"divider\"><!-- --></div>\n\n";
        }
        if($ITEM) {
            // display channel item(s)
            foreach($ITEM as $item) {
                $this->display_item($item, "CHANNEL");
                if(is_int($limit) && --$limit <= 0) break;
            }
        }
    }

    // display a single image as HTML
    function display_image($data, $parent="")
    {
        extract($data);
        if(!$URL) return;

        $this->retval .= "<p>";
        if($LINK) $this->retval .= "<a href=\"$LINK\" target=\"_blank\">";
        $this->retval .= "<img src=\"$URL\"";
        if($WIDTH && $HEIGHT) $this->retval .= " width=\"$WIDTH\" height=\"$HEIGHT\"";
        $this->retval .= " border=\"0\" alt=\"$TITLE\">";
        if($LINK) $this->retval .= "</a>";
        $this->retval .= "</p>\n\n";
    }

    // display a single item as HTML
    function display_item($data, $parent)
    {
        extract($data);
        if(!$TITLE) return;

        $this->retval .=  "<p><b>";
        if($LINK) $this->retval .=  "<a href=\"$LINK\" target=\"_blank\">";
        $this->retval .= stripslashes($TITLE);
        if($LINK) $this->retval .= "</a>";
        $this->retval .=  "</b>";
        if(!$PUBDATE && $DC["DATE"]) $PUBDATE = $DC["DATE"];
        if($PUBDATE) $this->retval .= " <small>($PUBDATE)</small>";
        $this->retval .=  "</p>\n";

        // use feed-formatted HTML if provided
        if($CONTENT['ENCODED']) {
            $this->retval .= "<p>" . stripslashes($CONTENT['ENCODED']) . "</p>\n";
        } elseif($DESCRIPTION) {
            if($IMAGE) {
                foreach($IMAGE as $IMG) $this->retval .= "<img src=\"$IMG\">\n";
            }
            $this->retval .=  "<p>" . stripslashes($DESCRIPTION) . "</p>\n\n";
        }

        // RSS 2.0 - ENCLOSURE
        if($ENCLOSURE) {
            $this->retval .= "<p><small><b>Media:</b> <a href=\"{$ENCLOSURE['URL']}\">";
            $this->retval .= $ENCLOSURE['TYPE'];
            $this->retval .= "</a> ({$ENCLOSURE['LENGTH']} bytes)</small></p>\n\n";
        }

        if($COMMENTS) {
            $this->retval .= "<p style=\"text-align: right;\"><small>";
            $this->retval .= "<a href=\"$COMMENTS\">Comments</a>";
            $this->retval .= "</small></p>\n\n";
        }
    }

    function fixEncoding(&$input, $key, $output_encoding)
    {
        if(!function_exists('mb_detect_encoding')) return $input;

        $encoding = mb_detect_encoding($input);
        switch($encoding)
        {
            case 'ASCII':
            case $output_encoding:
                break;
            case '':
                $input = mb_convert_encoding($input, $output_encoding);
                break;
            default:
                $input = mb_convert_encoding($input, $output_encoding, $encoding);
        }
    }

    // display entire feed as HTML
    function getOutput($limit=false, $output_encoding='UTF-8')
    {
        $this->retval = "";
        $start_tag = key($this->output);

        switch($start_tag)
        {
            case "RSS":
                // new format - channel contains all
                foreach($this->output[$start_tag]["CHANNEL"] as $channel) {
                    $this->display_channel($channel, $limit);
                }
                break;

            case "RDF:RDF":
                // old format - channel and items are separate
                if(isset($this->output[$start_tag]['IMAGE'])) {
                    foreach($this->output[$start_tag]['IMAGE'] as $image) {
                        $this->display_image($image);
                    }
                }
                foreach($this->output[$start_tag]['CHANNEL'] as $channel) {
                    $this->display_channel($channel, $limit);
                }
                foreach($this->output[$start_tag]['ITEM'] as $item) {
                    $this->display_item($item, $start_tag);
                }
                break;

            case "HTML":
                die("Error: cannot parse HTML document as RSS");

            default:
                die("Error: unrecognized start tag '$start_tag' in getOutput()");
        }

        if($this->retval && is_array($this->retval)) {
            array_walk_recursive($this->retval, 'RSSParser::fixEncoding', $output_encoding);
        }
        return $this->retval;
    }

    // return raw data as array
    function getRawOutput($output_encoding='UTF-8')
    {
        array_walk_recursive($this->output, 'RSSParser::fixEncoding', $output_encoding);
        return $this->output;
    }
}



class OpenGraph implements Iterator
{
    /**
     * There are base schema's based on type, this is just
     * a map so that the schema can be obtained
     *
     */
    public static $TYPES = array(
        'activity' => array('activity', 'sport'),
        'business' => array('bar', 'company', 'cafe', 'hotel', 'restaurant'),
        'group' => array('cause', 'sports_league', 'sports_team'),
        'organization' => array('band', 'government', 'non_profit', 'school', 'university'),
        'person' => array('actor', 'athlete', 'author', 'director', 'musician', 'politician', 'public_figure'),
        'place' => array('city', 'country', 'landmark', 'state_province'),
        'product' => array('album', 'book', 'drink', 'food', 'game', 'movie', 'product', 'song', 'tv_show'),
        'website' => array('blog', 'website'),
    );
    /**
     * Holds all the Open Graph values we've parsed from a page
     *
     */
    private $_values = array();
    /**
     * Fetches a URI and parses it for Open Graph data, returns
     * false on error.
     *
     * @param $URI    URI to page to parse for Open Graph data
     * @return OpenGraph
     */
    static public function fetch($URI) {
        $curl = curl_init($URI);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $response = curl_exec($curl);
        curl_close($curl);
        if (!empty($response)) {
            return self::_parse($response);
        } else {
            return false;
        }
    }
    /**
     * Parses HTML and extracts Open Graph data, this assumes
     * the document is at least well formed.
     *
     * @param $HTML    HTML to parse
     * @return OpenGraph
     */
    static private function _parse($HTML) {
        $old_libxml_error = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($HTML);

        libxml_use_internal_errors($old_libxml_error);
        $tags = $doc->getElementsByTagName('meta');
        if (!$tags || $tags->length === 0) {
            return false;
        }
        $page = new self();
        $nonOgDescription = null;

        foreach ($tags AS $tag) {
            if ($tag->hasAttribute('property') &&
                strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $page->_values[$key] = $tag->getAttribute('content');
            }

            //Added this if loop to retrieve description values from sites like the New York Times who have malformed it.
            if ($tag ->hasAttribute('value') && $tag->hasAttribute('property') &&
                strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $page->_values[$key] = $tag->getAttribute('value');
            }
            //Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
            if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $nonOgDescription = $tag->getAttribute('content');
            }

        }
        //Based on modifications at https://github.com/bashofmann/opengraph/blob/master/src/OpenGraph/OpenGraph.php
        if (!isset($page->_values['title'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $page->_values['title'] = $titles->item(0)->textContent;
            }
        }
        if (!isset($page->_values['description']) && $nonOgDescription) {
            $page->_values['description'] = $nonOgDescription;
        }
        //Fallback to use image_src if ogp::image isn't set.
        if (!isset($page->values['image'])) {
            $domxpath = new DOMXPath($doc);
            $elements = $domxpath->query("//link[@rel='image_src']");
            if ($elements->length > 0) {
                $domattr = $elements->item(0)->attributes->getNamedItem('href');
                if ($domattr) {
                    $page->_values['image'] = $domattr->value;
                    $page->_values['image_src'] = $domattr->value;
                }
            }
        }
        if (empty($page->_values)) { return false; }

        return $page;
    }
    /**
     * Helper method to access attributes directly
     * Example:
     * $graph->title
     *
     * @param $key    Key to fetch from the lookup
     */
    public function __get($key) {
        if (array_key_exists($key, $this->_values)) {
            return $this->_values[$key];
        }

        if ($key === 'schema') {
            foreach (self::$TYPES AS $schema => $types) {
                if (array_search($this->_values['type'], $types)) {
                    return $schema;
                }
            }
        }
    }
    /**
     * Return all the keys found on the page
     *
     * @return array
     */
    public function keys() {
        return array_keys($this->_values);
    }
    /**
     * Helper method to check an attribute exists
     *
     * @param $key
     */
    public function __isset($key) {
        return array_key_exists($key, $this->_values);
    }
    /**
     * Will return true if the page has location data embedded
     *
     * @return boolean Check if the page has location data
     */
    public function hasLocation() {
        if (array_key_exists('latitude', $this->_values) && array_key_exists('longitude', $this->_values)) {
            return true;
        }

        $address_keys = array('street_address', 'locality', 'region', 'postal_code', 'country_name');
        $valid_address = true;
        foreach ($address_keys AS $key) {
            $valid_address = ($valid_address && array_key_exists($key, $this->_values));
        }
        return $valid_address;
    }
    /**
     * Iterator code
     */
    private $_position = 0;
    public function rewind() { reset($this->_values); $this->_position = 0; }
    public function current() { return current($this->_values); }
    public function key() { return key($this->_values); }
    public function next() { next($this->_values); ++$this->_position; }
    public function valid() { return $this->_position < sizeof($this->_values); }
}
?>