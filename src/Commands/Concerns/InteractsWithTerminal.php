<?php

namespace NunoMaduro\Patrol\Commands\Concerns;

use Symfony\Component\Console\Terminal;

trait InteractsWithTerminal
{
    /**
     * The current terminal width.
     */
    private ?int $terminalWidth = null;

    /**
     * Computes the terminal width.
     */
    protected function getTerminalWidth(): int
    {
        if ($this->terminalWidth == null) {
            $this->terminalWidth = (new Terminal())->getWidth();

            $this->terminalWidth = $this->terminalWidth >= 30
                ? $this->terminalWidth
                : 30;
        }

        return $this->terminalWidth;
    }
}
