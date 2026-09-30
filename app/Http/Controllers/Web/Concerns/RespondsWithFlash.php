<?php

namespace App\Http\Controllers\Web\Concerns;

trait RespondsWithFlash
{
    protected function success(string $message, ?string $route = null, array $params = [])
    {
        return $route
            ? redirect()->route($route, $params)->with('success', $message)
            : back()->with('success', $message);
    }

    protected function error(string $message)
    {
        return back()->with('error', $message)->withInput();
    }
}
