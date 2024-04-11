<?php

// function sort_countries(array &$countries) {
//     usort($countries, function ($a, $b) {
//         if (($a !== "Canada" && $a !== "United States") && (($b !== "Canada" && $b !== "United States")))
//             return $a <=> $b;
//     });
// }
// function check_name(string $in): bool {
//     if (preg_match("/^[a-zA-Z][a-zA-Z'\s-]*[a-zA-Z]$/", $in))
//         return true;
//     else
//         return false;
// }

// function check_email(string $in): bool {
//     if (preg_match("/^[a-zA-Z][-\w.]*@[a-zA-Z-\w.]*\.[a-zA-Z]{2,}$/", $in))
//         return true;
//     else
//         return false;
// }

// function check_city(string $in): bool {
//     if (preg_match("/^[a-zA-Z][a-zA-Z\s-]*[a-zA-Z]$/", $in))
//         return true;
//     else
//         return false;
// }

// function check_country(string $in): bool {
//     if (preg_match("/^(Canada|United States|Columbia|Argentina|Peru|Brazil|Ecuador|Poland)$/", $in))
//         return true;
//     else
//         return false;
// }

// function check_password(string $in): string {
//     $reg_num = "/\d/";
//     $reg_low = "/[a-z]/";
//     $reg_up = "/[A-Z]/";
//     $reg_space = "/\s/";
//     $reg_no_letter = "/\W/";
//     $reg_length = "/^.{8,16}$/";

//     if (!preg_match($reg_num, $in))
//         return "Password must contain a number";
//     else if (!preg_match($reg_low, $in))
//         return "Password must contain lowercase";
//     else if (!preg_match($reg_up, $in))
//         return "Password must contain uppercase";
//     else if (preg_match($reg_space, $in))
//         return "Password cannot contain spaces";
//     else if (!preg_match($reg_no_letter, $in))
//         return "Password must contain a special character";
//     else if (!preg_match($reg_length, $in))
//         return "Password must be 8-16 characters long";
//     else
//         return "";
// }

// function check_same(string $p1, string $p2): bool {
//     if ($p1 === $p2)
//         return true;
//     else
//         return false;
// }

// function check_prof(string $prof): string {
//     if ($prof === "on")
//         return "yes";
//     else
//         return "no";
// }

// function check_errors(array &$errors, bool &$submitted, string &$action, string &$first_name, int &$error_first_name, string &$last_name, int &$error_last_name, string &$email, int &$error_email, string &$city, int &$error_city, string &$country, int &$error_country, string &$password, int &$error_password, string &$confirm_password, int &$error_confirm_password) {
//     $submitted = true;
//     if (empty($first_name)) {
//         $error_first_name = 1;
//         array_push($errors, "Please enter the player's first name");
//     } else if (!check_name($first_name)) {
//         $error_first_name = 2;
//         array_push($errors, 'First name must only contain letters (upper or lower case), "-", " \' " or spaces, start and end with letters');
//     }

//     if (empty($last_name)) {
//         $error_last_name = 1;
//         array_push($errors, "Please enter the player's last name");
//     } else if (!check_name($last_name)) {
//         $error_last_name = 2;
//         array_push($errors, 'Last name must only contain letters (upper or lower case), "-", " \' " or spaces, start and end with letters');
//     }

//     if ($action !== "edit") {
//         if (empty($email)) {
//             $error_email = 1;
//             array_push($errors, "Please enter the player's email");
//         } else if (!check_email($email)) {
//             $error_email = 2;
//             array_push($errors, 'Email must start and end with a letter and follow this format: john.smith@example.ca');
//         }
//     }

//     if (empty($city)) {
//         $error_city = 1;
//         array_push($errors, "Please enter the city");
//     } else if (!check_city($city)) {
//         $error_city = 2;
//         array_push($errors, 'City must only contain letters (upper or lower case), " \' ", "-" or space, start and end with letters');
//     }

//     if ($country === "None") {
//         $error_country = 1;
//         array_push($errors, "Please select the country");
//     } else if (!check_country($country)) {
//         $error_country = 2;
//         array_push($errors, "Country must be one of the available options");
//     }

//     if ($action !== "edit") {
//         if (empty($password)) {
//             $error_password = 1;
//             array_push($errors, "Please enter a password");
//         } else if (check_password($password) !== "") {
//             $error_password = 2;
//             array_push($errors, check_password($password));
//         }

//         if (empty($confirm_password)) {
//             $error_confirm_password = 1;
//             array_push($errors, "Please confirm the password");
//         } else if ($password !== $confirm_password) {
//             $error_confirm_password = 2;
//             array_push($errors, "Passwords do not match");
//         }
//     }
// }

// function get_players(array &$players) {
//     $file_name = "./players.txt";

//     if (file_exists($file_name)) {
//         $records = file($file_name);
//         for ($i = 0; $i < sizeof($records); $i++) {
//             $records[$i] = trim($records[$i]);
//         }
//         foreach ($records as $string) {
//             $record = explode("~", $string);
//             array_push($players, ["first" => $record[0], "last" => $record[1], "email" => $record[2], "city" => $record[3], "country" => $record[4], "prof" => $record[5]]);
//         }
//     }
// }

// function check_player(array &$players, array $new_player): bool {
//     foreach ($players as $player) {
//         if ($player["email"] === $new_player["email"]) {
//             return false;
//         }
//     }
//     return true;
// }

// function new_player(string $first, string $last, string $email, string $city, string $country, string $prof): array {
//     $check = check_prof($prof);
//     $player_array = ["first" => $first, "last" => $last, "email" => $email, "city" => $city, "country" => $country, "prof" => $check];
//     return $player_array;
// }

// function add_player(array &$players, array $new_player): bool {
//     if (check_player($players, $new_player)) {
//         array_push($players, $new_player);
//         return true;
//     } else
//         return false;
// }

// function delete_player(array &$players, int $id) {
//     if (is_numeric($id)) {
//         unset($players[$id]);
//         $players = array_values($players);
//         save_file($players);
//     }
// }

// function edit_player(array &$players, array $new_player, int $id): bool {
//     if (is_numeric($id)) {
//         if ($players[$id]["email"] !== $new_player["email"]) {
//             if (check_player($players, $new_player)) {
//                 $players[$id] = $new_player;
//                 return true;
//             } else {
//                 return false;
//             }
//         } else
//             return true;
//     } else {
//         return false;
//     }
// }

// function sort_players(array &$players) {
//     usort($players, function ($a, $b) {
//         return (strtoupper($a["last"]) <=> strtoupper($b["last"])) ? strtoupper($a["first"]) <=> strtoupper($b["first"]) : strtoupper($a["last"]) <=> strtoupper($b["last"]);
//     });
//     sort($players, SORT_FLAG_CASE);
// }

// function save_file(array &$players) {
//     $file_name = "./players.txt";
//     $file = fopen($file_name, 'w');

//     if ($file === false) {
//         // error opening file
//         echo "Unable to open \"$file_name\", exiting";
//         exit;
//     }

//     foreach ($players as $player) {
//         fwrite($file, "$player[first]~$player[last]~$player[email]~$player[city]~$player[country]~$player[prof]\n");
//     }

//     fclose($file);
// }

// function remove_ext(string $name): string {
//     return preg_replace("/\.[a-zA-Z]+$/", "", $name);
// }