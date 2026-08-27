<?php
/**
 * Copy this file to config.local.php in this same folder, then replace the
 * password hash below with your own. config.local.php is gitignored — it
 * must NEVER be committed, since it holds this site's real admin password.
 *
 * To generate a new hash for a password of your choice, run this in a
 * terminal on the server (or ask whoever manages the site to run it), then
 * paste the output below:
 *
 *   php -r "echo password_hash('your-new-password', PASSWORD_DEFAULT), PHP_EOL;"
 *
 * The hash below is only a working sample so local testing has something
 * to log in with out of the box — it is the hash for the password: changeme
 * CHANGE THIS before this admin panel is reachable anywhere but your own
 * machine.
 */

define('ADMIN_PASSWORD_HASH', '$2y$10$VbLn8veRK3EL9Jcd49RaR.qNOmC9zQo1Gk3zwM3d5GdWig5Bl0.0m');
