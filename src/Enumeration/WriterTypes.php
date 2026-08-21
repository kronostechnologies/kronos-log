<?php

namespace Kronos\Log\Enumeration;

enum WriterTypes: string
{
    case CONSOLE = 'console';
    case FILE = 'file';
    case FLUENTD = 'fluentd';
    case LOGDNA = 'logdna';
    case MEMORY = 'memory';
    case SENTRY = 'sentry';
    case SYSLOG = 'syslog';
    case TRIGGER_ERROR = 'trigger_error';
}
