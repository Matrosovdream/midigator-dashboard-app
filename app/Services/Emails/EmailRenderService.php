<?php

namespace App\Services\Emails;

class EmailRenderService
{
    public function render(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($m) use ($variables) {
            $key = $m[1];
            $value = $variables[$key] ?? null;
            return is_scalar($value) ? (string) $value : '';
        }, $template);
    }

    public function renderFromCase(string $template, array $caseRecord): string
    {
        $vars = $this->extractVariables($caseRecord);
        return $this->render($template, $vars);
    }

    public function extractVariables(array $caseRecord): array
    {
        $vars = [];
        foreach ($caseRecord as $key => $value) {
            if ($key === 'Model' || is_array($value) || is_object($value)) {
                continue;
            }
            $vars[$key] = $value;
        }
        return $vars;
    }
}
