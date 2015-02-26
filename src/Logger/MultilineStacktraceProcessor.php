<?php namespace Friendica\Directory\Logger;

/**
 * Forces a stacktrace to be multiline for Monolog.
 */
class MultilineStacktraceProcessor
{
    /**
     * Handles the incomming record.
     *
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {

        //When we have an exception.
        if (isset($record['context']['exception'])) {

            //Get the trace and indent it.
            $trace = $record['context']['exception']->getTraceAsString();
            $trace = implode("\n\t", explode(PHP_EOL, $trace));

            //Append it to the message.
            $record['message'] .= "\n\t".$trace."\n\t";

            //And remove it from context.
            unset($record['context']['exception']);
        }

        return $record;
    }
}
