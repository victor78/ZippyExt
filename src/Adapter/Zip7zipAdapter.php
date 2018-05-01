<?php

namespace Victor78\ZippyExt\Adapter;

use Alchemy\Zippy\Adapter\{
    Resource\ResourceInterface,
    AbstractBinaryAdapter
};
use Victor78\ZippyExt\Archive\Archive;
use Alchemy\Zippy\Archive\Member;
use Alchemy\Zippy\Exception\{
InvalidArgumentException, NotSupportedException, RuntimeException};
use Alchemy\Zippy\Parser\{ParserInterface, ZipOutputParser};
use Alchemy\Zippy\ProcessBuilder\{ProcessBuilderFactoryInterface,ProcessBuilderFactory};
use Alchemy\Zippy\Resource\Resource as ZippyResource;
use Alchemy\Zippy\Resource\ResourceManager;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

class Zip7zipAdapter extends AbstractBinaryAdapter
{
    private static $zipDateFormat = 'Y-m-d H:i';
    protected $password;
    public function __construct(
        ParserInterface $parser,
        ResourceManager $manager,
        ProcessBuilderFactoryInterface $inflator,
        ProcessBuilderFactoryInterface $deflator
    ) {
        parent::__construct($parser, $manager, $inflator, $deflator);

        $this->probe = new VersionProbe\Zip7zipVersionProbe($inflator, $deflator);
    }

    /**
     * @inheritdoc
     */
    protected function doCreate($path, $files, $recursive)
    {
        $files = (array) $files;

        $builder = $this
            ->inflator
            ->create()
            ->add('a')
            ->add('-tzip');  
        if (0 === count($files)) {
            throw new NotSupportedException('Can not create empty zip archive');
        }

        if ($recursive) {
            $builder->add('-r');
        }

        if ($this->password){
                      
            $builder->add('-p'.$this->password);            
            $builder->add('-mem=AES256');
        }

        $builder->add($path);

        $collection = $this->manager->handle(getcwd(), $files);
        $builder->setWorkingDirectory($collection->getContext());

        $collection->forAll(function($i, ZippyResource $resource) use ($builder) {
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
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return new Archive($this->createResource($path), $this, $this->manager);
    }

    public function setPassword($password){
        $this->password = $password;
    }
    
    /**
     * @inheritdoc
     */
    protected function doListMembers(ResourceInterface $resource)
    {
        $process = $this
            ->deflator
            ->create()
            ->add('l')
            ->add($resource->getResource())
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        $members = array();
        
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
    public function parseFileListing($output)
    {
        $lines = array_values(array_filter(explode("\n", $output)));
        array_shift($lines);
        array_shift($lines);
        array_shift($lines);
        $members = array();

        foreach ($lines as $line) {
            $matches = array();

            // 2018-04-03 21:56:00 .....            5           33  added.txt
            if (!preg_match_all('/([0-9]{4}-[0-9]{2}-[0-9]{2}) ([0-9]{2}:[0-9]{2}:[0-9]{2}) ([^ ]*) *([0-9]*) *([0-9]*) *(.*)/',
                $line, $matches, PREG_SET_ORDER
            )) {
                continue;
            }

            $chunks = array_shift($matches);

            if (7 !== count($chunks)) {
                continue;
            }
            $date = $chunks[1].' '.$chunks[2];
            $mtime = \DateTime::createFromFormat('Y-m-d H:i:s', $date);

            if ($mtime === false) {
                $mtime = \DateTime::createFromFormat('H:i Y-m-d', $date);

                if ($mtime === false) {
                    $mtime = new \DateTime($date);
                }                
            }

            $members[] = array(
                'location'  => $chunks[6],
                'size'      => $chunks[4],
                'mtime'     => $mtime,
                'is_dir'    => '/' === substr($chunks[6], -1)
            );
        }

        return $members;
    }
    /**
     * @inheritdoc
     */
    protected function doAdd(ResourceInterface $resource, $files, $recursive)
    {
        $files = (array) $files;

        $builder = $this
            ->inflator
            ->create();

        if ($recursive) {
            $builder->add('-r');
        }
        
        
        if ($this->password){
                      
            $builder->add('-p'.$this->password);            
            $builder->add('-mem=AES256');
        }
        $builder
            ->add('u')
            ->add($resource->getResource());

        $collection = $this->manager->handle(getcwd(), $files);

        $builder->setWorkingDirectory($collection->getContext());

        $collection->forAll(function($i, ZippyResource $resource) use ($builder) {
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
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    protected function doGetDeflatorVersion()
    {
        $process = $this
            ->deflator
            ->create()
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $this->parseVersion($process->getOutput() ?: '');
    }
    
    public function parseVersion($output)
    {
        $lines = array_values(array_filter(explode("\n", $output, 3)));

        $chunks = explode(' ', $lines[1], 4);
        if (2 > count($chunks)) {
            return null;
        }

        $version = $chunks[2];

        return $version;
    }

    /**
     * @inheritdoc
     */
    protected function doGetInflatorVersion()
    {
        $process = $this
            ->inflator
            ->create()
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $this->parseVersion($process->getOutput() ?: '');
    }

    /**
     * @inheritdoc
     */
    protected function doRemove(ResourceInterface $resource, $files)
    {
        $files = (array) $files;

        $builder = $this
            ->inflator
            ->create();

        $builder
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
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    public static function getName()
    {
        return '7zip';
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultDeflatorBinaryName()
    {
        return array('7za');
    }

    /**
     * @inheritdoc
     */
    public static function getDefaultInflatorBinaryName()
    {
        return array('7za');
    }

    /**
     * @inheritdoc
     */
    protected function doExtract(ResourceInterface $resource, $to)
    {
        if (null !== $to && !is_dir($to)) {
            throw new InvalidArgumentException(sprintf("%s is not a directory", $to));
        }

        $builder = $this
            ->deflator
            ->create();

        $builder
            ->add('x')
            ->add($resource->getResource());

        if (null !== $to) {
            $builder
                ->add('-o'.$to); //required do it sticky
        }
        
        
        if ($this->password){
                      
            $builder->add('-p'.$this->password);            
            $builder->add('-mem=AES256');
        }
        
        $builder->add('-y');
        
        $process = $builder->getProcess();
        
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to execute the following command %s {output: %s}',
                $process->getCommandLine(),
                $process->getErrorOutput()
            ));
        }

        return new \SplFileInfo($to ?: $resource->getResource());
    }

    /**
     * @inheritdoc
     */
    protected function doExtractMembers(ResourceInterface $resource, $members, $to, $overwrite = false)
    {
        throw new DomainException('Impossible to extract members with 7za.');
    }
    
    /**
     * Returns a new instance of the invoked adapter
     *
     * @param ExecutableFinder $finder
     * @param ResourceManager  $manager
     * @param string|null      $inflatorBinaryName The inflator binary name to use
     * @param string|null      $deflatorBinaryName The deflator binary name to use
     *
     * @return AbstractBinaryAdapter
     */
    public static function newInstance(
        ExecutableFinder $finder,
        ResourceManager $manager,
        $inflatorBinaryName = null,
        $deflatorBinaryName = null
    ) {
        

        $inflator = $inflatorBinaryName instanceof ProcessBuilderFactoryInterface ? $inflatorBinaryName : self::findABinary($inflatorBinaryName,
            static::getDefaultInflatorBinaryName(), $finder);
        $deflator = $deflatorBinaryName instanceof ProcessBuilderFactoryInterface ? $deflatorBinaryName : self::findABinary($deflatorBinaryName,
            static::getDefaultDeflatorBinaryName(), $finder);
        try {
            $outputParser = new ZipOutputParser(self::$zipDateFormat);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(sprintf(
                'Failed to get a new instance of %s',
                get_called_class()), $e->getCode(), $e
            );
        }

        if (null === $inflator) {
            throw new \RuntimeException(sprintf('Unable to create the inflator'));
        }

        if (null === $deflator) {
            throw new \RuntimeException(sprintf('Unable to create the deflator'));
        }

        return new static($outputParser, $manager, $inflator, $deflator);
    }    
    
    
    private static function findABinary($wish, array $defaults, ExecutableFinder $finder)
    {
        
        $possibles = $wish ? (array) $wish : $defaults;
        $binary = null;

        foreach ($possibles as $possible) {
            if (null !== $found = $finder->find($possible)) {
                $binary = new ProcessBuilderFactory($found);
                break;
            }
        }

        return $binary;
    }    
}
