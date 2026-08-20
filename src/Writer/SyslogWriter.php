<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter,
    Kronos\Log\Traits\PrependContext,
    Kronos\Log\Traits\LogLevelToSyslogPriority;
use Kronos\Log\Adaptor\SyslogAdaptor;
use Override;

class SyslogWriter extends AbstractWriter
{
    use PrependContext;
    use LogLevelToSyslogPriority;

    const int DEFAULT_OPTION = LOG_ODELAY;
    const int DEFAULT_FACILITY = LOG_LOCAL0;

    private $application;
    private $option;
    private $facility;
    private SyslogAdaptor $syslog_adaptor;

    public function __construct(
        $application,
        $option = self::DEFAULT_OPTION,
        $facility = self::DEFAULT_FACILITY,
        ?SyslogAdaptor $syslog_adaptor = null,
    ) {
        $this->application = $application;
        $this->option = $option;
        $this->facility = $facility;
        $this->syslog_adaptor = $syslog_adaptor ??  new SyslogAdaptor();
    }

    #[Override]
    public function log($level, $message, array $context = [])
    {
        $interpolated_message = $this->interpolate($message, $context);
        $prepended_message = $this->prependContext($interpolated_message, $context);

        $this->syslog_adaptor->log(
            $this->application,
            $this->option,
            $this->facility,
            $this->getSyslogPriorityForLogLevel($level),
            $prepended_message
        );
    }

}
