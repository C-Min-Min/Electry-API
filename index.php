<?php

    // Helper method to get a string description for an HTTP status code
    // From http://www.gen-x-design.com/archives/create-a-rest-api-with-php/ 
    function getStatusCodeMessage($status){
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    // Helper method to send a HTTP response code/message
    function sendResponse($status = 200, $body = '', $content_type = 'text/html'){
        $status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: ' . $content_type);
        echo $body;
    }

    class RedeemAPI{
        private $db;
        function __construct(){
            $ini_array = parse_ini_file("php.ini", true);
            $this->db = mysqli_connect("localhost", $ini_array['database']['username'], $ini_array['database']['password'], $ini_array['database']['dbname']);
            if(mysqli_connect_errno()){
                echo "Failed to connect to db: ". mysqli_connect_error();
            }
        }

        function __destruct(){
            $this->db->close();
        }

        function api_call(){
            if(isset($_POST["func"])){
                $func = $_POST["func"];
                if($func == "dev"){
                    $this->devices();
                } else if ($func == "stats"){
                    $this->stats();
                } else {
                    echo "WRONG FUNC";
                }
            } else {
                echo "NOT GIVEN FUNC";
            }
        }

        function api_json_return($sql){
            $stmt = mysqli_query($this->db, $sql);

            $resultArray = array();
            $tempArray = array();

            while($row = $stmt->fetch_object()){
                $tempArray = $row;
                array_push($resultArray, $tempArray);
            }

            echo json_encode($resultArray, JSON_PRETTY_PRINT);
        }

        function devices(){
            $sql = 'SELECT * FROM Devices'; 
            $this->api_json_return($sql);
        }

        function stats(){
            echo "Stats Work";

            if (   
                isset($_POST["dev_id"]) && 
                isset($_POST["time"]) &&
                isset($_POST["status"])
            ){
                
                // Param -> Var
                $dev_id = $_POST["dev_id"];
                $time_radius = $_POST["time"];
                $status = $_POST["status"];
                


            } else if (
                !isset($_POST["dev_id"]) && 
                isset($_POST["time"]) &&
                !isset($_POST["status"])
            ){
                $time_radius = array();
                $time_radius = $_POST["time"];

                $sql = 'SELECT `dev_name`, `image_link`, `time`, `power`
                        FROM Devices
                        INNER JOIN Stats
                        ON Devices.dev_id = Stats.dev_id
                        WHERE `time` > '.$time_radius[0].' AND `time` < '.$time_radius[1].'
                        ORDER BY `time` DESC;';
                                
                $this->api_json_return($sql);
            } else if (
                isset($_POST["dev_id"]) && 
                isset($_POST["time"]) &&
                !isset($_POST["status"])
            ){
                $dev_id = $_POST["dev_id"];
                $time_radius = $_POST["time"];

                $sql = 'SELECT `dev_name`, `image_link`, `time`, `power`
                FROM Devices
                INNER JOIN Stats
                ON Devices.dev_id = Stats.dev_id
                WHERE `time` > '.$time_radius[0].' AND `time` < '.$time_radius[1].' AND dev_id = '.$dev_id.'
                ORDER BY `time` DESC;';
                
                $this->api_json_return($sql);
            }
        }
    }

    $api = new RedeemAPI;
    $api->api_call();

?>