<?php

namespace Kronos\Log\Writer;

use Kronos\Log\AbstractWriter,
    Kronos\Log\Traits\PrependContext,
    Kronos\Log\Traits\LogLevelToSyslogPriority;

class Syslog extends AbstractWriter
{

    use PrependContext;
    use LogLevelToSyslogPriority;

    const DEFAULT_OPTION = LOG_ODELAY;
    const DEFAULT_FACILITY = LOG_LOCAL0;

    /**
     * @var \Kronos\Log\Adaptor\Syslog
     */
    private $syslog_adaptor;

    private $application;
    private $option;
    private $facility;

    /**
     * Syslog constructor.
     * @param \Kronos\Log\Adaptor\Syslog $syslog_adaptor
     * @param $application
     * @param $option
     * @param $facility
     */
    public function __construct(
        \Kronos\Log\Adaptor\Syslog $syslog_adaptor,
        $application,
        $option = self::DEFAULT_OPTION,
        $facility = self::DEFAULT_FACILITY
    ) {
        $this->syslog_adaptor = $syslog_adaptor;
        $this->application = $application;
        $this->option = $option;
        $this->facility = $facility;
    }

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