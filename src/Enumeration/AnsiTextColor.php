<?php

namespace Kronos\Log\Enumeration;

enum AnsiTextColor: string
{
    case BLACK = '0;30';
    case DARK_GRAY = '1;30';
    case RED = '0;31';
    case LIGHT_RED = '1;31';
    case GREEN = '0;32';
    case LIGHT_GREEN = '1;32';
    case BROWN = '0;33';
    case YELLOW = '1;33';
    case BLUE = '0;34';
    case LIGHT_BLUE = '1;34';
    case PURPLE = '0;35';
    case LIGHT_PURPLE = '1;35';
    case CYAN = '0;36m';
    case LIGHT_CYAN = '1;36';
    case LIGHT_GRAY = '0;37';
    case WHITE = '1;37';
}