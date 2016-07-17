<?php

class CitiesController {
    protected static $ins;
    public $conn;
    public $host_db = "mysql:host=localhost;dbname=task";
    public $db_login = "root";
    public $city_a;
    public $city_b;
    public $check_distance = array();
    public $checked_path_already = array();
    public $city_a_id;
    public $city_b_id;

    private function __construct() {
        $this->conn=new PDO($this->host_db, $this->db_login );
    }
    public static function getInstance()
    {
        if (self::$ins === null) {
            self::$ins = new self;
        }
        return self::$ins;
    }
    public function searchCity() {
        $this->city_a = trim(htmlspecialchars($_POST['city_a'], ENT_QUOTES));
        $this->city_b = trim(htmlspecialchars($_POST['city_b'], ENT_QUOTES));
        $priority = trim(htmlspecialchars($_POST['search_object'], ENT_QUOTES));

        $city_name_ab = $this->conn->query(sprintf('Select id, city From cities Where city ="%s" or city ="%s"', $this->city_a, $this->city_b));
        if($city_name_ab) {
            $city_name_ab = $city_name_ab->fetchAll(PDO::FETCH_ASSOC);
            if(count($city_name_ab) > 1) {
                $this->city_a_id = $city_name_ab[0]['city'] == $this->city_a ? $city_name_ab[0]['id'] : $city_name_ab[1]['id'];
                $this->city_b_id = $city_name_ab[1]['city'] == $this->city_b ? $city_name_ab[1]['id'] : $city_name_ab[0]['id'];
            }
            else {
                return json_encode(array('error' => 'Please enter correct names of the cities'));
            }
        }
        else {
            return json_encode(array('error' => 'Please enter correct names of the cities'));
        }

        $view = $this->priority($this->check_optimal($this->city_a, $this->city_a_id), $priority, SORT_ASC);
        return json_encode($view);
    }

    protected function check_optimal($city_name, $city_search = '', $city_host = '', $total_distance = 0, $total_time = 0,  $depth = 0) {
        $check_name = '';
        $check_cities = $this->conn->query(sprintf('Select routes.*, cities.city From cities
               Inner Join routes on cities.id = routes.city_a
                Where cities.id = "%d" and routes.city_a != "%d" and routes.city_b != "%d"', $city_search, $city_host, $city_host));
        if($check_cities) $check_cities = $check_cities->fetchAll(PDO::FETCH_ASSOC);
        else $check_cities = array();

        foreach ($check_cities as $key) {
            $this->checked_path_already[] = $key['city_a'];
            if($key['city_b'] === $this->city_b_id) {
                $check_name = $depth>0 ? ($city_name == '' ? '':$city_name.', ').$key['city'].', '. $this->city_b :  $this->city_b;
                $this->check_distance[] = array('distance' =>$total_distance+$key['distance'], 'time' => $total_time + $key['time'], 'city_a' =>  $this->city_a, 'city_b' => $check_name);
            } else {
                if($depth == 5  || in_array($key['city_b'], $this->checked_path_already)) continue;
                $city_search = $key['city_b'];   $city_name = ($depth>0 ? ($city_name == '' ? '':$city_name.', ').$key['city'] : '');
                $this->check_distance = $this->check_optimal($city_name, $city_search, $key['city_a'], $total_distance+$key['distance'], $total_time+$key['time'], $depth+1);
            }
        }
        return $this->check_distance;
    }

    protected function priority() {
        $args = func_get_args();
        $data = array_shift($args);
        if(empty($data))
            return array('error' => 'Not found route in one of the cities, please recheck again...');
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}

$db = CitiesController::getInstance();
echo $view = $db->searchCity();