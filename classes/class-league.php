<?php 

class League {
    public function sort_countries(array &$countries) {
        usort($countries, function ($a, $b) {
            if (($a->get_name() !== "Canada" && $a->get_name() !== "United States") && (($b->get_name() !== "Canada" && $b->get_name() !== "United States")))
                return $a->get_name() <=> $b->get_name();
        });
    }

    public function check_prof(string $prof): string {
		if ($prof === "on")
			return "yes";
		else
			return "no";
	}

    public function get_players(array &$players) {
        $file_name = "./players.txt";

        if (file_exists($file_name)) {
            $records = file($file_name);
            for ($i = 0; $i < sizeof($records); $i++) {
                $records[$i] = trim($records[$i]);
            }
            foreach ($records as $string) {
                $record = explode("~", $string);
                array_push($players, new Player($record[0], $record[1], $record[2], $record[3], new Country($record[4]), $record[5], $record[6]));
            }
        }
    }

    public function check_player(array &$players, string $new_email): bool {
        foreach ($players as $player) {
            if ($player->get_email() === $new_email) {
                return false;
            }
        }
        return true;
    }

    public function add_player(array &$players, Player $new_player): bool {
        if ($this->check_player($players, $new_player->get_email())) {
            array_push($players, $new_player);
            return true;
        } else
            return false;
    }

    public function delete_player(array &$players, int $id) {
        if (is_numeric($id)) {
            unset($players[$id]);
            $players = array_values($players);
            $this->save_file($players);
        }
    }

    public function edit_player(array &$players, array $new_player, int $id): bool {
        if (is_numeric($id)) {
            if ($players[$id]->get_email() !== $new_player["email"]) {
                if ($this->check_player($players, $new_player["email"])) {
                    $players[$id] = new Player($new_player["first_name"], $new_player["last_name"], $new_player["email"], $new_player["city"], new Country($new_player["country"]), $players[$id]->get_password(), $new_player["professional"]);
                    return true;
                } else {
                    return false;
                }
            } else
                return true;
        } else {
            return false;
        }
    }

    public function sort_players(array &$players) {
        usort($players, function ($a, $b) {
            return (strtoupper($a->get_last_name()) <=> strtoupper($b->get_last_name())) ? strtoupper($a->get_first_name()) <=> strtoupper($b->get_first_name()) : strtoupper($a->get_last_name()) <=> strtoupper($b->get_last_name());
        });
        sort($players, SORT_FLAG_CASE);
    }

    public function save_file(array &$players) {
        $file_name = "./players.txt";
        $file = fopen($file_name, 'w');

        if ($file === false) {
            // error opening file
            echo "Unable to open \"$file_name\", exiting";
            exit;
        }

        foreach ($players as $player) {
            $first_name = $player->get_first_name();
            $last_name = $player->get_last_name();
            $email = $player->get_email();
            $city = $player->get_city();
            $country = $player->get_country()->get_name();
            $password = $player->get_password();
            $professional = $player->get_professional();
            fwrite($file, "$first_name~$last_name~$email~$city~$country~$password~$professional\n");
        }

        fclose($file);
    }

    public function check_password(array $players, string $email, string $password) : array {
        foreach ($players as $player) {
            if ($player->get_email() === $email) {
                if (password_verify($password, $player->get_password())) {
                    return [$player->get_first_name() . " " . $player->get_last_name(), date("F j, Y, h:i a")];
                } else {
                    return [];
                }
            }
        }
        return [];
    }
}

?>