<?php

namespace Novay\MiniOS\Http\Controllers;

use Illuminate\Http\Response;

class AssetController
{
    /**
     * Serve the pre-compiled MiniOS JavaScript bundle.
     */
    public function script(): Response
    {
        $path = __DIR__.'/../../../dist/minios.min.js';
        $content = file_exists($path) ? file_get_contents($path) : '';

        return response($content, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Serve the MiniOS CSS stylesheet.
     */
    public function style(): Response
    {
        $path = __DIR__.'/../../../dist/minios.css';
        $content = file_exists($path) ? file_get_contents($path) : '';

        return response($content, 200, [
            'Content-Type' => 'text/css; charset=utf-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
