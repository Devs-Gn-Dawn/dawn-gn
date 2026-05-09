<?php

namespace App\Tests\Service;

use App\Entity\FactionType;
use App\Service\PlacePjItemNameFactionResolver;
use PHPUnit\Framework\TestCase;

class PlacePjItemNameFactionResolverTest extends TestCase
{
    private PlacePjItemNameFactionResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new PlacePjItemNameFactionResolver();
    }

    public function testResolvesTechersWithApostrophe(): void
    {
        $f = $this->resolver->resolve('Place PJ Tech\'ers - Groupe Legionnaire');
        self::assertSame(FactionType::TECHERS, $f);
    }

    public function testResolvesNeoCuba(): void
    {
        $f = $this->resolver->resolve('Place PJ Neo Cuba');
        self::assertSame(FactionType::NEOCUBA, $f);
    }

    public function testResolvesNomads(): void
    {
        $f = $this->resolver->resolve('Place PJ Nomads - Groupe Shaman');
        self::assertSame(FactionType::NOMADS, $f);
    }

    public function testResolvesRodoirsPlural(): void
    {
        $f = $this->resolver->resolve('Place PJ Rodoirs - Groupe Auriculaire');
        self::assertSame(FactionType::RODOIR, $f);
    }

    public function testNullWhenNoPlacePjPrefix(): void
    {
        self::assertNull($this->resolver->resolve('M+'));
        self::assertNull($this->resolver->resolve('Something Place PJ Nomads'));
    }

    public function testNullWhenUnknownPlacePjLabel(): void
    {
        self::assertNull($this->resolver->resolve('Place PJ Unknown Faction'));
    }

    public function testNullForEmpty(): void
    {
        self::assertNull($this->resolver->resolve(null));
        self::assertNull($this->resolver->resolve(''));
        self::assertNull($this->resolver->resolve('   '));
    }

    public function testPrefixIsCaseInsensitive(): void
    {
        $f = $this->resolver->resolve('place pj Nomads - Groupe Raider');
        self::assertSame(FactionType::NOMADS, $f);
    }
}
