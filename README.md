Callable Compiler Pass [![Build Status](https://travis-ci.org/bangpound/callable-compiler-pass.svg?branch=master)](https://travis-ci.org/bangpound/callable-compiler-pass)
======================

Use this implementation of `CompilerPassInterface` to [create compiler passes][1] in
Symfony without defining another class.

[1]: http://symfony.com/doc/current/components/dependency_injection/compilation.html

Usage
-----

You should use this class wherever you instantiate a container compiler pass to modify
the Symfony dependency injection container. For typical Symfony applications, this happens
in a bundle.

Without `CallableCompilerPass`, your bundle class would add the compiler pass this way:

```php
<?php
namespace My\Bundle\CoolBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use My\Bundle\CoolBundle\DependencyInjection\Compiler\AddMyStuffPass;

class MyCoolBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddMyTaggedServicesPass());
    }
}
```

With `CallableCompilerPass`, you can skip creating the `AddMyTaggedServicesPass` and
define the compiler pass in the bundle's build method:

```php
<?php
namespace My\Bundle\CoolBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Bangpound\Symfony\DependencyInjection\CallableCompilerPass;

class MyCoolBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CallableCompilerPass(
            function (ContainerBuilder $container) {
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
        ));
    }
}
```

The utility of the `CallableCompilerPass` is most apparent when developing a Symfony
application with the new [`MicroKernelTrait`][2] in Symfony 2.8 and 3.0.

```php
<?php

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        );

        return $bundles;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->addCompilerPass(new CallableCompilerPass(function (ContainerBuilder $container) {
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
        });
    }
}
```

In the microkernel, you might also use `CallableCompilerPass` to remove services you do
not need but which are added by the Symfony FrameworkBundle by default, for example.

[2]: https://symfony.com/doc/2.8/cookbook/configuration/micro-kernel-trait.html
