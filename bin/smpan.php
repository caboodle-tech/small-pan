<?php

if (!function_exists('str_ends_with')) {
    /**
     * Checks if a string ends with a given substring.
     * 
     * NOTE: This is a native function in PHP 8+ now. Please visit the PHP manual
     * for the {@link https://www.php.net/manual/en/function.str-ends-with.php#125967 original source}.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for in the haystack.
     * 
     * @return boolean TRUE if haystack ends with needle, FALSE otherwise.
     */
    function str_ends_with(string $haystack, string $needle) {
        $length = strlen($needle);
        return $length > 0 ? substr($haystack, -$length) === $needle : true;
    }
}

class Smpan {

    private $cmd;
    const VERSION = '0.1.0';

    public function __construct(array $argv = []) {
        $this->setCommand($argv);
    }

    public function setCommand(array $argv = []) {
        if (count($argv) > 0) {
            $cmd = $argv[0];
            if (str_ends_with($cmd, 'smpan')) {
                array_shift($argv);
            }
        }
        $this->cmd = $argv;
    }

    public function run() {
        $cmd = '';
        if (count($this->cmd) > 0) {
            $cmd = strtoupper($this->cmd[0]);
        }
        switch ($cmd) {
            case 'PHP:TEST':
                passthru('./testing/php/bin/phpunit --bootstrap ./testing/php/bin/bootstrap.php --testdox ./testing/php --cache-result-file ./testing/php/bin --colors always grep --color=always');
                break;
            case 'UPDATE':
                echo 'Feature coming soon. In the meantime please manually update.' . PHP_EOL;
                break;
            default:
                echo 'Command not recognized.' . PHP_EOL;
        }
    }

    private update() {
        // Limit what we request to reduce the size of the request.
        $params = [
            'per_page' => 1
        ];
        
        // Make the request via the GitHub API.
        $url  = 'https://api.github.com/repos/caboodle-tech/small-pan/releases';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = json_decode(curl_exec($curl));
        curl_close($curl);
    }

    private function newerVersion($check) {
        if (version_compare(self::VERSION, $check) === -1) {
            return true;
        }
        return false;
    }
}