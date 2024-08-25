<?php
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}
