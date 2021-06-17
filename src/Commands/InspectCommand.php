<?php

declare(strict_types=1);

namespace NunoMaduro\Patrol\Commands;

use NunoMaduro\Patrol\Handlers\DependenciesList;
use NunoMaduro\Patrol\Handlers\Score;
use function NunoMaduro\Patrol\Support\collect;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class InspectCommand extends Command
{
    use Concerns\InteractsWithIO;

    /** @var string */
    public const FORMAT_LIST = 'LIST';

    /** @var string  */
    public const FORMAT_TABLE = 'TABLE';

    /**
     * The command name.
     */
    protected static $defaultName = 'inspect';

    /**
     * @var array<string, string>>
     */
    private array $dependencies;

    /**
     * The command exit code.
     */
    private int $exitCode = Command::SUCCESS;

    /**
     * @var array<class-string>
     */
    private array $handlers = [
        Score::class,
        DependenciesList::class,
    ];

    private array $formats = [
        self::FORMAT_LIST,
        self::FORMAT_TABLE
    ];

    /**
     * Configures the console command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('directory', InputArgument::OPTIONAL, 'The project directory', (string) getcwd());
        $this->addOption('min', null, InputOption::VALUE_OPTIONAL, 'The minimum score', '0.0');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'The output format', self::FORMAT_LIST);
    }

    /**
     * Runs the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->inputUsing($input);
        $this->outputUsing($output);
        if (self::FORMAT_TABLE === $input->getOption('format')) {
            $this->createTable();
        }

        /** @var string $directory */
        $directory = $input->getArgument('directory');

        collect($this->handlers)
            ->map(fn ($class)    => $class::resolve($directory))
            ->each(fn ($handler) => $handler($this));

        if (self::FORMAT_TABLE === $input->getOption('format')) {
            $this->table->render();
        }

        return $this->exitCode;
    }

    /**
     * Sets the exit code.
     */
    public function exitWith(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
}
