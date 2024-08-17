<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CurieRO\Symfony\Component\VarDumper\Cloner;

use CurieRO\Symfony\Component\VarDumper\Caster\Caster;
use CurieRO\Symfony\Component\VarDumper\Exception\ThrowingCasterException;
/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements ClonerInterface
{
    public static $defaultCasters = ['__PHP_Incomplete_Class' => ['CurieRO\Symfony\Component\VarDumper\Caster\Caster', 'castPhpIncompleteClass'], 'CurieRO\Symfony\Component\VarDumper\Caster\CutStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'], 'CurieRO\Symfony\Component\VarDumper\Caster\CutArrayStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'castCutArray'], 'CurieRO\Symfony\Component\VarDumper\Caster\ConstStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'castStub'], 'CurieRO\Symfony\Component\VarDumper\Caster\EnumStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'castEnum'], 'Fiber' => ['CurieRO\Symfony\Component\VarDumper\Caster\FiberCaster', 'castFiber'], 'Closure' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClosure'], 'Generator' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castGenerator'], 'ReflectionType' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castType'], 'ReflectionAttribute' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castAttribute'], 'ReflectionGenerator' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReflectionGenerator'], 'ReflectionClass' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClass'], 'ReflectionClassConstant' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castClassConstant'], 'ReflectionFunctionAbstract' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castFunctionAbstract'], 'ReflectionMethod' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castMethod'], 'ReflectionParameter' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castParameter'], 'ReflectionProperty' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castProperty'], 'ReflectionReference' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castReference'], 'ReflectionExtension' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castExtension'], 'ReflectionZendExtension' => ['CurieRO\Symfony\Component\VarDumper\Caster\ReflectionCaster', 'castZendExtension'], 'CurieRO\Doctrine\Common\Persistence\ObjectManager' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Doctrine\Common\Proxy\Proxy' => ['CurieRO\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castCommonProxy'], 'CurieRO\Doctrine\ORM\Proxy\Proxy' => ['CurieRO\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castOrmProxy'], 'CurieRO\Doctrine\ORM\PersistentCollection' => ['CurieRO\Symfony\Component\VarDumper\Caster\DoctrineCaster', 'castPersistentCollection'], 'CurieRO\Doctrine\Persistence\ObjectManager' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'DOMException' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castException'], 'DOMStringList' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'], 'DOMNameList' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'], 'DOMImplementation' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castImplementation'], 'DOMImplementationList' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'], 'DOMNode' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNode'], 'DOMNameSpaceNode' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNameSpaceNode'], 'DOMDocument' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocument'], 'DOMNodeList' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'], 'DOMNamedNodeMap' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLength'], 'DOMCharacterData' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castCharacterData'], 'DOMAttr' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castAttr'], 'DOMElement' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castElement'], 'DOMText' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castText'], 'DOMTypeinfo' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castTypeinfo'], 'DOMDomError' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDomError'], 'DOMLocator' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castLocator'], 'DOMDocumentType' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castDocumentType'], 'DOMNotation' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castNotation'], 'DOMEntity' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castEntity'], 'DOMProcessingInstruction' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castProcessingInstruction'], 'DOMXPath' => ['CurieRO\Symfony\Component\VarDumper\Caster\DOMCaster', 'castXPath'], 'XMLReader' => ['CurieRO\Symfony\Component\VarDumper\Caster\XmlReaderCaster', 'castXmlReader'], 'ErrorException' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castErrorException'], 'Exception' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castException'], 'Error' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castError'], 'CurieRO\Symfony\Bridge\Monolog\Logger' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Symfony\Component\DependencyInjection\ContainerInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Symfony\Component\EventDispatcher\EventDispatcherInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Symfony\Component\HttpClient\AmpHttpClient' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'], 'CurieRO\Symfony\Component\HttpClient\CurlHttpClient' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'], 'CurieRO\Symfony\Component\HttpClient\NativeHttpClient' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClient'], 'CurieRO\Symfony\Component\HttpClient\Response\AmpResponse' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'], 'CurieRO\Symfony\Component\HttpClient\Response\CurlResponse' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'], 'CurieRO\Symfony\Component\HttpClient\Response\NativeResponse' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castHttpClientResponse'], 'CurieRO\Symfony\Component\HttpFoundation\Request' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castRequest'], 'CurieRO\Symfony\Component\Uid\Ulid' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUlid'], 'CurieRO\Symfony\Component\Uid\Uuid' => ['CurieRO\Symfony\Component\VarDumper\Caster\SymfonyCaster', 'castUuid'], 'CurieRO\Symfony\Component\VarDumper\Exception\ThrowingCasterException' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castThrowingCasterException'], 'CurieRO\Symfony\Component\VarDumper\Caster\TraceStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castTraceStub'], 'CurieRO\Symfony\Component\VarDumper\Caster\FrameStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castFrameStub'], 'CurieRO\Symfony\Component\VarDumper\Cloner\AbstractCloner' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Symfony\Component\ErrorHandler\Exception\SilencedErrorContext' => ['CurieRO\Symfony\Component\VarDumper\Caster\ExceptionCaster', 'castSilencedErrorContext'], 'CurieRO\Imagine\Image\ImageInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\ImagineCaster', 'castImage'], 'CurieRO\Ramsey\Uuid\UuidInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\UuidCaster', 'castRamseyUuid'], 'CurieRO\ProxyManager\Proxy\ProxyInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\ProxyManagerCaster', 'castProxy'], 'PHPUnit_Framework_MockObject_MockObject' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\PHPUnit\Framework\MockObject\MockObject' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\PHPUnit\Framework\MockObject\Stub' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Prophecy\Prophecy\ProphecySubjectInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'CurieRO\Mockery\MockInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\StubCaster', 'cutInternals'], 'PDO' => ['CurieRO\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdo'], 'PDOStatement' => ['CurieRO\Symfony\Component\VarDumper\Caster\PdoCaster', 'castPdoStatement'], 'AMQPConnection' => ['CurieRO\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castConnection'], 'AMQPChannel' => ['CurieRO\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castChannel'], 'AMQPQueue' => ['CurieRO\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castQueue'], 'AMQPExchange' => ['CurieRO\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castExchange'], 'AMQPEnvelope' => ['CurieRO\Symfony\Component\VarDumper\Caster\AmqpCaster', 'castEnvelope'], 'ArrayObject' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayObject'], 'ArrayIterator' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castArrayIterator'], 'SplDoublyLinkedList' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castDoublyLinkedList'], 'SplFileInfo' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileInfo'], 'SplFileObject' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castFileObject'], 'SplHeap' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'], 'SplObjectStorage' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castObjectStorage'], 'SplPriorityQueue' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castHeap'], 'OuterIterator' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castOuterIterator'], 'WeakReference' => ['CurieRO\Symfony\Component\VarDumper\Caster\SplCaster', 'castWeakReference'], 'Redis' => ['CurieRO\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedis'], 'RedisArray' => ['CurieRO\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisArray'], 'RedisCluster' => ['CurieRO\Symfony\Component\VarDumper\Caster\RedisCaster', 'castRedisCluster'], 'DateTimeInterface' => ['CurieRO\Symfony\Component\VarDumper\Caster\DateCaster', 'castDateTime'], 'DateInterval' => ['CurieRO\Symfony\Component\VarDumper\Caster\DateCaster', 'castInterval'], 'DateTimeZone' => ['CurieRO\Symfony\Component\VarDumper\Caster\DateCaster', 'castTimeZone'], 'DatePeriod' => ['CurieRO\Symfony\Component\VarDumper\Caster\DateCaster', 'castPeriod'], 'GMP' => ['CurieRO\Symfony\Component\VarDumper\Caster\GmpCaster', 'castGmp'], 'MessageFormatter' => ['CurieRO\Symfony\Component\VarDumper\Caster\IntlCaster', 'castMessageFormatter'], 'NumberFormatter' => ['CurieRO\Symfony\Component\VarDumper\Caster\IntlCaster', 'castNumberFormatter'], 'IntlTimeZone' => ['CurieRO\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlTimeZone'], 'IntlCalendar' => ['CurieRO\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlCalendar'], 'IntlDateFormatter' => ['CurieRO\Symfony\Component\VarDumper\Caster\IntlCaster', 'castIntlDateFormatter'], 'Memcached' => ['CurieRO\Symfony\Component\VarDumper\Caster\MemcachedCaster', 'castMemcached'], 'CurieRO\Ds\Collection' => ['CurieRO\Symfony\Component\VarDumper\Caster\DsCaster', 'castCollection'], 'CurieRO\Ds\Map' => ['CurieRO\Symfony\Component\VarDumper\Caster\DsCaster', 'castMap'], 'CurieRO\Ds\Pair' => ['CurieRO\Symfony\Component\VarDumper\Caster\DsCaster', 'castPair'], 'CurieRO\Symfony\Component\VarDumper\Caster\DsPairStub' => ['CurieRO\Symfony\Component\VarDumper\Caster\DsCaster', 'castPairStub'], 'mysqli_driver' => ['CurieRO\Symfony\Component\VarDumper\Caster\MysqliCaster', 'castMysqliDriver'], 'CurlHandle' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'], ':curl' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castCurl'], ':dba' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'], ':dba persistent' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castDba'], 'GdImage' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'], ':gd' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castGd'], ':mysql link' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castMysqlLink'], ':pgsql large object' => ['CurieRO\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLargeObject'], ':pgsql link' => ['CurieRO\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'], ':pgsql link persistent' => ['CurieRO\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castLink'], ':pgsql result' => ['CurieRO\Symfony\Component\VarDumper\Caster\PgSqlCaster', 'castResult'], ':process' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castProcess'], ':stream' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'], 'OpenSSLCertificate' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'], ':OpenSSL X.509' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castOpensslX509'], ':persistent stream' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStream'], ':stream-context' => ['CurieRO\Symfony\Component\VarDumper\Caster\ResourceCaster', 'castStreamContext'], 'XmlParser' => ['CurieRO\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'], ':xml' => ['CurieRO\Symfony\Component\VarDumper\Caster\XmlResourceCaster', 'castXml'], 'RdKafka' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castRdKafka'], 'CurieRO\RdKafka\Conf' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castConf'], 'CurieRO\RdKafka\KafkaConsumer' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castKafkaConsumer'], 'CurieRO\RdKafka\Metadata\Broker' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castBrokerMetadata'], 'CurieRO\RdKafka\Metadata\Collection' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castCollectionMetadata'], 'CurieRO\RdKafka\Metadata\Partition' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castPartitionMetadata'], 'CurieRO\RdKafka\Metadata\Topic' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicMetadata'], 'CurieRO\RdKafka\Message' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castMessage'], 'CurieRO\RdKafka\Topic' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopic'], 'CurieRO\RdKafka\TopicPartition' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicPartition'], 'CurieRO\RdKafka\TopicConf' => ['CurieRO\Symfony\Component\VarDumper\Caster\RdKafkaCaster', 'castTopicConf']];
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;
    /**
     * @var array<string, list<callable>>
     */
    private $casters = [];
    /**
     * @var callable|null
     */
    private $prevErrorHandler;
    private $classInfo = [];
    private $filter = 0;
    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(?array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }
    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters(array $casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }
    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     */
    public function setMaxItems(int $maxItems)
    {
        $this->maxItems = $maxItems;
    }
    /**
     * Sets the maximum cloned length for strings.
     */
    public function setMaxString(int $maxString)
    {
        $this->maxString = $maxString;
    }
    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     */
    public function setMinDepth(int $minDepth)
    {
        $this->minDepth = $minDepth;
    }
    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data
     */
    public function cloneVar($var, int $filter = 0)
    {
        $this->prevErrorHandler = set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }
            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }
            return \false;
        });
        $this->filter = $filter;
        if ($gc = gc_enabled()) {
            gc_disable();
        }
        try {
            return new Data($this->doClone($var));
        } finally {
            if ($gc) {
                gc_enable();
            }
            restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }
    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array
     */
    abstract protected function doClone($var);
    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castObject(Stub $stub, bool $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;
        if ((\PHP_VERSION_ID < 80000) ? "\x00" === ($class[15] ?? null) : str_contains($class, "@anonymous\x00")) {
            $stub->class = get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = method_exists($class, '__debugInfo');
            foreach (class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';
            $r = new \ReflectionClass($class);
            $fileInfo = ($r->isInternal() || $r->isSubclassOf(Stub::class)) ? [] : ['file' => $r->getFileName(), 'line' => $r->getStartLine()];
            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }
        $stub->attr += $fileInfo;
        $a = Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);
        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [((Stub::TYPE_OBJECT === $stub->type) ? Caster::PREFIX_VIRTUAL : '') . '⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array
     */
    protected function castResource(Stub $stub, bool $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;
        try {
            if (!empty($this->casters[':' . $type])) {
                foreach ($this->casters[':' . $type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [((Stub::TYPE_OBJECT === $stub->type) ? Caster::PREFIX_VIRTUAL : '') . '⚠' => new ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
}
