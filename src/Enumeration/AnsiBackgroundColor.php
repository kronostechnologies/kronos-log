<?php

namespace Kronos\Log\Enumeration;

enum AnsiBackgroundColor: string
{
    case BLACK = '0;40';
    case RED = '0;41';
    case GREEN = '0;42';
    case YELLOW = '0;43';
    case BLUE = '0;44';
    case MAGENTA = '0;45';
    case CYAN = '0;46';
    case LIGHT_GRAY = '0;47';
}