<?php

class XmlHelper {

    public static function printAsXML($xml) {
        



        $parser = xml_parser_create();
        $stack = array();

        xml_set_element_handler($parser, function($parser, $name, $attribs) use(&$stack) {
            $n = strtolower($name);
            if (empty($stack)) {
                $tag = 'Text';
            } else if ($n == 'span' && isset($attribs['STYLE'])) {
                $rules = explode(';', $attribs['STYLE']);
                $u = false;

                foreach ($rules as $rule) {
                    $a = explode(':', $rule);
                    if (count($a) == 2 && trim($a[0]) == 'text-decoration' && trim($a[1]) == 'underline') {
                        $u = true;
                    }
                }
                if ($u) {
                    $tag = 'u';
                } else {
                    $tag = $n;
                }
            } else {
                $tag = $n;
            }


            if ($tag == 'br') {
                echo "\n";
            } else {
                echo '<', $tag;
                if (in_array($tag, array('tab', 'hr'))) {
                    echo ' /';
                }
                echo '>';
            }


            $stack[] = $tag;
        }, function($parser, $name) use(&$stack) {
            $tag = array_pop($stack);
            if (!in_array($tag, array('br', 'tab', 'hr'))) {
                echo '</', $tag, '>';
            }
        });

        xml_set_character_data_handler(
                $parser, function($parser, $txt) {
            echo htmlspecialchars($txt);
        }
        );

        xml_parse($parser, '<Text>' . $xml . '</Text>', false);
    }

}
