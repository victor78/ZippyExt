<?php

declare(strict_types=1);

namespace Victor78\ZippyExt\Adapter;

use Alchemy\Zippy\Adapter\{
    Resource\ResourceInterface,
    AbstractBinaryAdapter
};
use Victor78\ZippyExt\Archive\Archive;
use Alchemy\Zippy\Archive\Member;
use Alchemy\Zippy\Exception\{InvalidArgumentException, NotSupportedException, RuntimeException};
use Alchemy\Zippy\Parser\{ParserInterface, ZipOutputParser};
use Alchemy\Zippy\ProcessBuilder\{ProcessBuilderFactoryInterface, ProcessBuilderFactory};
use Alchemy\Zippy\Resource\Resource as ZippyResource;
use Alchemy\Zippy\Resource\ResourceManager;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

class Zip7zipAdapter extends AbstractBinaryAdapter
{
    private static string $zipDateFormat = 'Y-m-d H:i';
    protected ?string $password = null;

    public function __construct(
        ParserInterface $parser,
        ResourceManager $manager,
        ProcessBuilderFactoryInterface $inflator,
        ProcessBuilderFactoryInterface $deflator
    ) {
        parent::__construct($parser, $manager, $inflator, $deflator);

        $this->probe = new VersionProbe\Zip7zipVersionProbe($inflator, $deflator);
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    protected function doCreate($path, $files, $recursive): Archive
    {
        $files = (array) $files;

        if (0 === count($files)) {
            throw new NotSupportedException('Can not create empty zip archive');
        }

        $builder = $this->inflator->create()
            ->add('a')
            ->add('-tzip');

        if ($recursive) {
            $builder->add('-r');
        }

        if ($this->password !== null) {
            $builder->add('-p' . $this->password);
            $builder->add('-mem=AES256');
        }

        $builder->add($path);

        $collection = $this->manager->handle(getcwd(), $files);
        $builder->setWorkingDirectory($collection->getContext());

        $collection->forAll(function ($i, ZippyResource $resource) use ($builder) {
            return $builder->add($resource->getTarget());
        });

        $process = $builder->getProcess();

        try {
            $process->run();
        } catch (ProcessException $e) {
            $this->manager->cleanup($collection);
            throw $e;
        }

        $this->manager->cleanup($collection);

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        return new Archive($this->createResource($path), $this, $this->manager);
    }

    protected function doListMembers(ResourceInterface $resource): array
    {
        $process = $this->deflator->create()
            ->add('l')
            ->add('-slt')
            ->add($resource->getResource())
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        $members = [];

        foreach ($this->parseFileListing($process->getOutput() ?: '') as $member) {
            $members[] = new Member(
                $resource,
                $this,
                $member['location'],
                $member['size'],
                $member['mtime'],
                $member['is_dir']
            );
        }

        return $members;
    }

    public function parseFileListing(string $output): array
    {
        $members = $this->parseSltFileListing($output);
        if (count($members) > 0) {
            return $members;
        }

        // Fallback for legacy/non-slt output formats.
        return $this->parseLegacyFileListing($output);
    }

    private function parseSltFileListing(string $output): array
    {
        $lines = preg_split('/\R/', $output) ?: [];
        $records = [];
        $current = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                if (isset($current['Path'])) {
                    $records[] = $current;
                }
                $current = [];
                continue;
            }

            if (!str_contains($line, ' = ')) {
                continue;
            }

            [$key, $value] = explode(' = ', $line, 2);
            $current[$key] = $value;
        }

        if (isset($current['Path'])) {
            $records[] = $current;
        }

        $members = [];
        foreach ($records as $record) {
            // Item records always include both Path and Folder in -slt output.
            if (!isset($record['Path'], $record['Folder'])) {
                continue;
            }

            $path = (string) $record['Path'];
            if ($path === '') {
                continue;
            }

            $isDir = (($record['Folder'] ?? '-') === '+');
            $size = (int) ($record['Size'] ?? 0);
            $mtime = $this->parseMtime($record['Modified'] ?? null);

            $members[] = [
                'location' => $path,
                'size'     => $size,
                'mtime'    => $mtime,
                'is_dir'   => $isDir,
            ];
        }

        return $members;
    }

    private function parseLegacyFileListing(string $output): array
    {
         $lines = array_values(array_filter(explode("\n", $output)));
         array_shift($lines);
         array_shift($lines);
         array_shift($lines);
         $members = [];

        foreach ($lines as $line) {
            $matches = [];

            // 2018-04-03 21:56:00 .....            5           33  added.txt
            if (!preg_match_all(
                '/([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}) ([^ ]*) *([0-9]*) *([0-9]*) *(.*)/',
                $line, $matches, PREG_SET_ORDER
            )) {
                continue;
            }

            $chunks = array_shift($matches);

            if (7 !== count($chunks)) {
                continue;
            }

            $date = $chunks[1] . ' ' . $chunks[2];
            $mtime = $this->parseMtime($date);

            $members[] = [
                'location' => $chunks[6],
                'size'     => (int) $chunks[4],
                'mtime'    => $mtime,
                'is_dir'   => '/' === substr($chunks[6], -1),
            ];
        }

        return $members;
    }

    protected function doAdd(ResourceInterface $resource, $files, $recursive): void
    {
        $files = (array) $files;

        $builder = $this->inflator->create();

        if ($recursive) {
            $builder->add('-r');
        }

        if ($this->password !== null) {
            $builder->add('-p' . $this->password);
            $builder->add('-mem=AES256');
        }

        $builder->add('u')->add($resource->getResource());

        $collection = $this->manager->handle(getcwd(), $files);
        $builder->setWorkingDirectory($collection->getContext());

        $collection->forAll(function ($i, ZippyResource $resource) use ($builder) {
            return $builder->add($resource->getTarget());
        });

        $process = $builder->getProcess();

        try {
            $process->run();
        } catch (ProcessException $e) {
            $this->manager->cleanup($collection);
            throw $e;
        }

        $this->manager->cleanup($collection);

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }
    }

    protected function doGetDeflatorVersion(): ?string
    {
        $process = $this->deflator->create()->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        return $this->parseVersion($process->getOutput() ?: '');
    }

    public function parseVersion(string $output): ?string
    {
        if (preg_match('/\b(\d+(?:\.\d+)+)\b/', $output, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function doGetInflatorVersion(): ?string
    {
        $process = $this->inflator->create()->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        return $this->parseVersion($process->getOutput() ?: '');
    }

    protected function doRemove(ResourceInterface $resource, $files): array
    {
        $files = (array) $files;

        $builder = $this->inflator->create()
            ->add('d')
            ->add($resource->getResource());

        if (!$this->addBuilderFileArgument($files, $builder)) {
            throw new InvalidArgumentException('Invalid files');
        }

        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        return $files;
    }

    public static function getName(): string
    {
        return '7zip';
    }

    public static function getDefaultDeflatorBinaryName(): array
    {
        return ['7za', '7z'];
    }

    public static function getDefaultInflatorBinaryName(): array
    {
        return ['7za', '7z'];
    }

    protected function doExtract(ResourceInterface $resource, $to): \SplFileInfo
    {
        if (null !== $to && !is_dir($to)) {
            throw new InvalidArgumentException(sprintf('%s is not a directory', $to));
        }

        $builder = $this->deflator->create()
            ->add('x')
            ->add($resource->getResource());

        if (null !== $to) {
            $builder->add('-o' . $to);
        }

        if ($this->password !== null) {
            $builder->add('-p' . $this->password);
            $builder->add('-mem=AES256');
        }

        $builder->add('-y');

        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $this->sanitizeCommandLine($process->getCommandLine()),
                $process->getErrorOutput()
            ));
        }

        return new \SplFileInfo($to ?: $resource->getResource());
    }

    protected function doExtractMembers(ResourceInterface $resource, $members, $to, $overwrite = false): never
    {
        throw new \DomainException('Impossible to extract members with 7za. Use extract() instead.');
    }

    public static function newInstance(
        ExecutableFinder $finder,
        ResourceManager $manager,
        $inflatorBinaryName = null,
        $deflatorBinaryName = null
    ): static {
        $inflator = $inflatorBinaryName instanceof ProcessBuilderFactoryInterface
            ? $inflatorBinaryName
            : self::findABinary($inflatorBinaryName, static::getDefaultInflatorBinaryName(), $finder);

        $deflator = $deflatorBinaryName instanceof ProcessBuilderFactoryInterface
            ? $deflatorBinaryName
            : self::findABinary($deflatorBinaryName, static::getDefaultDeflatorBinaryName(), $finder);

        try {
            $outputParser = new ZipOutputParser(self::$zipDateFormat);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(
                sprintf('Failed to get a new instance of %s', get_called_class()),
                $e->getCode(),
                $e
            );
        }

        if (null === $inflator) {
            throw new \RuntimeException('Unable to create the inflator');
        }

        if (null === $deflator) {
            throw new \RuntimeException('Unable to create the deflator');
        }

        return new static($outputParser, $manager, $inflator, $deflator);
    }

    private static function findABinary($wish, array $defaults, ExecutableFinder $finder): ?ProcessBuilderFactory
    {
        $possibles = $wish ? (array) $wish : $defaults;

        foreach ($possibles as $possible) {
            if (null !== $found = $finder->find($possible)) {
                return new ProcessBuilderFactory($found);
            }
        }

        return null;
    }

    private function sanitizeCommandLine(string $commandLine): string
    {
        return (string) preg_replace('/-p(?:\s+)?(?:"[^"]*"|\'[^\']*\'|\S+)/', '-p*****', $commandLine);
    }

    private function parseMtime(?string $value): \DateTime
     {
         $date = trim((string) $value);
         if ($date === '') {
            return new \DateTime('@0');
         }
 
         foreach (['Y-m-d H:i:s', 'H:i Y-m-d'] as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
             if ($parsed !== false) {
                 return $parsed;
             }
         }
 
         try {
            return new \DateTime($date);
         } catch (\Exception) {
            return new \DateTime('@0');
         }
     }
}
