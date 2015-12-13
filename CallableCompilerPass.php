<?php

namespace Bangpound\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CallableCompilerPass.
 *
 * Define a compiler pass when it is instantiated.
 */
class CallableCompilerPass implements CompilerPassInterface
{
    /**
     * @var callable
     */
    private $pass;

    /**
     * CallableCompilerPass constructor.
     *
     * The callable must have the same signature as
     * CompilerPassInterface::process
     *
     * @param callable $pass
     */
    public function __construct(callable $pass)
    {
        $this->pass = $pass;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        call_user_func($this->pass, $container);
    }
}
