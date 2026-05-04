<?php

require_once __DIR__ . '/includes/db.php';

function getDBConnection(): mysqli {
    return getDB();
}
