<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Tests\ArgumentResolver;

use Outcomer\ValidationBundle\ArgumentResolver\MapRequestResolver;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Model\ValidatedRequest;
use Outcomer\ValidationBundle\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Test suite for MapRequestResolver casting behaviour
 */
final class MapRequestResolverTest extends TestCase
{
    private string $schemasPath;

    protected function setUp(): void
    {
        $this->schemasPath = sys_get_temp_dir().'/outcomer_resolver_test';
        @mkdir($this->schemasPath, 0777, true);

        file_put_contents($this->schemasPath.'/permissive.json', json_encode([
            'type' => 'object',
        ]));
    }

    private function makeValidator(): SchemaValidator
    {
        return new SchemaValidator(new ServiceLocator([]), $this->schemasPath, 'https://test.local/schemas');
    }

    private function makeArgument(?string $type): ArgumentMetadata
    {
        return new ArgumentMetadata('data', $type, false, false, null, false, [new MapRequest('permissive.json')]);
    }

    public function testQueryIsCastWhenAutoCastQueryEnabled(): void
    {
        $resolver = new MapRequestResolver($this->makeValidator(), $this->schemasPath, autoCastQuery: true, autoCastPath: true);
        $request  = Request::create('/test?page=2&flag=true');

        /** @var ValidatedRequest $result */
        [$result] = $resolver->resolve($request, $this->makeArgument(null));

        $this->assertSame(2, $result->getPayload()->getQuery()->page);
        $this->assertTrue($result->getPayload()->getQuery()->flag);
    }

    public function testQueryIsNotCastWhenAutoCastQueryDisabled(): void
    {
        $resolver = new MapRequestResolver($this->makeValidator(), $this->schemasPath, autoCastQuery: false, autoCastPath: true);
        $request  = Request::create('/test?page=2&flag=true');

        /** @var ValidatedRequest $result */
        [$result] = $resolver->resolve($request, $this->makeArgument(null));

        $this->assertSame('2', $result->getPayload()->getQuery()->page);
        $this->assertSame('true', $result->getPayload()->getQuery()->flag);
    }

    public function testHeadersAreNeverCastRegardlessOfFlags(): void
    {
        $resolver = new MapRequestResolver($this->makeValidator(), $this->schemasPath, autoCastQuery: true, autoCastPath: true);
        $request  = Request::create('/test');
        $request->headers->set('X-Count', '5');
        $request->headers->set('X-Flag', 'true');

        /** @var ValidatedRequest $result */
        [$result] = $resolver->resolve($request, $this->makeArgument(null));

        $this->assertSame('5', $result->getPayload()->getHeaders()->{'x-count'});
        $this->assertSame('true', $result->getPayload()->getHeaders()->{'x-flag'});
    }
}
