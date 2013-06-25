<?php
/**
 * File with configuration parameters.
 *
 * @author Norman Seidel
 * @since 0.2
 */

/**
 * Constant for input file with links and threasholds.
 */
define("INPUT_FILE", "agent_input.txt");

/**
 * Constant for delimiter between link and threashold.
 */
define("DELIMITER", "###");

/*
 * Constant for proxy server url.
 */
define("PROXY_URL","");

// Mail settings
/**
 * Constant for the mail server host.
 */
define("MAIL_HOST", "");

/**
 * Constant for the recipient of the e-mail (name <e-mail address>).
 */
define("MAIL_TO", "");

/**
 * Constant for the sender of the e-mail (name <e-mail address).
 */
define("MAIL_FROM", "");

/**
 * Constant for the subject of the e-mail.
 */
define("MAIL_SUBJECT", "Target price has been reached");

/**
 * Constant for the body of the e-mail.
 */
define("MAIL_BODY", "Hello,\r\nthe target price for {0} has been reached.\r\nCurrent price is {1}.");
?>
