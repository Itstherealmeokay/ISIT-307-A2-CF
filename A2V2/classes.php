<?php
class Database {
    private $conn;
    public function __construct($host, $username, $password, $dbname) {
        $this->conn = new mysqli($host, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function escape_string($string) {
        return $this->conn->real_escape_string($string);
    }

    public function close() {
        $this->conn->close();
    }
}

class ChargingLocation {
    private $db;

    // Constructor to initialize the Database class
    public function __construct($db) {
        $this->db = $db;
    }

    // Get all charging locations with session count (active and total)
    public function getLocations($filter = '') {
        $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, 
                        COUNT(cs.session_id) AS active_sessions
                FROM ChargingLocations cl
                LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
                GROUP BY cl.location_id";

        if ($filter == 'full') {
            $sql .= " HAVING COUNT(cs.session_id) = cl.num_stations";
        } elseif ($filter == 'empty') {
            $sql .= " HAVING COUNT(cs.session_id) < cl.num_stations";
        }

        return $this->db->query($sql);
    }

        public function getAvailableLocations() {
        $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, 
                       COUNT(cs.session_id) AS active_sessions
                FROM ChargingLocations cl
                LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
                GROUP BY cl.location_id
                HAVING COUNT(cs.session_id) < cl.num_stations";
        return $this->db->query($sql);
    }

    // Search locations by description
    public function searchLocations($search_term) {
        $search_term = $this->db->escape_string($search_term);
        $sql = "SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour, 
                       COUNT(cs.session_id) AS active_sessions
                FROM ChargingLocations cl
                LEFT JOIN ChargingSessions cs ON cl.location_id = cs.location_id AND cs.check_out_time IS NULL
                WHERE cl.description LIKE '%$search_term%'
                GROUP BY cl.location_id";
        return $this->db->query($sql);
    }

    public function getAllLocations() {
        $sql = "SELECT * FROM ChargingLocations";
        return $this->db->query($sql);
    }

    public function getPrice_Per_Hour($location_id) {
        $location_id = $this->db->escape_string($location_id);
        $sql = "SELECT cost_per_hour FROM ChargingLocations WHERE location_id = '$location_id'";
        return $this->db->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['cost_per_hour'];
        }
        return null;
    }

    public function updateLocation($location_id, $description, $num_stations, $cost_per_hour) {
        $description = $this->db->escape_string($description);
        $num_stations = $this->db->escape_string($num_stations);
        $cost_per_hour = $this->db->escape_string($cost_per_hour);
        $sql = "UPDATE ChargingLocations SET description = '$description', num_stations = '$num_stations', cost_per_hour = '$cost_per_hour' WHERE location_id = '$location_id'";
        return $this->db->query($sql);
    }

}

class User {
    private $db;

    // Constructor to initialize the Database class
    public function __construct($db) {
        $this->db = $db;
    }

    public function login($email) {
        $email = $this->db->escape_string($email);
        $sql = "SELECT * FROM Users WHERE email = '$email'";
        return $this->db->query($sql);
    }

    public function startSession($user) {
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
    }

    // Get all users with type 'user'
    public function getUsers($search_term = '') {
        $search_term = $this->db->escape_string($search_term);
        $sql = "SELECT user_id, name, email, user_type 
                FROM Users 
                WHERE user_type = 'user'";

        if ($search_term) {
            $sql .= " AND name LIKE '%$search_term%'";
        }

        return $this->db->query($sql);
    }

    public function getUserById($user_id) {
        $user_id = $this->db->escape_string($user_id);
        $sql = "SELECT * FROM Users WHERE user_id = '$user_id'";
        return $this->db->query($sql);
    }

    public function getAllUsers() {
        $sql = "SELECT * FROM Users";
        return $this->db->query($sql);
    }

    public function register($name, $email, $phone, $user_type) {
        $name = $this->db->escape_string($name);
        $email = $this->db->escape_string($email);
        $phone = $this->db->escape_string($phone);
        $user_type = $this->db->escape_string($user_type);
        $sql = "INSERT INTO Users (name, email, phone, user_type) VALUES ('$name', '$email', '$phone', '$user_type')";
        return $this->db->query($sql);

        if ($this->db->query($sql) === TRUE) {
            return "User registered successfully";
        } else {
            return "Error: " . $sql . "<br>" . $this->db->error;
        }
        
    }

    public function filterIfCheckedIn() {
        $sql = "SELECT u.user_id, u.name, u.email, u.user_type 
                FROM Users u
                LEFT JOIN ChargingSessions cs ON u.user_id = cs.user_id
                WHERE cs.check_out_time IS NULL AND u.user_type = 'user' AND cs.check_in_time IS NOT NULL";
        return $this->db->query($sql);
    }
}


class ChargingSession {
    private $db;

    // Constructor to initialize the Database class
    public function __construct($db) {
        $this->db = $db;
    }

    // Get active charging sessions for a specific user
    public function getActiveSessions($user_id) {
        $sql = "SELECT cs.location_id, cl.description, cs.check_in_time, cs.session_id
                FROM ChargingSessions cs
                JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                WHERE cs.check_out_time IS NULL AND cs.user_id = '$user_id'";
        return $this->db->query($sql);
    }

    // Get checkout sessions for a specific user
    public function getCheckoutSessions($user_id) {
        $sql = "SELECT cs.location_id, cl.description, cs.check_in_time, cs.check_out_time, cs.total_cost
                FROM ChargingSessions cs
                JOIN ChargingLocations cl ON cs.location_id = cl.location_id
                WHERE cs.check_out_time IS NOT NULL AND cs.user_id = '$user_id'";
        return $this->db->query($sql);
    }

    // Add a new check-in session
    public function checkIn($user_id, $location_id, $check_in_time) {
        $check_in_time = $this->db->escape_string($check_in_time);
        $sql = "INSERT INTO ChargingSessions (user_id, location_id, check_in_time) 
                VALUES ('$user_id', '$location_id', '$check_in_time')";
        return $this->db->query($sql);
    }

    // Check-out a session for the user
    public function checkOut($session_id, $check_out_time, $total_cost) {
        $check_out_time = $this->db->escape_string($check_out_time);
        $sql = "UPDATE ChargingSessions SET check_out_time = '$check_out_time', total_cost = '$total_cost' WHERE session_id = '$session_id'";
        return $this->db->query($sql);
    }

    public function calculateTotalCost($session_id) {
    // Fetch session data and cost per hour
    $sql = "SELECT cs.check_in_time, cs.check_out_time, cl.cost_per_hour
            FROM ChargingSessions cs
            JOIN ChargingLocations cl ON cs.location_id = cl.location_id
            WHERE cs.session_id = '$session_id'";

    $result = $this->db->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $check_in_time = new DateTime($row['check_in_time']);
        $check_out_time = new DateTime($row['check_out_time']);
        $cost_per_hour = $row['cost_per_hour'];

        $interval = $check_in_time->diff($check_out_time);

        $total_hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);

        // If the duration is less than 1 hour, set total_hours to 1 (to avoid small charges)
        if ($total_hours < 1) {
            $total_hours = 1; // Round to 1 hour if the duration is less than 1 hour
        }

        $total_cost = $cost_per_hour * $total_hours;

        // Return the total cost rounded to 2 decimal places
        return number_format($total_cost, 2);
    }

    return 0;  // If session data is not found
}







    public function addLocation($description, $num_stations, $cost_per_hour) {
        $description = $this->db->escape_string($description);
        $num_stations = $this->db->escape_string($num_stations);
        $cost_per_hour = $this->db->escape_string($cost_per_hour);
        $sql = "INSERT INTO ChargingLocations (description, num_stations, cost_per_hour) VALUES ('$description', '$num_stations', '$cost_per_hour')";
        return $this->db->query($sql);

    }

}
?>
