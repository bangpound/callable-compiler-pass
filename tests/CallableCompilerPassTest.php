<?php

namespace Bangpound\Symfony\DependencyInjection\Tests;

use Bangpound\Symfony\DependencyInjection\CallableCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CallableCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('my_cool.service', __NAMESPACE__ . '\MyCoolService');
        $container->register('my_cool.thing', __NAMESPACE__ . '\MyCoolThing')
            ->addTag('my_cool.service_addition')
        ;

        $pass = new CallableCompilerPass(function (ContainerBuilder $container) {
            if (!$container->has('my_cool.service')) {
                return;
            }

            $definition = $container->findDefinition('my_cool.service');

            $taggedServices = $container->findTaggedServiceIds(
                'my_cool.service_addition'
            );

            foreach ($taggedServices as $id => $tags) {
                $definition->addMethodCall(
                    'addThing', array(new Reference($id))
                );
            }
        }
        );

        $pass->process($container);

        // Test definition properties are correct
        $this->assertCount(1, $container->getDefinition('my_cool.service')->getMethodCalls());
        $this->assertEquals('addThing', $container->getDefinition('my_cool.service')->getMethodCalls()[0][0]);

        // Check thing is a thing
        $thing = $container->get('my_cool.service')->getThing();
        $this->assertEquals(__NAMESPACE__ . '\MyCoolThing', get_class($thing));
    }
}

class MyCoolService
{
    private $thing;

    public function addThing($thing)
    {
        $this->thing = $thing;
    }
    
    public function getThing()
    {
        return $this->thing;
    }
}

class MyCoolThing
{
    
}
