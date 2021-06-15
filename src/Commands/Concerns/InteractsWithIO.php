<?php

namespace NunoMaduro\Patrol\Commands\Concerns;

use DateTime;
use function NunoMaduro\Patrol\Support\collect;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait InteractsWithIO
{
    use InteractsWithTerminal;

    /**
     * Holds the console output.
     */
    private OutputInterface $output;

    /**
     * Holds the console input.
     */
    private InputInterface $input;

    /**
     * Sets the current input that should be used.
     */
    public function inputUsing(InputInterface $input): void
    {
        $this->input = $input;
    }

    /**
     * Sets the current output that should be used.
     */
    public function outputUsing(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Returns the console options.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return $this->input->getOptions();
    }

    /**
     * Write a string as information output.
     */
    public function info(string $string): void
    {
        $this->label($string, 'INFO', 'cyan', 'black');
    }

    /**
     * Write a string as high output.
     */
    public function high(string $string): void
    {
        $this->label($string, 'HIGH', 'red', 'white');
    }

    /**
     * Write a string as high output.
     */
    public function low(string $string): void
    {
        $this->label($string, 'LOW', 'yellow', 'black');
    }

    /**
     * Write a string as label output.
     */
    public function label(string $string, string $level, string $background, string $foreground): void
    {
        $this->paint([
            '',
            "  <bg=$background;fg=$foreground;options=bold> $level </> $string",
            '',
        ]);
    }

    /**
     * Write a new line on the output.
     */
    public function line(): void
    {
        $this->output->write([''], true);
    }

    /**
     * Paint the given lines on the output.
     *
     * @param array<int, string>|string $lines
     */
    public function paint(array | string $lines): void
    {
        if (is_string($lines)) {
            $lines = [$lines];
        }

        $this->output->writeln($lines);
    }

    /**
     * Write information about a vulnerability to the console.
     *
     * @param array<string, string> $vulnerability
     */
    public function vulnerabilityInfo(array $vulnerability): void
    {
        $terminalWidth = $this->getTerminalWidth();

        [
            'link'             => $link,
            'affectedVersions' => $affectedVersions,
            'reportedAt'       => $reportedAt,
            'title'            => $title,
        ] = $vulnerability;

        $link             = ltrim($link, 'https://');
        $affectedVersions = explode('<', $affectedVersions)[1];

        $dots = str_repeat('.', max(
                $terminalWidth - 10 - strlen($title) - strlen($affectedVersions), 0)
        );

        if (empty($dots) && !$this->output->isVerbose()) {
            $title = substr($title, 0, $terminalWidth - strlen($title) - 7) . '...';
        } else {
            $dots .= ' ';
        }

        $this->output->writeln(sprintf(
            '  <options=bold;fg=red>↳ %s</> <fg=#6C7280>%s</><fg=red;options=bold> ↓%s</>',
            $title,
            $dots,
            $affectedVersions,
        ));

        $dots = str_repeat(
            '.',
            max(0, $this->getTerminalWidth() - 10 - strlen($link) - strlen($reportedAt = (new DateTime($reportedAt))->format('Y-m-d'))),
        );

        $this->output->writeln(sprintf(
            '    <options=bold>»</> %s <fg=#6C7280>%s %s</>',
            $link,
            $dots,
            $reportedAt
        ));
    }

    /**
     * Write information about a outdated dependency to the console.
     *
     * @param array<string, string|array> $outdated
     */
    public function outdatedInfo(array $outdated): void
    {
        $terminalWidth = $this->getTerminalWidth();

        $latest       = $outdated['latest'] ?? $outdated['version'];
        $latestStatus = $outdated['latest-status'] ?? 'non-stable';
        [
            'name'    => $name,
            'version' => $current,
        ] = $outdated;

        $current = ltrim($current, 'v');
        $latest  = ltrim($latest, 'v');

        $type = '';

        ($why = collect($outdated['why'] ?? []))->whenNotEmpty(function () use ($why, &$type) {
            $type .= $why->implode(', ');
        });

        // @todo why-not?

        $dots = str_repeat('.', max(
                $terminalWidth - strlen($name) - 11 - strlen($type) - strlen($current) - strlen($latest), 0)
        );

        if (empty($dots) && !$this->output->isVerbose()) {
            $type = substr($type, 0, $terminalWidth - strlen($name) - 13 - strlen($current) - strlen($latest)) . '...';
        } else {
            $dots .= ' ';
        }

        $this->output->writeln(sprintf(
            ' <options=bold;fg=red></><options=bold> %s:</> <fg=#6C7280>%s %s</>%s ➜ <fg=%s;options=%s>%s</>',
            $name,
            $type,
            $dots,
            ltrim($current, 'v'),
            $latestStatus !== 'non-stable' && $current === $latest ? 'green' : ($latestStatus === 'semver-safe-update' ? 'yellow' : 'red'),
            $current === $latest ? '' : 'bold',
            ltrim($latest, 'v'),
        ));

        collect($outdated['vulnerabilities'])
            ->each(fn ($vulnerability) => $this->vulnerabilityInfo($vulnerability))
            ->whenNotEmpty(fn ()       => $this->line());
    }
}
