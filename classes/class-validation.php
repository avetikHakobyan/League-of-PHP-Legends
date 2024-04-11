<?php

class Validation {
    public function check_name(string $in): bool {
        if (preg_match("/^[a-zA-Z][a-zA-Z'\s-]*[a-zA-Z]$/", $in))
            return true;
        else
            return false;
    }

    public function check_email(string $in): bool {
        if (preg_match("/^[a-zA-Z][-\w.]*@[a-zA-Z-\w.]*\.[a-zA-Z]{2,}$/", $in))
            return true;
        else
            return false;
    }

    public function check_city(string $in): bool {
        if (preg_match("/^[a-zA-Z][a-zA-Z\s-]*[a-zA-Z]$/", $in))
            return true;
        else
            return false;
    }

    public function check_country(string $in): bool {
        if (preg_match("/^(Canada|United States|Columbia|Argentina|Peru|Brazil|Ecuador|Poland)$/", $in))
            return true;
        else
            return false;
    }

    public function check_password(string $in): string {
        $reg_num = "/\d/";
        $reg_low = "/[a-z]/";
        $reg_up = "/[A-Z]/";
        $reg_space = "/\s/";
        $reg_no_letter = "/\W/";
        $reg_length = "/^.{8,16}$/";

        if (!preg_match($reg_num, $in))
            return "Password must contain a number";
        else if (!preg_match($reg_low, $in))
            return "Password must contain lowercase";
        else if (!preg_match($reg_up, $in))
            return "Password must contain uppercase";
        else if (preg_match($reg_space, $in))
            return "Password cannot contain spaces";
        else if (!preg_match($reg_no_letter, $in))
            return "Password must contain a special character";
        else if (!preg_match($reg_length, $in))
            return "Password must be 8-16 characters long";
        else
            return "";
    }

    public function check_same(string $p1, string $p2): bool {
        if ($p1 === $p2)
            return true;
        else
            return false;
    }

    public function check_errors(array &$errors, bool &$submitted, string &$action, string &$first_name, int &$error_first_name, string &$last_name, int &$error_last_name, string &$email, int &$error_email, string &$city, int &$error_city, string &$country, int &$error_country, string &$password, int &$error_password, string &$confirm_password, int &$error_confirm_password) {
        $submitted = true;
        if (empty($first_name)) {
            $error_first_name = 1;
            array_push($errors, "Please enter the player's first name");
        } else if (!$this->check_name($first_name)) {
            $error_first_name = 2;
            array_push($errors, 'First name must only contain letters (upper or lower case), "-", " \' " or spaces, start and end with letters');
        }

        if (empty($last_name)) {
            $error_last_name = 1;
            array_push($errors, "Please enter the player's last name");
        } else if (!$this->check_name($last_name)) {
            $error_last_name = 2;
            array_push($errors, 'Last name must only contain letters (upper or lower case), "-", " \' " or spaces, start and end with letters');
        }

        if ($action !== "edit") {
            if (empty($email)) {
                $error_email = 1;
                array_push($errors, "Please enter the player's email");
            } else if (!$this->check_email($email)) {
                $error_email = 2;
                array_push($errors, 'Email must start and end with a letter and follow this format: john.smith@example.ca');
            }
        }

        if (empty($city)) {
            $error_city = 1;
            array_push($errors, "Please enter the city");
        } else if (!$this->check_city($city)) {
            $error_city = 2;
            array_push($errors, 'City must only contain letters (upper or lower case), " \' ", "-" or space, start and end with letters');
        }

        if ($country === "None") {
            $error_country = 1;
            array_push($errors, "Please select the country");
        } else if (!$this->check_country($country)) {
            $error_country = 2;
            array_push($errors, "Country must be one of the available options");
        }

        if ($action !== "edit") {
            if (empty($password)) {
                $error_password = 1;
                array_push($errors, "Please enter a password");
            } else if ($this->check_password($password) !== "") {
                $error_password = 2;
                array_push($errors, $this->check_password($password));
            }

            if (empty($confirm_password)) {
                $error_confirm_password = 1;
                array_push($errors, "Please confirm the password");
            } else if ($password !== $confirm_password) {
                $error_confirm_password = 2;
                array_push($errors, "Passwords do not match");
            }
        }
    }

    public function generate_token() {
        // Check if a token is present for the current session
        if (!isset($_SESSION["csrf_token"])) {
            // No token present, generate a new one
            $token = bin2hex(random_bytes(64));
            $_SESSION["csrf_token"] = $token;
        } else {
            // Reuse the token
            $token = $_SESSION["csrf_token"];
        }
        return $token;
    }
}

?>