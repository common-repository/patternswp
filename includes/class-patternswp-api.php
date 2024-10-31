<?php
class PatternsWP_API_Section {

    public $api_url;

    public function __construct() {
        add_action('admin_init', array($this, 'register_patterns_endpoint'));

        // API URL
        $this->api_url = 'https://d1zr.thepatternswp.com/';
    }

    public function register_patterns_endpoint() {
        $api_url = $this->api_url . 'wp-json/patternswps/v1/patterns';

        // Fetch data from API using wp_remote_get
        $response = wp_remote_get($api_url);

        // Check if request was successful
        if (is_wp_error($response)) {
            // Handle error, maybe log it or return an error response
            return false;
        }

        // Get the response body
        $response_body = wp_remote_retrieve_body($response);

        // Decode JSON response
        $patterns = json_decode($response_body, true);

        // Check if decoding was successful
        if ($patterns === null) {
            // Handle JSON decoding error, maybe log it or return an error response
            return false;
        }

        // Sample usage: Output the titles of the patterns
        foreach ($patterns as $pattern) {

            $patterns_title = isset($pattern['title']) ? $pattern['title'] : '';
            $patterns_content = isset($pattern['content']) ? $pattern['content'] : '';
            $category = isset($pattern['categories'][0]) ? $pattern['categories'][0] : '';
            $category_label = ucwords(str_replace('patternswp-', 'PatternsWP - ', $category));
            $category_rbp = ucwords(str_replace('patternswp-', 'PatternsWP', $category));
            $category_label = "$category_label";
            $patternsm_title = strtolower(str_replace(' ', '-', $patterns_title));

            if (isset($pattern['categories'][0]) && !empty($pattern['categories'][0])) {
                unset($pattern['categories'][0]);
            }
            $keywords = isset($pattern['categories']) ? $pattern['categories'] : array();

            // Register block pattern category with dynamic label
            register_block_pattern_category($category, array('label' => $category_label));

            if (empty($keywords)) {
                $keywords = array($category);
            }

            $keywords = array_values($keywords);

            $newArray = array();
            foreach ($keywords as $value) {
                $newArray[] = "'" . $value . "'";
            }

            $keywords = implode(", ", $newArray);

            $patterns_content = wp_kses_post($patterns_content);

            register_block_pattern(
                $category_rbp . '-gutenberg-block-patterns/' . $patternsm_title,
                array(
                    'title' => $patterns_title,
                    'content' => $patterns_content,
                    'categories' => array($category),
                    'keywords' => array($keywords),
                )
            );
        }
    }
}

$patternswp_api_section = new PatternsWP_API_Section();
