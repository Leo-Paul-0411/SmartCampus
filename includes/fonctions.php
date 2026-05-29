<?php

function securiser($valeur)
{
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}
