<?php

declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; font-src 'self' fonts.gstatic.com cdn.jsdelivr.net; style-src 'self' fonts.googleapis.com cdn.jsdelivr.net; script-src 'self' cdn.jsdelivr.net; frame-ancestors 'none';connect-src 'self' wss; img-src 'self'; media-src 'self'; worker-src 'none'; form-action http://localhost:*");
header_remove("X-Powered-By");

date_default_timezone_set("America/New_York");

include("./player-functions.php");
include("./classes/class-country.php");
include("./classes/class-player.php");
include("./classes/class-validation.php");
include("./classes/class-league.php");

session_start();

$display_table = true;
$display_add_form = false;
$players = array();

$timestamp = date("Y-m-d h:i:s", time());
$out_csrf_missing = "CSRF token missing";
$out_csrf_mismatch = "CSRF token mismatch";
$out_xss_attempt = "XSS attempt";
$out_bad_credentials = "Bad username/password";
$output_file = "IntrusionLog.txt";

$logout = htmlspecialchars($_GET["logout"] ?? "");
$first_name = htmlspecialchars($_POST["playerFirstName"] ?? "");
$last_name = htmlspecialchars($_POST["playerLastName"] ?? "");
$email = htmlspecialchars($_POST["email"] ?? "");
$city = htmlspecialchars($_POST["city"] ?? "");
$country = htmlspecialchars($_POST["country"] ?? "");
$password = htmlspecialchars($_POST["password"] ?? "");
$confirm_password = htmlspecialchars($_POST["passwordConfirm"] ?? "");
$professional = htmlspecialchars($_POST["prof"] ?? "");

$action = htmlspecialchars($_GET["action"] ?? "");
$id = htmlspecialchars($_GET["id"] ?? "");
$confirm = htmlspecialchars($_GET["confirm"] ?? "");

$league = new League();
$validation = new Validation();

$countries = [
    new Country("Canada"),
    new Country("United States"),
    new Country("Columbia"),
    new Country("Argentina"),
    new Country("Peru"),
    new Country("Brazil"),
    new Country("Ecuador"),
    new Country("Poland")
];

$errors = array();

$error_first_name = 0;
$error_last_name = 0;
$error_email = 0;
$error_city = 0;
$error_country = 0;
$error_password = 0;
$error_confirm_password = 0;

$submitted = false;

if (isset($_SESSION['login'])) {
    if (isset($_SESSION['timeout'])) {
        if ($_SESSION['timeout'] + 10 * 60 < time()) {
            session_unset();
            session_destroy();
            header("Location: player-league.php");
        }
    }
}

if ($logout) {
    $_SESSION['timeout'] = time();
    session_unset();
    session_destroy();
    header("Location: player-league.php");
}

switch ($action) {
    case 'add':
        $_SESSION['timeout'] = time();
        $display_table = false;
        $display_add_form = true;
        break;
    case 'delete':
        $_SESSION['timeout'] = time();
        $league->get_players($players);
        $display_table = false;
        $display_add_form = false;
        if ($confirm) {
            $league->delete_player($players, intval($id));
            header("Location: player-league.php");
        }
        break;
    case 'edit':
        $_SESSION['timeout'] = time();
        if (is_numeric($id)) {
            $league->get_players($players);
            $display_table = false;
            $display_add_form = true;
        } else {
            $action = "";
            header("Location: player-league.php");
        }
        break;
    default:
        # code...
        break;
}

if (isset($_POST["login"])) {
    $_SESSION['timeout'] = time();
    $submitted = true;
    if (isset($_POST["csrf_token"]) && $_POST["csrf_token"] == $_SESSION["csrf_token"]) {
        if (!empty($email) && !empty($password)) {
            $league->get_players($players);
            if (!$league->check_player($players, $email)) {
                $name = $league->check_password($players, $email, $password);
                if (sizeof($name) !== 0) {
                    $_SESSION["login"] = true;
                    $_SESSION["name"] = $name[0];
                    $_SESSION["time"] = $name[1];
                } else {
                    $_SESSION["login"] = false;
                    file_put_contents($output_file, "$timestamp: $out_bad_credentials \n", FILE_APPEND);
                }
            }
            $players = array();
        }
    } else {
        unset($_SESSION["csrf_token"]);
    }
}

if (isset($_POST["add_player"])) {
    if (isset($_POST["csrf_token"])) {
        if (($_POST["csrf_token"] === $_SESSION["csrf_token"])) {
            $action = "add";
            $submitted = true;
            $validation->check_errors($errors, $submitted, $action, $first_name, $error_first_name, $last_name, $error_last_name, $email, $error_email, $city, $error_city, $country, $error_country, $password, $error_password, $confirm_password, $error_confirm_password);
            $display_table = false;
            if (sizeof($errors) === 0) {
                $league->get_players($players);
                $new_player = new Player($first_name, $last_name, $email, $city, new Country($country), password_hash($password, PASSWORD_DEFAULT), $league->check_prof($professional));
                if ($league->add_player($players, $new_player)) {
                    $league->sort_players($players);
                    $league->save_file($players);
                    $display_add_form = false;
                } else {
                    $error_email = 3;
                    array_push($errors, 'Email ' . "\"$email\"" . ' already exists. Please enter a different email');
                }
            }
        } else {
            unset($_SESSION["csrf_token"]);
            file_put_contents($output_file, "$timestamp: $out_csrf_missing \n", FILE_APPEND);
        }
    } else {
        unset($_SESSION["csrf_token"]);
        file_put_contents($output_file, "$timestamp: $out_csrf_mismatch \n", FILE_APPEND);
    }
} else if (isset($_POST["edit_player"])) {
    if (isset($_POST["csrf_token"])) {
        if ($_POST["csrf_token"] === $_SESSION["csrf_token"]) {
            $league->get_players($players);
            $id = $_POST["edit_player"];
            $action = "edit";
            $submitted = true;
            $validation->check_errors($errors, $submitted, $action, $first_name, $error_first_name, $last_name, $error_last_name, $email, $error_email, $city, $error_city, $country, $error_country, $password, $error_password, $confirm_password, $error_confirm_password);
            $display_table = false;
            if (sizeof($errors) === 0) {
                $new_player = ["first_name" => $first_name, "last_name" => $last_name, "email" => $email, "city" => $city, "country" => $country, "professional" => $league->check_prof($professional)];
                if (is_numeric($id)) {
                    if ($league->edit_player($players, $new_player, intval($id))) {
                        $league->sort_players($players);
                        $league->save_file($players);
                        $display_add_form = false;
                        header("Location: player-league.php");
                    } else {
                        $error_email = 3;
                        array_push($errors, 'Email ' . "\"$email\"" . ' already exists. Please enter a different email');
                    }
                } else {
                    header("Location: player-league.php");
                }
            }
        } else {
            unset($_SESSION["csrf_token"]);
            file_put_contents($output_file, "$timestamp: $out_csrf_mismatch \n", FILE_APPEND);
        }   
    } else {
        unset($_SESSION["csrf_token"]);
        file_put_contents($output_file, "$timestamp: $out_csrf_missing \n", FILE_APPEND);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player league</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="./styles/site.css">
</head>

<body class="d-flex flex-column min-vh-100 m-0">
    <header class="bg-white text-black py-4 mb-4 min-vh-50">
        <?php if (isset($_SESSION["name"]) && isset($_SESSION["time"])):?>
            <div class="text-end me-4">
                <div>
                    <a href="./player-league.php"><?= $_SESSION["name"] ?></a>
                    <img src="./images/icon-userdark.png" width="40" height="40" alt="A black icon of a user">
                    <a class="btn btn-danger" href="./player-league.php?logout=1">Log out</a>
                </div>
                <div class="mt-3">
                    <span class="fw-bold">Last login: </span><span><?= $_SESSION["time"] ?></span>
                </div>
            </div>
        <?php endif;?>
        <h1 class="fw-bold text-center">
            <?php if ($action === "add"): ?>
                <?= "Add new player" ?>
            <?php elseif ($action === "delete"): ?>
                <?= "Delete player" ?>
            <?php elseif ($action === "edit"): ?>
                <?= "Edit player" ?>
            <?php else: ?>
                <?= "Welcome to League of Players" ?>
            <?php endif; ?>
        </h1>
    </header>
    <?php if (isset($_SESSION["login"]) && $_SESSION["login"] === true): ?>
        <?php if ($display_table): ?>
            <section class="container-xxl w-75 m-auto mt-0">
                <?= $league->get_players($players) ?>
                <?php if (sizeof($players) === 0): ?>
                    <h3 class="text-center mb-4">No players found - add players with the button below</h3>
                <?php else: ?>
                    <table class="table align-middle table-bordered table-dark table-hover mb-4">
                        <thead>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>City</th>
                            <th>Country</th>
                            <th>Pro</th>
                            <th></th>
                            <th></th>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($players as $index => $player): ?>
                                <tr class="table-dark">
                                    <td class="text-center">
                                        <img title="No photo" width="120" height="120" src="./images/blank-profile-picture.png"
                                            alt="No photo">
                                    </td>
                                    <td>
                                        <?= $player->get_first_name() ?>
                                        <?= $player->get_last_name() ?>
                                    </td>
                                    <td>
                                        <?= $player->get_email() ?>
                                    </td>
                                    <td>
                                        <?= $player->get_city() ?>
                                    </td>
                                    <td>
                                        <?= $player->get_country()->get_name() ?>
                                    </td>
                                    <td>
                                        <?= ucfirst($player->get_professional()) ?>
                                    </td>
                                    <td class="text-center px-0">
                                        <a title="Edit player" class="btn btn-sm btn-warning px-3"
                                            href="player-league.php?action=edit&id=<?= $index ?>">
                                            <!-- Icon from https://icons.getbootstrap.com/icons/pencil-square/ -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                                                class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                                <path
                                                    d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708l-3-3zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207l6.5-6.5zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.499.499 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11l.178-.178z" />
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="text-center px-0"><a title="Delete player" class="btn btn-sm btn-danger px-3"
                                            href="player-league.php?action=delete&id=<?= $index ?>">X</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <div class="text-center">
                    <a class="btn btn-success" href="player-league.php?action=add" title="Add a new player">Add Player</a>
                </div>
            </section>
        <?php else: ?>
            <section class="row container-xxl w-60 m-auto mt-5 border p-5 bg-dark">
                <?php if ($submitted): ?>
                    <?php if (sizeof($errors)): ?>
                        <?php $display_add_form = true ?>
                        <h5>The form contains
                            <?= sizeof($errors) ?>
                            <?= (sizeof($errors) > 1) ? "errors" : "error" ?>. See below for more details
                        </h5>
                        <ol class="ms-4">
                            <?php foreach ($errors as $key): ?>
                                <li class="text-danger">
                                    <?= $key ?>
                                </li>
                            <?php endforeach ?>
                        </ol>
                    <?php endif ?>
                <?php else: ?>
                    <?php if ($action === "add"): ?>
                        <h5>Please complete the form below to add a new player</h5>
                    <?php elseif ($action === "edit"): ?>
                        <h5>Changes will only be saved after clicking "Done"</h5>
                    <?php endif ?>
                <?php endif ?>
                <?php if ($display_add_form): ?>
                    <form method="post" action="./player-league.php" class="mt-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="playerFirstName" class="form-label">First Name</label>
                                <input id="playerFirstName"
                                    class='<?= ($error_first_name > 0 && $submitted) ? "form-control border-danger" : "form-control" ?>'
                                    name="playerFirstName" placeholder="John" size="30" type="text"
                                    value="<?= ($action === "edit" && $id !== "") ? $players[$id]->get_first_name() : $first_name ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="playerLastName" class="form-label">Last Name</label>
                                <input id="playerLastName"
                                    class='<?= ($error_last_name > 0 && $submitted) ? "form-control border-danger" : "form-control" ?>'
                                    name="playerLastName" placeholder="Smith" size="30" type="text"
                                    value="<?= ($action === "edit" && $id !== "") ? $players[$id]->get_last_name() : $last_name ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label mt-1">Email Address</label>
                            <input id="email"
                                class='<?= ($error_email > 0 && $submitted) ? "form-control border-danger" : "form-control" ?>'
                                name="email" placeholder="example@example.ca" size="35" type="text"
                                value="<?= ($action === "edit" && $id !== "") ? $players[$id]->get_email() : $email ?>">
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label mt-1">City</label>
                            <input name="city" id="city"
                                class='<?= ($error_city > 0 && $submitted) ? "form-control border-danger w-auto" : "form-control w-auto" ?>'
                                placeholder="London" type="text"
                                value="<?= ($action === "edit" && $id !== "") ? $players[$id]->get_city() : $city ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="country">Country</label>
                            <select id="country" name="country"
                                class="<?= ($error_country > 0 && $submitted) ? "border-danger w-auto form-select form-select-sm" : "w-auto form-select form-select-sm" ?>">
                                <option value="None">-- Select --</option>
                                <?= $league->sort_countries($countries) ?>
                                <?php foreach ($countries as $key): ?>
                                    <option value="<?= $key->get_name() ?>" <?= ($country === $key->get_name()) ? "selected='selected'" : (($action === "edit" && $players[$id]->get_country()->get_name() === $key->get_name()) ? "selected='selected'" : "") ?>><?= $key->get_name() ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-check mb-4">
                            <label for="prof" class="form-check-label me-2">Professional</label>
                            <input class="form-check-input" type="checkbox" name="prof" id="prof" <?= ($action === "edit" && $players[$id]->get_professional() === "yes") ? "checked" : (($professional) ? "checked" : "") ?>>
                        </div>
                        <?php if ($action !== "edit"): ?>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" name="password"
                                    class='<?= ($error_password > 0 && $submitted) ? "form-control border-danger w-auto" : "form-control w-auto" ?>'
                                    placeholder="••••••••">
                            </div>
                            <div class="mb-4">
                                <label for="passwordConfirm" class="form-label">Confirm Password</label>
                                <input id="passwordConfirm" type="password" name="passwordConfirm"
                                    class='<?= ($error_confirm_password > 0 && $submitted) ? "form-control border-danger w-auto" : "form-control w-auto" ?>'
                                    placeholder="••••••••">
                            </div>
                        <?php endif ?>
                        <div class="text-start">
                            <?php if ($action !== "edit"): ?>
                                <input type="hidden" name="csrf_token" value="<?= $validation->generate_token() ?>" />
                                                <input type="submit" name="add_player" class="btn btn-success" value="Add player">
                            <?php else: ?>
                                <a class="text-info w-auto pe-2" title="Cancel and go back to home page"
                                    href="./player-league.php">Cancel</a>
                                    <input type="hidden" name="csrf_token" value="<?=$validation->generate_token()?>" />
                                <button value="<?= $id ?>" type="submit" name="edit_player" class="btn btn-warning">Done</button>
                            <?php endif ?>
                        </div>
                    </form>
                <?php else: ?>
                    <?php if ($action === "add"): ?>
                        <h3 class="fw-bold mb-3">Player Added</h3>
                        <h5>
                            <?= $first_name ?>
                            <?= $last_name ?> has been added to your roster. Good luck with your picks in the pool
                        </h5>
                        <a class="text-info" title="Go back to home page" href="./player-league.php">Continue</a>
                    <?php endif ?>
                <?php endif ?>
                <?php if ($action === "delete"): ?>
                    <h3 class="fw-bold mb-3">You are about to permanently delete
                        <?= $players[$id]->get_first_name() ?>
                        <?= $players[$id]->get_last_name() ?>
                    </h3>
                    <div class="text-end">
                        <a class="text-info w-auto pe-2" title="Cancel and go back to home page" href="./player-league.php">Cancel</a>
                        <a class="btn btn-danger w-auto" title="Confirm and delete"
                            href="player-league.php?action=delete&id=<?= $id ?>&confirm=1">Confirm</a>
                    </div>
                <?php endif ?>
            </section>
        <?php endif ?>
    <?php else :?>
        <section class="container-sm m-auto mt-5">
            <form class="w-50 m-auto p-5 border bg-dark" action="player-league.php" method="post">
                <h2 class="fw-bold text-center mb-4">Login</h2>
                <?php if (empty($email) || empty($password)): ?>
                    <p class="mb-4">Please fill in your credentials to login</p>
                <?php elseif ($submitted && (!isset($_SESSION["login"]) || $_SESSION["login"] === false)):?>
                    <p class="mb-4 text-danger">Invalid email or password. Please try again.</p>
                <?php endif ?>
                <div class="mb-4">
                    <label class="form-label fw-bold fs-5" for="email">Email</label>
                    <input type="text" name="email" id="email" class="form-control" value="<?= $email?>" placeholder="example@example.ex">
                    <?php if ($submitted && empty($email)): ?>
                            <span class="text-danger">Please enter your email</span>
                    <?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold fs-5" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••">
                    <?php if ($submitted && empty($password)): ?>
                            <span class="text-danger">Please enter your password</span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?= $validation->generate_token() ?>">
                    <input class="btn btn-primary px-5" id="login" name="login" type="submit" value="Log in">
                </div>
                </form>
            </section>
    <?php endif ?>
    <footer class="bg-white min-vh-50 text-black pt-4 pb-1 text-center mt-5 fw-bold">
        <h5>Made by <a href="mailto:hakobyan.avetik@cegep-heritage.qc.ca">Avetik Hakobyan</a></h5>
        <span>Copyright @
            <?= date("Y") ?>
        </span>
    </footer>
</body>

</html>