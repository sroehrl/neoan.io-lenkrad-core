<?php

namespace Test\Render;

use Neoan\Render\Renderer;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockRenderer;

class RendererTest extends TestCase
{
    function testMockability()
    {
        $r = Renderer::getInstance(new MockRenderer());
        $this->assertInstanceOf(MockRenderer::class, $r);
        Renderer::detachInstance();
        $r = Renderer::getInstance();
        $this->assertInstanceOf(Renderer::class, $r);
    }
    function testSetters()
    {
        Renderer::setHtmlSkeleton(__DIR__ . '/test.html');
        Renderer::setTemplatePath('/tests');
        $r = Renderer::getInstance();
        $this->assertIsString($r->getTemplatePath());
        $this->assertIsString($r->getHtmlSkeletonPath());
        $this->assertIsString($r->getHtmlComponentPlacement());
    }
    function testRender()
    {
        Renderer::detachInstance();
        Renderer::setTemplatePath('tests');
        $output = Renderer::render(['main'=>'test'], '/Render/test.html');
        $this->assertIsString($output);
    }
    function testSkeleton()
    {
        Renderer::detachInstance();
        Renderer::setTemplatePath('tests');
        $fromSkeleton = ['content' => 'works'];
        Renderer::setHtmlSkeleton('tests/Render/test.html', 'main',$fromSkeleton);
        $output = Renderer::render([], '/Render/subView.html');
        $this->assertSame('<section><p>works</p></section>', $output);
    }
}
